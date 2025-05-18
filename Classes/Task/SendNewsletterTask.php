<?php
namespace Cylancer\CyNewsletter\Task;


use GeorgRinger\News\Domain\Repository\NewsRepository;

use Cylancer\CyNewsletter\Domain\Repository\NewsletterLogRepository;
use Cylancer\CyNewsletter\Domain\Model\NewsletterLog;
use Cylancer\CyNewsletter\Domain\Model\UserNewsletterOptions;
use Cylancer\CyNewsletter\Domain\Repository\FrontendUserRepository;


use TYPO3\CMS\Scheduler\Task\AbstractTask;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;


/**
 * This file is part of the "cy_newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */

class SendNewsletterTask extends AbstractTask
{

    public const NEWS_STORAGE_PAGE_ID = 'newsStoragePageId';

    public const FE_USER_PAGE_ID = 'feUserPageId';

    public const LOG_STORAGE_PAGE_ID = 'logStoragePageId';

    public const MAX_CHARACTERS = 'maxCharacters';

    public const NEWS_DISPLAY_PAGE_ID = 'newsDisplayPageUid';

    public const SUBJECT_PREFIX = 'subjectPrefix';

    public const SENDER_NAME = 'senderName';

    public const SITE_IDENTIFIER = 'siteIdentifier';

    public const READ_MORE = 'readMore';

    public const NEWS_PUBLIC_OFFSET = 3600;

    public const EXTENSION_NAME = 'CyNewsletter';

    public int|string $newsStoragePageId = 0;

    public int|string $feUserPageId = 0;

    public int|string $logStoragePageId = 0;

    public int|string $maxCharacters = 25;

    public int|string $newsDisplayPageUid = 0;

    public string $subjectPrefix = '';

    public string $readMore = '';
    public string $senderName = '';

    public string $siteIdentifier = '';

    private ?FrontendUserRepository $frontendUserRepository = null;
    private ?NewsRepository $newsRepository = null;

    private ?NewsletterLogRepository $newsletterLogRepository = null;

    private ?PersistenceManager $persistenceManager;

    public function init(): void
    {

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


    public function execute(): bool
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
                    foreach ($uq->matching($uq->logicalAnd($uq->in('newsletterSetting', $newsletterOptions), $uq->equals('disable', '0'), $uq->logicalNot($uq->equals('email', ''))))->execute() as $u) {
                        $fluidEmail = GeneralUtility::makeInstance(FluidEmail::class);
                        $fluidEmail
                            ->setRequest($this->createRequest(siteIdentifier: $this->siteIdentifier))
                            ->to(new Address($u->getEmail(), $u->getName()))
                            ->from(new Address(MailUtility::getSystemFromAddress(), $this->get(SendNewsletterTask::SENDER_NAME)))
                            ->subject($subject)
                            ->format(FluidEmail::FORMAT_BOTH) // send HTML and plaintext mail
                            ->setTemplate('NewsRememberEmail')
                            ->assign('user', $u)
                            ->assign('news', $n)
                            ->assign('pageUid', $this->newsDisplayPageUid)
                            ->assign('maxCharacters', $this->maxCharacters)
                            ->assign('readMore', $this->readMore)
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

    private function createRequest(string $siteIdentifier): ServerRequest
    {
        $serverRequestFactory = GeneralUtility::makeInstance(ServerRequestFactoryInterface::class);
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($siteIdentifier);
        $serverRequest = $serverRequestFactory->createServerRequest('GET', $site->getBase())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('site', $site)
            ->withAttribute('extbase', GeneralUtility::makeInstance(ExtbaseRequestParameters::class))
        ;
        return $serverRequest;
    }

    public function getAdditionalInformation(): string
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

    public function get(int|string $key): mixed
    {
        switch ($key) {
            case SendNewsletterTask::MAX_CHARACTERS:
                return intval($this->maxCharacters);
            case SendNewsletterTask::NEWS_STORAGE_PAGE_ID:
                return intval($this->newsStoragePageId);
            case SendNewsletterTask::FE_USER_PAGE_ID:
                return intval($this->feUserPageId);
            case SendNewsletterTask::LOG_STORAGE_PAGE_ID:
                return intval($this->logStoragePageId);
            case SendNewsletterTask::NEWS_DISPLAY_PAGE_ID:
                return intval($this->newsDisplayPageUid);
            case SendNewsletterTask::SUBJECT_PREFIX:
                return $this->subjectPrefix;
            case SendNewsletterTask::SENDER_NAME:
                return $this->senderName;
            case SendNewsletterTask::READ_MORE:
                return $this->readMore;
            case SendNewsletterTask::SITE_IDENTIFIER:
                return $this->siteIdentifier;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }

    public function set(array $data): void
    {

        foreach ([ // 
            SendNewsletterTask::NEWS_STORAGE_PAGE_ID,  // 
            SendNewsletterTask::FE_USER_PAGE_ID,  // 
            SendNewsletterTask::LOG_STORAGE_PAGE_ID,  // 
            SendNewsletterTask::MAX_CHARACTERS,  // 
            SendNewsletterTask::NEWS_DISPLAY_PAGE_ID,  // 
            SendNewsletterTask::SUBJECT_PREFIX,  // 
            SendNewsletterTask::SENDER_NAME,  // 
            SendNewsletterTask::READ_MORE,  // 
            SendNewsletterTask::SITE_IDENTIFIER // 
        ] as $key) {

            $value = $data[$key];

            switch ($key) {
                case SendNewsletterTask::NEWS_STORAGE_PAGE_ID:
                    $this->newsStoragePageId = intval($value);
                    break;
                case SendNewsletterTask::FE_USER_PAGE_ID:
                    $this->feUserPageId = intval($value);
                    break;
                case SendNewsletterTask::LOG_STORAGE_PAGE_ID:
                    $this->logStoragePageId = intval($value);
                    break;
                case SendNewsletterTask::MAX_CHARACTERS:
                    $this->maxCharacters = intval($value);
                    break;
                case SendNewsletterTask::NEWS_DISPLAY_PAGE_ID:
                    $this->newsDisplayPageUid = intval($value);
                    break;
                case SendNewsletterTask::SUBJECT_PREFIX:
                    $this->subjectPrefix = $value;
                    break;
                case SendNewsletterTask::SENDER_NAME:
                    $this->senderName = $value;
                    break;
                case SendNewsletterTask::READ_MORE:
                    $this->readMore = $value;
                    break;
                case SendNewsletterTask::SITE_IDENTIFIER:
                    $this->siteIdentifier = $value;
                    break;
                default:
                    throw new \Exception("Unknown key: $key");
            }
        }
    }

    /**
     * 
     * @deprecated remove if all instances with the correct types are saved.
     * @return bool
     */
    public function save(): bool
    {
        $this->newsStoragePageId = intval($this->newsStoragePageId);
        $this->feUserPageId = intval($this->feUserPageId);
        $this->logStoragePageId = intval($this->logStoragePageId);
        $this->maxCharacters = intval($this->maxCharacters);
        $this->newsDisplayPageUid = intval($this->newsDisplayPageUid);
        return parent::save();
    }

}
