<?php
namespace Cylancer\CyNewsletter\Domain\Model;

use \GeorgRinger\News\Domain\Model\News;

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

class NewsletterLog extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    protected ?News $news = null;

    public function __construct()
    {
    }


    public function getNews(): News
    {
        return $this->news;
    }

    public function setNews(News $news): void
    {
        $this->news = $news;
    }
}
