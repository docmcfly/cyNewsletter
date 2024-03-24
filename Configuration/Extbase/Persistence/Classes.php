<?php
declare(strict_types = 1);
use Cylancer\CyNewsletter\Domain\Model\FrontendUser;
use Cylancer\CyNewsletter\Domain\Model\FrontendUserGroup;

return [
    FrontendUser::class => [
        'tableName' => 'fe_users'
    ],
    FrontendUserGroup::class => [
        'tableName' => 'fe_groups'
    ],

];
