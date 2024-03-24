<?php
namespace Cylancer\CyNewsletter\Domain\Model;

/**
 *
 * This file is part of the "Newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 Clemens Gogolin <service@cylancer.net>
 *
 * @package Cylancer\CyNewsletter\Domain\Model
 */
class NewsletterLog extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * news
     *
     * @var \GeorgRinger\News\Domain\Model\News
     */
    protected $news = null;

    /**
     * __construct
     */
    public function __construct()
    {
        // Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {}

    /**
     * Returns the news
     *
     * @return \GeorgRinger\News\Domain\Model\News news
     */
    public function getNews()
    {
        return $this->news;
    }

    /**
     * Sets the news
     *
     * @param
     *            \GeorgRinger\News\Domain\Model\News news
     * @return void
     */
    public function setNews(\GeorgRinger\News\Domain\Model\News $news)
    {
        $this->news = $news;
    }
}
