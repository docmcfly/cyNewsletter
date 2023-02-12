<?php
namespace Cylancer\CyNewsletter\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 *
 * This file is part of the "Newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Clemens Gogolin <service@cylancer.net>
 *
 * @package Cylancer\CyNewsletter\Domain\Model
 */
class FrontendUser extends AbstractEntity
{

    /**
     *
     * @var integer
     */
    protected $newsletterSetting = 0;

    /**
     *
     * @var string
     */
    protected $username = '';

    /**
     *
     * @var string
     */
    protected $name = '';

    /**
     *
     * @var string
     */
    protected $firstName = '';

    /**
     *
     * @var string
     */
    protected $lastName = '';

    /**
     *
     * @var string
     */
    protected $email = '';

    /**
     * Constructs a new Front-End User
     */
    public function __construct()
    {
        $this->usergroup = new ObjectStorage();
        $this->image = new ObjectStorage();
    }

    /**
     * Called again with initialize object, as fetching an entity from the DB does not use the constructor
     */
    public function initializeObject()
    {
        $this->usergroup = $this->usergroup ?? new ObjectStorage();
    }

    /**
     * Returns the newsletterSetting
     *
     * @return int $newsletterSetting
     */
    public function getNewsletterSetting(): int
    {
        return $this->newsletterSetting;
    }

    /**
     * Sets the newsletterSetting
     *
     * @param int $newsletterSetting
     * @return void
     */
    public function setNewsletterSetting(int $newsletterSetting): void
    {
        $this->newsletterSetting = $newsletterSetting;
    }

    /**
     * Sets the username value
     *
     * @param string $username
     */
    public function setUsername(String $username): void
    {
        $this->username = $username;
    }

    /**
     * Returns the username value
     *
     * @return string
     */
    public function getUsername(): String
    {
        return $this->username;
    }

    /**
     * Sets the name value
     *
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * Returns the name value
     *
     * @return string
     */
    public function getName(): String
    {
        return $this->name;
    }

    /**
     * Sets the firstName value
     *
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Returns the firstName value
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the lastName value
     *
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Returns the lastName value
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the email value
     *
     * @param string $email
     */
    public function setEmail(String $email): void
    {
        $this->email = $email;
    }

    /**
     * Returns the email value
     *
     * @return string
     */
    public function getEmail(): ?String
    {
        return $this->email;
    }
}
