<?php
defined('TYPO3') || die('Access denied.');

if (! isset($GLOBALS['TCA']['fe_users']['ctrl']['type'])) {
    // no type field defined, so we define it here. This will only happen the first time the extension is installed!!
    $GLOBALS['TCA']['fe_users']['ctrl']['type'] = 'tx_extbase_type';
    $tempColumnstx_cynewsletter_fe_users = [];
    $tempColumnstx_cynewsletter_fe_users[$GLOBALS['TCA']['fe_users']['ctrl']['type']] = [
        'exclude' => true,
        'label' => 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_db.xlf:fe_users.tx_extbase_type.Tx_CyNewsletter_User',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    '',
                    ''
                ],
                [
                    'User',
                    'Tx_CyNewsletter_User'
                ]
            ],
            'default' => 'Tx_CyNewsletter_User',
            'size' => 1,
            'maxitems' => 1
        ]
    ];
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumnstx_cynewsletter_fe_users);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', $GLOBALS['TCA']['fe_users']['ctrl']['type'], '', 'after:' . $GLOBALS['TCA']['fe_users']['ctrl']['label']);

$tmp_newsletter_columns = [
    'newsletter_setting' => [
        'exclude' => true,
        'label' => 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_db.xlf:tx_cynewsletter_domain_model_frontendUser.sheet.user_settings',
        'config' => [
            'readOnly' => 1,
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [

                    'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang.xlf:userSettings.form.newsletterOption.disabled',
                    1
                ],
                [

                    'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang.xlf:userSettings.form.newsletterOption.onlyImportantNews',
                    2
                ],
                [

                    'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang.xlf:userSettings.form.newsletterOption.allNews',
                    3
                ]
            ],
            'default' => 2
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tmp_newsletter_columns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', '--div--;LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_db.xlf:tx_cynewsletter_domain_model_frontendUser.sheet.user_settings, newsletter_setting');

/* inherit and extend the show items from the parent class */

if (isset($GLOBALS['TCA']['fe_users']['types']['0']['showitem'])) {
    $GLOBALS['TCA']['fe_users']['types']['Tx_CyNewsletter_User']['showitem'] = $GLOBALS['TCA']['fe_users']['types']['0']['showitem'];
} elseif (is_array($GLOBALS['TCA']['fe_users']['types'])) {
    // use first entry in types array
    $fe_users_type_definition = reset($GLOBALS['TCA']['fe_users']['types']);
    $GLOBALS['TCA']['fe_users']['types']['Tx_CyNewsletter_User']['showitem'] = $fe_users_type_definition['showitem'];
} else {
    $GLOBALS['TCA']['fe_users']['types']['Tx_CyNewsletter_User']['showitem'] = $fe_users_type_definition['showitem'];
}

$GLOBALS['TCA']['fe_users']['columns'][$GLOBALS['TCA']['fe_users']['ctrl']['type']]['config']['items'][] = [
    'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_db.xlf:fe_users.tx_extbase_type.Tx_CyNewsletter_User',
    'Tx_CyNewsletter_User'
];



