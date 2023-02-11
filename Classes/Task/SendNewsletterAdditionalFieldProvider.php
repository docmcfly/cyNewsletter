<?php
namespace Cylancer\CyNewsletter\Task;

use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * This file is part of the "user tools" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Clemens Gogolin <service@cylancer.net>
 */
class SendNewsletterAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{

    /**
     *
     * @param array $taskInfo
     * @param SendNewsletterTask|null $task
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @param array $additionalFields
     * @return void
     */
    private function initIntegerAddtionalField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule, String $key, array &$additionalFields)
    {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();

        // Initialize extra field value
        if (empty($taskInfo[$key])) {
            if ($currentSchedulerModuleAction->equals(Action::ADD)) {
                // In case of new task and if field is empty, set default sleep time
                $taskInfo[$key] = 0;
            } elseif ($currentSchedulerModuleAction->equals(Action::EDIT)) {
                // In case of edit, set to internal value if no data was submitted already
                $taskInfo[$key] = $task->get($key);
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo[$key] = 0;
            }
        }

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="number" min="0" max="99999" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => 'LLL:EXT:usertools/Resources/Private/Language/locallang_task_sendNewsLetter.xlf:task.sendNewsletter.' . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    /**
     *
     * @param array $taskInfo
     * @param SendNewsletterTask|null $task
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @param array $additionalFields
     * @return void
     */
    private function initUrlAddtionalField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule, String $key, array &$additionalFields)
    {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();

        // Initialize extra field value
        if (empty($taskInfo[$key])) {
            if ($currentSchedulerModuleAction->equals(Action::ADD)) {
                // In case of new task and if field is empty, set default sleep time
                $taskInfo[$key] = 'https://';
            } elseif ($currentSchedulerModuleAction->equals(Action::EDIT)) {
                // In case of edit, set to internal value if no data was submitted already
                $taskInfo[$key] = $task->get($key);
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo[$key] = 'https://';
            }
        }

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="url" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => 'LLL:EXT:usertools/Resources/Private/Language/locallang_task_sendNewsLetter.xlf:task.sendNewsletter.' . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    /**
     * This method is used to define new fields for adding or editing a task
     * In this case, it adds a sleep time field
     *
     * @param array $taskInfo
     *            Reference to the array containing the info used in the add/edit form
     * @param SendNewsletterTask|null $task
     *            When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $schedulerModule
     *            Reference to the calling object (Scheduler's BE module)
     * @return array Array containing all the information pertaining to the additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $additionalFields = [];

        $this->initIntegerAddtionalField($taskInfo, $task, $schedulerModule, SendNewsletterTask::NEWS_STORAGE_PAGE_ID, $additionalFields);

        $this->initIntegerAddtionalField($taskInfo, $task, $schedulerModule, SendNewsletterTask::FE_USER_PAGE_ID, $additionalFields);

        $this->initIntegerAddtionalField($taskInfo, $task, $schedulerModule, SendNewsletterTask::LOG_STORAGE_PAGE_ID, $additionalFields);

        $this->initUrlAddtionalField($taskInfo, $task, $schedulerModule, SendNewsletterTask::NEWS_DISPLAY_URL, $additionalFields);

        $this->initIntegerAddtionalField($taskInfo, $task, $schedulerModule, SendNewsletterTask::MAX_CHARACTERS, $additionalFields);

        // debug($additionalFields);
        return $additionalFields;
    }

    /**
     *
     * @param array $submittedData
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @return boolean
     */
    private function validateIntegerAdditionalField(array &$submittedData, SchedulerModuleController $schedulerModule, String $key)
    {
        $result = true;

        $submittedData[$key] = (int) $submittedData[$key];
        if ($submittedData[$key] < 0) {
            $this->addMessage($this->getLanguageService()
                ->sL('LLL:EXT:usertools/Resources/Private/Language/locallang_task_sendNewsLetter.xlf:task.sendNewsletter.error.invalid.' . $key), FlashMessage::ERROR);
            $result = false;
        }

        return $result;
    }

    /**
     *
     * @param array $submittedData
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @return boolean
     */
    private function validateUrlAdditionalField(array &$submittedData, SchedulerModuleController $schedulerModule, String $key)
    {
        $result = true;

        if (! filter_var($submittedData[$key], FILTER_VALIDATE_URL)) {
            $this->addMessage($this->getLanguageService()
                ->sL('LLL:EXT:usertools/Resources/Private/Language/locallang_task_sendNewsLetter.xlf:task.sendNewsletter.error.invalid.' . $key), FlashMessage::ERROR);
            $result = false;
        }

        return $result;
    }

    /**
     * This method checks any additional data that is relevant to the specific task
     * If the task class is not relevant, the method is expected to return TRUE
     *
     * @param array $submittedData
     *            Reference to the array containing the data submitted by the user
     * @param SchedulerModuleController $schedulerModule
     *            Reference to the calling object (Scheduler's BE module)
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $result = true;

        $result &= $this->validateIntegerAdditionalField($submittedData, $schedulerModule, SendNewsletterTask::NEWS_STORAGE_PAGE_ID);
        $result &= $this->validateIntegerAdditionalField($submittedData, $schedulerModule, SendNewsletterTask::FE_USER_PAGE_ID);
        $result &= $this->validateIntegerAdditionalField($submittedData, $schedulerModule, SendNewsletterTask::LOG_STORAGE_PAGE_ID);
        $result &= $this->validateIntegerAdditionalField($submittedData, $schedulerModule, SendNewsletterTask::MAX_CHARACTERS);
        $result &= $this->validateUrlAdditionalField($submittedData, $schedulerModule, SendNewsletterTask::NEWS_DISPLAY_URL);

        return $result;
    }

    /**
     *
     * @param array $submittedData
     * @param AbstractTask $task
     * @param String $key
     * @return void
     */
    public function saveAdditionalField(array $submittedData, AbstractTask $task, String $key)
    {
        $task->set($key, $submittedData[$key]);
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches
     *
     * @param array $submittedData
     *            Array containing the data submitted by the user
     * @param SendNewsletterTask $task
     *            Reference to the current task object
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $this->saveAdditionalField($submittedData, $task, SendNewsletterTask::NEWS_STORAGE_PAGE_ID);
        $this->saveAdditionalField($submittedData, $task, SendNewsletterTask::FE_USER_PAGE_ID);
        $this->saveAdditionalField($submittedData, $task, SendNewsletterTask::LOG_STORAGE_PAGE_ID);
        $this->saveAdditionalField($submittedData, $task, SendNewsletterTask::MAX_CHARACTERS);
        $this->saveAdditionalField($submittedData, $task, SendNewsletterTask::NEWS_DISPLAY_URL);
    }

    /**
     *
     * @return LanguageService|null
     */
    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}
