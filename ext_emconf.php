<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "newsletter"
 *
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Small newsletter service for frontend user',
    'description' => 'This plugin contains a small service to send a newsletter for frontend user (based on the news extension).',
    'category' => 'plugin',
    'author' => 'Clemens Gogolin',
    'author_email' => 'service@cylancer.net',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.6',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
            'news' => '9.4.0-10.0.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

/** ---- CHANGELOG ----------
1.0.0 :: Initial 
1.0.1 :: Fix the broken log record icon / add a usertools migration wizzard
1.0.2 :: Fix the plugin descriptiom
1.0.3 :: Add typo script auto load
1.0.4 :: Add a space on the top of the save button / Removes unused files
1.0.5 :: Allows the news extension 10.0.x
1.0.5 :: Fix translation texts in the mail... 

// ---- CHANGELOG ---------- */
