<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {


<<<<<<< HEAD
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('Cylancer.CyNewsletter', 'UserSettings', 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_be_usersettings.xlf:plugin.name');
=======
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('Cylancer.CyNewsletter', 'UserSettings', 'LLL:EXT:usertools/Resources/Private/Language/locallang_be_usersettings.xlf:plugin.name');
>>>>>>> refs/remotes/origin/develop

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('cynewsletter', 'Configuration/TypoScript', 'Newsletter');
    
});