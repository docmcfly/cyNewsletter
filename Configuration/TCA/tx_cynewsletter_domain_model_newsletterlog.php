<?php

/**
 * This file is part of the "cy_newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */

return [
    'ctrl' => [
        'title' => 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_db.xlf:tx_cynewsletter_domain_model_newsletter_log:title',
        'label' => 'news',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime'
        ],
        'searchFields' => 'news',
        'iconfile' => 'EXT:cy_newsletter/Resources/Public/Icons/actions-document-select.png'
    ],
    'types' => [
        '1' => [
            'showitem' => 'news'
        ]
    ],
    'columns' => [
        'news' => [
            'exclude' => false,
            'label' => 'LLL:EXT:cy_newsletter/Resources/Private/Language/locallang_db.xlf:tx_cynewsletter_domain_model_newsletter_log.news',
            'config' => [
                'readOnly' => 1,
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_news_domain_model_news',
                'minitems' => 0,
                'maxitems' => 1
            ]
        ],
    ]
];
