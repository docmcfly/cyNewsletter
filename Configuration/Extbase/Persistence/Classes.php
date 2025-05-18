<?php
declare(strict_types = 1);
use Cylancer\CyNewsletter\Domain\Model\FrontendUser;
use Cylancer\CyNewsletter\Domain\Model\FrontendUserGroup;

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
    FrontendUser::class => [
        'tableName' => 'fe_users'
    ],
    FrontendUserGroup::class => [
        'tableName' => 'fe_groups'
    ],

];
