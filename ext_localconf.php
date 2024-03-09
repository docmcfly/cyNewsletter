<?php
use Cylancer\CyNewsletter\Controller\UserSettingsController;
use Cylancer\CyNewsletter\Upgrades\MigrationUpgradeWizard;
use Cylancer\CyNewsletter\Upgrades\UserToolsMigrationWizard;

defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin('CyNewsletter', 'UserSettings', [
       UserSettingsController::class => 'show, save'
    ], 
        // non-cacheable actions
        [
            UserSettingsController::class => 'show,save'
        ]);

 

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                   usersettings {
                        iconIdentifier = cynewsletter-plugin-usersettings
                        title = LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_be_usersettings.xlf:plugin.name
                        description = LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_be_usersettings.xlf:plugin.description
                        tt_content_defValues {
                            CType = list
                            list_type = cynewsletter_usersettings
                        }
                    }
                }
                show = *
            }
       }');
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

    $iconRegistry->registerIcon('cynewsletter-plugin-usersettings', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
        'source' => 'EXT:cy_newsletter/Resources/Public/Icons/ext_icon.svg'
    ]);

 
});

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Cylancer\CyNewsletter\Task\SendNewsletterTask::class]['description'] = 'Send Newsletter';
// Add task for optimizing database tables
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Cylancer\CyNewsletter\Task\SendNewsletterTask::class] = [
    'extension' => 'cy_newsletter',
    'title' => 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_task_sendNewsLetter.xlf:task.sendNewsletter.title',
    'description' => 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_task_sendNewsLetter.xlf:task.sendNewsletter.description',
    'additionalFields' => \Cylancer\CyNewsletter\Task\SendNewsletterAdditionalFieldProvider::class
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cynewsletter_newsletterUsertoolsMigrationWizard']
= UserToolsMigrationWizard::class;

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'cy_newsletter',
    'setup',
    "@import 'EXT:cy_newsletter/Configuration/TypoScript/setup.typoscript'"
    );


    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths']['cy_newsletter']    = 'EXT:cy_newsletter/Resources/Private/Templates/NewsRememberEmail/';
    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths']['cy_newsletter']    = 'EXT:cy_newsletter/Resources/Private/Layouts/NewsRememberEmail/';
    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths']['cy_newsletter']    = 'EXT:cy_newsletter/Resources/Private/Partials/NewsRememberEmail/';
