<?php
defined('TYPO3') || die('Access denied.');

/**
 * This file is part of the "cy_newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */


use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$translationPath = 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_db.xlf:tx_cynewsletter_domain_model_frontendUser';

ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    [
        'newsletter_setting' => [
            'exclude' => true,
            'label' => "$translationPath.sheet.user_settings",
            'config' => [
                'readOnly' => 1,
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang.xlf:userSettings.form.newsletterOption.disabled',
                        'value' => 1
                    ],
                    [
                        'label' => 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang.xlf:userSettings.form.newsletterOption.onlyImportantNews',
                        'value' => 2
                    ],
                    [
                        'label' => 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang.xlf:userSettings.form.newsletterOption.allNews',
                        'value' => 3
                    ],
                ],
                'default' => 2
            ]
        ]
    ]

);

ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    "--div--;$translationPath.sheet.user_settings, newsletter_setting"
);

