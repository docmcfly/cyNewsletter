<?php
namespace Cylancer\CyNewsletter\Domain\Model;


/**
 *
 * This file is part of the "cy_newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */

class UserNewsletterOptions
{

    public const DISABLE = 1;

    public const ONLY_IMPORTANT_NEWS = 2;

    public const ALL_NEWS = 3;

    public const LABEL = [
        UserNewsletterOptions::DISABLE => 'disabled',
        UserNewsletterOptions::ONLY_IMPORTANT_NEWS => 'onlyImportantNews',
        UserNewsletterOptions::ALL_NEWS => 'allNews'
    ];
}
