<?php
namespace Cylancer\CyNewsletter\Task;

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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Cylancer\CyNewsletter\Service\EmailSendService;

/**
 * This file is part of the "newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 Clemens Gogolin <service@cylancer.net>
 *
 * @package Cylancer\CyNewsletter\Task;
 */
class SendNewsletterTask extends AbstractTask
{

    const NEWS_STORAGE_PAGE_ID = 'newsStoragePageId';

    const FE_USER_PAGE_ID = 'feUserPageId';

    const LOG_STORAGE_PAGE_ID = 'logStoragePageId';

    const MAX_CHARACTERS = 'maxCharacters';

    const NEWS_DISPLAY_URL = 'newsDisplayUrl';

    const SUBJECT_PREFIX = 'subjectPrefix';

    const SENDER_NAME = 'senderName';

    const NEWS_PUBLIC_OFFSET = 3600;

    const EXTENSION_NAME = 'CyNewsletter';

    public $newsStoragePageId = 0;

    public $feUserPageId = 0;

    public $logStoragePageId = 0;

    public $maxCharacters = 25;

    public $newsDisplayUrl = 'https://';

    public $subjectPrefix = '';

    public $senderName = '';

    /** @var FrontendUserRepository */
    private $frontendUserRepository = null;

    /** @var \GeorgRinger\News\Domain\Repository\NewsRepository */
    private $newsRepository = null;

    /** @var NewsletterLogRepository */
    private $newsletterLogRepository = null;

    /**  @var EmailSendService */
    private $emailService = null;

    /** @var PersistenceManagerInterface */
    private $persistenceManager;

    /**
     *
     * {@inheritdoc}
     * @see \TYPO3\CMS\Scheduler\Task\AbstractTask::__construct()
     */
    public function init()
    {
        /**
         *
         * @var ObjectManager $objectManager
         * @deprecated $objectManager
         */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        $this->frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class, $objectManager);
        $this->frontendUserRepository->injectPersistenceManager($this->persistenceManager);

        $querySettings = $this->frontendUserRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds([
            $this->feUserPageId
        ]);
        $this->frontendUserRepository->setDefaultQuerySettings($querySettings);

        $this->newsRepository = GeneralUtility::makeInstance(NewsRepository::class, $objectManager);
        $this->newsRepository->injectPersistenceManager($this->persistenceManager);

        $querySettings = $this->newsRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds([
            $this->newsStoragePageId
        ]);
        $this->newsRepository->setDefaultQuerySettings($querySettings);

        $this->newsletterLogRepository = GeneralUtility::makeInstance(NewsletterLogRepository::class, $objectManager);
        $this->newsletterLogRepository->injectPersistenceManager($this->persistenceManager);

        $querySettings = $this->newsletterLogRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds([
            $this->logStoragePageId
        ]);

        $this->newsletterLogRepository->setDefaultQuerySettings($querySettings);

        $this->emailSendService = GeneralUtility::makeInstance(EmailSendService::class);
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

            // debug($nqb->getSQL());

            $sql = $nqb->getSQL() . ' AND `uid` NOT IN ( ' . $nlqb->getSQL() . ' ) ';

            // Alle News zwischen dem letzten Log-Eintrag und dem aktuellen Zeitpunkt -1h
            // debug($sql);

            foreach ($this->newsRepository->createQuery()
                ->statement($sql)
                ->execute() as $n) {

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

                    foreach ($uq->matching($uq->logicalAnd([
                        $uq->in('newsletterSetting', $newsletterOptions),
                        $uq->logicalNot($uq->equals('email', ''))
                    ]))
                        ->execute() as $u) {
                        $this->emailSendService->sendTemplateEmail([
                            $u->getEmail() => $u->getName()
                        ], [
                            MailUtility::getSystemFromAddress() => $this->get(SendNewsletterTask::SENDER_NAME)
                        ], [], $subject, 'NewsRememberEmail', SendNewsletterTask::EXTENSION_NAME, [
                            'targetUrl' => $this->newsDisplayUrl,
                            'user' => $u,
                            'news' => $n,
                            'maxCharacters' => $this->maxCharacters
                        ]);
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
        ' / newsDisplayUrl: ' . $this->newsDisplayUrl . //
        ' / maxCharacters: ' . $this->maxCharacters . //
        ' / subjectPrefix: ' . $this->subjectPrefix . //
        ' / senderName: ' . $this->senderName;
    }

    /**
     *
     * @param String $key
     * @throws \Exception
     * @return number|String
     */
    public function get(String $key)
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
            case SendNewsletterTask::NEWS_DISPLAY_URL:
                return $this->newsDisplayUrl;
            case SendNewsletterTask::SUBJECT_PREFIX:
                return $this->subjectPrefix;
            case SendNewsletterTask::SENDER_NAME:
                return $this->senderName;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }

    public function set(String $key, $value)
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
            case SendNewsletterTask::NEWS_DISPLAY_URL:
                $this->newsDisplayUrl = $value;
                break;
            case SendNewsletterTask::SUBJECT_PREFIX:
                $this->subjectPrefix = $value;
                break;
            case SendNewsletterTask::SENDER_NAME:
                $this->senderName = $value;
                break;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }
}
