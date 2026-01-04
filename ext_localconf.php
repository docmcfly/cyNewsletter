<?php
use Cylancer\CyNewsletter\Controller\UserSettingsController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

/**
 *
 * This file is part of the "cy_newletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */


ExtensionUtility::configurePlugin(
    'CyNewsletter',
    'UserSettings',
    [
        UserSettingsController::class => 'show, save'
    ],
    // non-cacheable actions
    [
        UserSettingsController::class => 'show,save'
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Cylancer\CyNewsletter\Task\SendNewsletterTask::class] = [
    'extension' => 'cy_newsletter',
    'title' => 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_task_sendNewsLetter.xlf:task.sendNewsletter.title',
    'description' => 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_task_sendNewsLetter.xlf:task.sendNewsletter.description',
    'additionalFields' => \Cylancer\CyNewsletter\Task\SendNewsletterAdditionalFieldProvider::class
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'cy_newsletter',
    'setup',
    "@import 'EXT:cy_newsletter/Configuration/TypoScript/setup.typoscript'"
);

// E-Mail Templates
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths']['cy_newsletter'] = 'EXT:cy_newsletter/Resources/Private/Templates/NewsRememberEmail/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths']['cy_newsletter'] = 'EXT:cy_newsletter/Resources/Private/Layouts/NewsRememberEmail/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths']['cy_newsletter'] = 'EXT:cy_newsletter/Resources/Private/Partials/NewsRememberEmail/';
