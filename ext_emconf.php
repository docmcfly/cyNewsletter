<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "newsletter"
 *
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Small newsletter service for frontend user',
    'description' => 'This plug in contains a small service to send a newsletter for frontend user (based on the news extension).',
    'category' => 'plugin',
    'author' => 'Clemens Gogolin',
    'author_email' => 'service@cylancer.net',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
            'news' => '9.4.0-9.4.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

/** ---- CHANGELOG ----------
1.0.0 :: Initial 
1.0.1 :: fix the broken log record icon / add a usertools migration wizzard


// ---- CHANGELOG ---------- */
