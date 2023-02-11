<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "usertools"
 *
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Newsletter for frontend user',
    'description' => 'This plug in contains a task to send a newsletter for frontend user (based on the news extension).',
    'category' => 'plugin',
    'author' => 'Clemens Gogolin',
    'author_email' => 'service@cylancer.net',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
            'news' => '8.6.0-9.4.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

/** ---- CHANGELOG ----------
1.0.0 :: Initial 

// ---- CHANGELOG ---------- */
