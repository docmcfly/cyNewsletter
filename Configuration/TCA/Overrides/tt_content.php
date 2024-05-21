<?php
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;


defined('TYPO3') or die();

(static function (): void{

    ExtensionUtility::registerPlugin(
        'CyNewsletter',
        'UserSettings',
        'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_be_usersettings.xlf:plugin.name'
    );

})();