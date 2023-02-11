<?php
namespace Cylancer\Usertools\Service;

/**
 *
 * This file is part of the "user tools" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 *
 * @package Cylancer\Usertools\Service
 */
use TYPO3\CMS\Core\SingletonInterface;
use Cylancer\Usertools\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Context\Context;
use Cylancer\Usertools\Domain\Model\FrontendUserGroup;
use Cylancer\Usertools\Domain\Model\FrontendUser;

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
    public function getCurrentUser(): FrontendUser
    {
        if (! $this->isLogged()) {
            return false;
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

    /**
     *
     * @param FrontendUserGroup $userGroup
     * @param integer $fegid
     * @param array $loopProtect
     * @return boolean
     */
    public function contains($userGroup, $feugid, &$loopProtect = array()): bool
    {
        if ($userGroup->getUid() == $feugid) {
            return true;
        } else {
            if (! in_array($userGroup->getUid(), $loopProtect)) {
                $loopProtect[] = $userGroup->getUid();
                foreach ($userGroup->getSubgroup() as $sg) {
                    if ($this->contains($sg, $feugid, $loopProtect)) {
                        return true;
                    }
                }
            }
            return false;
        }
    }

    /**
     *
     * @param FrontendUserGroup $userGroup
     * @param integer $fegid
     * @param array $loopProtect
     * @return boolean
     */
    public function getAllGroups($userGroup, $return = array(), &$loopProtect = array()): bool
    {
        $return = array();
        if ($userGroup->getUid() == $feugid) {
            return true;
        } else {
            if (! in_array($userGroup->getUid(), $loopProtect)) {
                $loopProtect[] = $userGroup->getUid();
                foreach ($userGroup->getSubgroup() as $sg) {
                    if ($this->contains($sg, $feugid, $loopProtect)) {
                        return true;
                    }
                }
            }
            return false;
        }
    }
}
