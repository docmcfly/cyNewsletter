<?php
namespace Cylancer\CyNewsletter\Task;

use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Scheduler\Task\AbstractTask;


/**
 * This file is part of the "cy_newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */

class SendNewsletterAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    private const TRANSLATION_PREFIX = 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_task_sendNewsLetter.xlf:task.sendNewsletter.';

    private function getDefault(string $key): string|int
    {
        switch ($key) {
            case SendNewsletterTask::FE_USER_PAGE_ID:
            case SendNewsletterTask::NEWS_STORAGE_PAGE_ID:
            case SendNewsletterTask::NEWS_DISPLAY_PAGE_ID:
            case SendNewsletterTask::LOG_STORAGE_PAGE_ID:
                return 0;
            case SendNewsletterTask::MAX_CHARACTERS:
                return 150;
            case SendNewsletterTask::READ_MORE:
                return LocalizationUtility::translate(SendNewsletterAdditionalFieldProvider::TRANSLATION_PREFIX . 'readMore.default', SendNewsletterTask::EXTENSION_NAME);
            default:
                return '';
        }
    }

    private function setCurrentKey(array &$taskInfo, ?SendNewsletterTask $task, string $key): void
    {
        if (empty($taskInfo[$key])) {
            $taskInfo[$key] = $task != null ? $task->get($key) : $this->getDefault($key);
        }
    }

    private function initIntegerAddtionalField(array &$taskInfo, $task, string $key, array &$additionalFields)
    {

        $this->setCurrentKey($taskInfo, $task, $key);

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="number" min="0" max="99999" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => SendNewsletterAdditionalFieldProvider::TRANSLATION_PREFIX . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    } 
    
    private function initStringAddtionalField(array &$taskInfo, $task, string $key, array &$additionalFields)
    {
        $this->setCurrentKey($taskInfo, $task, $key);

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="text" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => SendNewsletterAdditionalFieldProvider::TRANSLATION_PREFIX . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $additionalFields = [];
        $this->initIntegerAddtionalField($taskInfo, $task, SendNewsletterTask::NEWS_STORAGE_PAGE_ID, $additionalFields);
        $this->initIntegerAddtionalField($taskInfo, $task, SendNewsletterTask::FE_USER_PAGE_ID, $additionalFields);
        $this->initIntegerAddtionalField($taskInfo, $task, SendNewsletterTask::LOG_STORAGE_PAGE_ID, $additionalFields);
        $this->initIntegerAddtionalField($taskInfo, $task, SendNewsletterTask::NEWS_DISPLAY_PAGE_ID, $additionalFields);
        $this->initIntegerAddtionalField($taskInfo, $task, SendNewsletterTask::MAX_CHARACTERS, $additionalFields);

        $this->initStringAddtionalField($taskInfo, $task, SendNewsletterTask::SUBJECT_PREFIX, $additionalFields);
        $this->initStringAddtionalField($taskInfo, $task, SendNewsletterTask::SENDER_NAME, $additionalFields);
        $this->initStringAddtionalField($taskInfo, $task, SendNewsletterTask::SITE_IDENTIFIER, $additionalFields);
        $this->initStringAddtionalField($taskInfo, $task, SendNewsletterTask::READ_MORE, $additionalFields);

        return $additionalFields;
    }

    private function validateIntegerAdditionalField(array &$submittedData, string $key)
    {
        $submittedData[$key] = (int) $submittedData[$key];
        if ($submittedData[$key] < 0) {
            $this->addMessage($this->getLanguageService()
                ->sL(SendNewsletterAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalid.' . $key), ContextualFeedbackSeverity::ERROR);
            return false;
        }

        return true;
    }

    private function validatePageUidAdditionalField(array &$submittedData, string $key)
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        if (count($pageRepository->getPage($submittedData[$key], true)) === 0) {
            $this->addMessage($this->getLanguageService()
                ->sL(SendNewsletterAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalid.' . $key), ContextualFeedbackSeverity::ERROR);
            return false;
        }

        return true;
    }

    private function validateRequiredField(array &$submittedData, string $key)
    {
        if (empty($submittedData[$key])) {
            $this->addMessage($this->getLanguageService()
                ->sL(SendNewsletterAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.required.' . $key), ContextualFeedbackSeverity::ERROR);
            return false;
        }

        return true;
    }

    private function validateSitedField(array &$submittedData, string $key)
    {
        try {
            GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($submittedData[$key]);
            return true;
        } catch (\Exception $e) {
            $this->addMessage($this->getLanguageService()
                ->sL(SendNewsletterAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.siteNotFound.' . $key));
            return false;
        }
    }

    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $result = true;

        $result &= $this->validateIntegerAdditionalField($submittedData, SendNewsletterTask::NEWS_STORAGE_PAGE_ID);
        $result &= $this->validateIntegerAdditionalField($submittedData, SendNewsletterTask::FE_USER_PAGE_ID);
        $result &= $this->validateIntegerAdditionalField($submittedData, SendNewsletterTask::LOG_STORAGE_PAGE_ID);
        $result &= $this->validateIntegerAdditionalField($submittedData, SendNewsletterTask::MAX_CHARACTERS);
        $result &= $this->validateRequiredField($submittedData, SendNewsletterTask::SITE_IDENTIFIER)
            && $this->validateSitedField($submittedData, SendNewsletterTask::SITE_IDENTIFIER);
        $result &= $this->validateIntegerAdditionalField($submittedData, SendNewsletterTask::NEWS_DISPLAY_PAGE_ID)
            && $this->validatePageUidAdditionalField($submittedData, SendNewsletterTask::NEWS_DISPLAY_PAGE_ID);
        $result &= $this->validateRequiredField($submittedData, SendNewsletterTask::READ_MORE);

        return $result;
    }

    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->set($submittedData);
    }

    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}
