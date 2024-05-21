<?php
namespace Cylancer\CyNewsletter\Task;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Site\SiteFinder;

use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use GeorgRinger\News\Domain\Repository\NewsRepository;
use Cylancer\CyNewsletter\Domain\Repository\NewsletterLogRepository;
use Cylancer\CyNewsletter\Domain\Model\NewsletterLog;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Cylancer\CyNewsletter\Domain\Model\UserNewsletterOptions;
use Cylancer\CyNewsletter\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

/**
 * This file is part of the "newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 Clemens Gogolin <service@cylancer.net>
 *
 * @package Cylancer\CyNewsletter\Task;
 */
class SendNewsletterTask extends AbstractTask
{

    const NEWS_STORAGE_PAGE_ID = 'newsStoragePageId';

    const FE_USER_PAGE_ID = 'feUserPageId';

    const LOG_STORAGE_PAGE_ID = 'logStoragePageId';

    const MAX_CHARACTERS = 'maxCharacters';

    const NEWS_DISPLAY_PAGE_ID = 'newsDisplayPageUid';

    const SUBJECT_PREFIX = 'subjectPrefix';

    const SENDER_NAME = 'senderName';

    const SITE_IDENTIFIER = 'siteIdentifier';

    const NEWS_PUBLIC_OFFSET = 3600;

    const EXTENSION_NAME = 'CyNewsletter';

    public $newsStoragePageId = 0;

    public $feUserPageId = 0;

    public $logStoragePageId = 0;

    public $maxCharacters = 25;

    public $newsDisplayPageUid = 0;

    public $subjectPrefix = '';

    public $senderName = '';
    public $siteIdentifier = '';

    /** @var FrontendUserRepository */
    private $frontendUserRepository = null;

    /** @var \GeorgRinger\News\Domain\Repository\NewsRepository */
    private $newsRepository = null;

    /** @var NewsletterLogRepository */
    private $newsletterLogRepository = null;

    /** @var PersistenceManagerInterface */
    private $persistenceManager;

    /* @var UriBuilder */
    private UriBuilder $uriBuilder;


    /**
     *
     * {@inheritdoc}
     * @see \TYPO3\CMS\Scheduler\Task\AbstractTask::__construct()
     */
    public function init()
    {

        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        $this->frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
        $this->frontendUserRepository->injectPersistenceManager($this->persistenceManager);

        $querySettings = $this->frontendUserRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds([
            $this->feUserPageId
        ]);
        $this->frontendUserRepository->setDefaultQuerySettings($querySettings);

        $this->newsRepository = GeneralUtility::makeInstance(NewsRepository::class);
        $this->newsRepository->injectPersistenceManager($this->persistenceManager);

        $querySettings = $this->newsRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds([
            $this->newsStoragePageId
        ]);
        $this->newsRepository->setDefaultQuerySettings($querySettings);

        $this->newsletterLogRepository = GeneralUtility::makeInstance(NewsletterLogRepository::class);
        $this->newsletterLogRepository->injectPersistenceManager($this->persistenceManager);

        $querySettings = $this->newsletterLogRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds([
            $this->logStoragePageId
        ]);

        $this->newsletterLogRepository->setDefaultQuerySettings($querySettings);
    }


    public function execute()
    {
        $this->init();
        if ($this->newsletterLogRepository->countAll() == 0) {
            foreach ($this->newsRepository->findAll() as $news) {
                $newletterLog = GeneralUtility::makeInstance(NewsletterLog::class);
                $newletterLog->setPid($this->logStoragePageId);
                $newletterLog->setNews($news);
                $this->newsletterLogRepository->add($newletterLog);
            }
            $this->persistenceManager->persistAll();
            return true;
        } else {
            $nlqb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_cynewsletter_domain_model_newsletterlog');
            $nlqb->select('news')
                ->from('tx_cynewsletter_domain_model_newsletterlog')
                ->where($nlqb->expr()
                    ->eq('pid', $this->logStoragePageId));
            // debug($nlqb->getSQL());

            $nqb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_news_domain_model_news');
            $nqb->select('*')
                ->from('tx_news_domain_model_news')
                ->where($nlqb->expr()
                    ->eq('pid', $this->newsStoragePageId));
            $sql = $nqb->getSQL() . ' AND `uid` NOT IN ( ' . $nlqb->getSQL() . ' ) ';
            foreach ($this->newsRepository->createQuery()->statement($sql)->execute() as $n) {

                $t = time();
                $plusOneHour = $t - SendNewsletterTask::NEWS_PUBLIC_OFFSET;

                if ($n->getDatetime()->getTimestamp() < $plusOneHour && ($n->getArchive() == null || $n->getArchive()->getTimestamp() > $t)) {
                    $newsletterOptions = $n->getIstopnews() ? [
                        UserNewsletterOptions::ALL_NEWS,
                        UserNewsletterOptions::ONLY_IMPORTANT_NEWS
                    ] : [
                        UserNewsletterOptions::ALL_NEWS
                    ];

                    $subject = $this->get(SendNewsletterTask::SUBJECT_PREFIX) . $n->getTitle();
                    $uq = $this->frontendUserRepository->createQuery();

                    foreach ($uq->matching($uq->logicalAnd($uq->in('newsletterSetting', $newsletterOptions), $uq->logicalNot($uq->equals('email', ''))))->execute() as $u) {

                        $fluidEmail = GeneralUtility::makeInstance(FluidEmail::class);
                        $fluidEmail
                            ->setRequest($this->createRequest($this->siteIdentifier))
                            ->to(new Address($u->getEmail(), $u->getName()))
                            ->from(new Address(MailUtility::getSystemFromAddress(), $this->get(SendNewsletterTask::SENDER_NAME)))
                            ->subject($subject)
                            ->format(FluidEmail::FORMAT_BOTH) // send HTML and plaintext mail
                            ->setTemplate('NewsRememberEmail')
                            ->assign('user', $u)
                            ->assign('news', $n)
                            ->assign('pageUid', $this->newsDisplayPageUid)
                            ->assign('maxCharacters', $this->maxCharacters)
                        ;
                        GeneralUtility::makeInstance(MailerInterface::class)->send($fluidEmail);
                    }

                    $newletterLog = GeneralUtility::makeInstance(NewsletterLog::class);
                    $newletterLog->setPid($this->logStoragePageId);
                    $newletterLog->setNews($n);
                    $this->newsletterLogRepository->add($newletterLog);
                }
            }
        }
        $this->persistenceManager->persistAll();
        return true;
    }

    private function createRequest(string $siteIdentifier): RequestInterface
    {
        $serverRequestFactory = GeneralUtility::makeInstance(ServerRequestFactoryInterface::class);
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($siteIdentifier);
        $serverRequest = $serverRequestFactory->createServerRequest('GET', $site->getBase())
            ->withAttribute('applicationType', \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('site', $site)
            ->withAttribute('extbase', new \TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters());
        $request = GeneralUtility::makeInstance(Request::class, $serverRequest);
       //$GLOBALS['TYPO3_REQUEST'] = $request;
       if(!isset($GLOBALS['TYPO3_REQUEST'])){
        $GLOBALS['TYPO3_REQUEST'] = $request;
        }
        return $request;
    }

    /**
     * This method returns the sleep duration as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation()
    {
        return 'News page id :' . $this->newsStoragePageId . //
            ' / feUserPageId: ' . $this->feUserPageId . //
            ' / logStoragePageId: ' . $this->logStoragePageId . //
            ' / newsDisplayPageUid: ' . $this->newsDisplayPageUid . //
            ' / maxCharacters: ' . $this->maxCharacters . //
            ' / subjectPrefix: ' . $this->subjectPrefix . //
            ' / senderName: ' . $this->senderName .
            ' / siteIdentifier: ' . $this->siteIdentifier .
            '';
    }

    /**
     *
     * @param string $key
     * @throws \Exception
     * @return number|string
     */
    public function get(string $key)
    {
        switch ($key) {
            case SendNewsletterTask::MAX_CHARACTERS:
                return $this->maxCharacters;
            case SendNewsletterTask::NEWS_STORAGE_PAGE_ID:
                return $this->newsStoragePageId;
            case SendNewsletterTask::FE_USER_PAGE_ID:
                return $this->feUserPageId;
            case SendNewsletterTask::LOG_STORAGE_PAGE_ID:
                return $this->logStoragePageId;
            case SendNewsletterTask::NEWS_DISPLAY_PAGE_ID:
                return $this->newsDisplayPageUid;
            case SendNewsletterTask::SUBJECT_PREFIX:
                return $this->subjectPrefix;
            case SendNewsletterTask::SENDER_NAME:
                return $this->senderName;
            case SendNewsletterTask::SITE_IDENTIFIER:
                return $this->siteIdentifier;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }

    public function set(string $key, $value)
    {
        switch ($key) {
            case SendNewsletterTask::NEWS_STORAGE_PAGE_ID:
                $this->newsStoragePageId = $value;
                break;
            case SendNewsletterTask::FE_USER_PAGE_ID:
                $this->feUserPageId = $value;
                break;
            case SendNewsletterTask::LOG_STORAGE_PAGE_ID:
                $this->logStoragePageId = $value;
                break;
            case SendNewsletterTask::MAX_CHARACTERS:
                $this->maxCharacters = $value;
                break;
            case SendNewsletterTask::NEWS_DISPLAY_PAGE_ID:
                $this->newsDisplayPageUid = $value;
                break;
            case SendNewsletterTask::SUBJECT_PREFIX:
                $this->subjectPrefix = $value;
                break;
            case SendNewsletterTask::SENDER_NAME:
                $this->senderName = $value;
                break;
            case SendNewsletterTask::SITE_IDENTIFIER:
                $this->siteIdentifier = $value;
                break;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }
}
