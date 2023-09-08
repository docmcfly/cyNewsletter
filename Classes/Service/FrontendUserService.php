<?php
namespace Cylancer\CyNewsLetter\Service;

/**
 *
 * This file is part of the "user tools" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 C. Gogolin <service@cylancer.net>
 *
 * @package Cylancer\CyNewsLetter\Service
 */
use TYPO3\CMS\Core\SingletonInterface;
use Cylancer\CyNewsletter\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Context\Context;
use Cylancer\CyNewsletter\Domain\Model\FrontendUserGroup;
use Cylancer\CyNewsletter\Domain\Model\FrontendUser;

class FrontendUserService implements SingletonInterface
{

    /** @var FrontendUserRepository   */
    private $frontendUserRepository = null;

    /**
     *
     * @param FrontendUserRepository $frontendUserRepository
     */
    public function __construct(FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }

    /**
     *
     * @return FrontendUser Returns the current frontend user
     */
    public function getCurrentUser(): ?FrontendUser
    {
        if (! $this->isLogged()) {
            return null;
        }
        return $this->frontendUserRepository->findByUid($this->getCurrentUserUid());
    }
    
    /**
     * 
     * @return int
     */
    public function getCurrentUserUid(): int
    {
        if (! $this->isLogged()) {
            return false;
        }
        $context = GeneralUtility::makeInstance(Context::class);
        return $context->getPropertyFromAspect('frontend.user', 'id');
    }

    /**
     * Check if the user is logged
     *
     * @return bool
     */
    public function isLogged(): bool
    {
        $context = GeneralUtility::makeInstance(Context::class);
        return $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
    }

}
