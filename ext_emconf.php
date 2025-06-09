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

 $EM_CONF[$_EXTKEY] = [
    'title' => 'Small newsletter service for frontend user',
    'description' => 'This plugin contains a small service to send a newsletter for frontend user (based on the news extension).',
    'category' => 'plugin',
    'author' => 'C. Gogolin',
    'author_email' => 'service@cylancer.net',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '3.1.1',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'news' => '12.3.0-12.3.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
