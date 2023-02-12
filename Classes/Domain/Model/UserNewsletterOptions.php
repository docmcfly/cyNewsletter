<?php
namespace Cylancer\CyNewsletter\Domain\Model;


/**
 *
 * This file is part of the "Newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 Clemens Gogolin <service@cylancer.net>
 *
 * @package Cylancer\CyNewsletter\Domain\Model
 */
class UserNewsletterOptions
{

    const DISABLE = 1;

    const ONLY_IMPORTANT_NEWS = 2;

    const ALL_NEWS = 3;

    const LABEL  = [
        UserNewsletterOptions::DISABLE => 'disabled',
        UserNewsletterOptions::ONLY_IMPORTANT_NEWS => 'onlyImportantNews',
        UserNewsletterOptions::ALL_NEWS => 'allNews'
    ];
}
    