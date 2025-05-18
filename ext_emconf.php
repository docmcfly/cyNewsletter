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
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'news' => '12.3.0-12.3.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

/** ---- CHANGELOG ----------

3.0.0 :: UPD to TYPO3 13.4.x
2.0.2 :: FIX The user flag “disable” was evaluated incorrectly. / The ununsed attribute t3ver_label generates exceptions. 
2.0.1 :: FIX Disabled user does not receive a newsletter.
2.0.0 :: UPD to TYPO3 12.4.x
1.1.1 :: Fix the news url.
1.1.0 :: Fix the plugin configuration / registry.
1.0.6 :: Fix translation texts in the mail...
1.0.5 :: Allows the news extension 10.0.x
1.0.4 :: Add a space on the top of the save button / Removes unused files
1.0.3 :: Add typo script auto load
1.0.2 :: Fix the plugin descriptiom
1.0.1 :: Fix the broken log record icon / add a usertools migration wizzard
1.0.0 :: Initial

// ---- CHANGELOG ---------- */
