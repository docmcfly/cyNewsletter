<?php
declare(strict_types=1);

defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('cynewsletter', 'Configuration/TypoScript', 'Newsletter');
});