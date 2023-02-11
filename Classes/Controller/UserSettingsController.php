<?php
namespace Cylancer\CyNewsletter\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Cylancer\CyNewsletter\Domain\Model\UserNewsletterOptions;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use Cylancer\CyNewsletter\Domain\Repository\FrontendUserRepository;
use Cylancer\CyNewsletter\Domain\Model\FrontendUser;
use Cylancer\CyNewsletter\Domain\Model\ValidationResults;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Cylancer\Usertools\Service\FrontendUserService;

/**
 * This file is part of the "Newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 Clemens Gogolin <service@cylancer.net>
 * 
 * @package Cylancer\Usertools\Controller;
 */
class UserSettingsController extends ActionController
{

    const CURRENT_USER = 'currentUser';

    const VALIDATIOPN_RESULTS = 'validationResults';

    const NEWSLETTER_OPTIONS = 'newsletterOptions';

    /** @var FrontendUserRepository   */
    private $frontendUserRepository = null;

    /** @var FrontendUserService **/
    private $frontendUserService;

    private $_validationResults = null;

    private function getValidationResults()
    {
        if ($this->_validationResults == null) {
            $this->_validationResults = ($this->request->hasArgument(UserSettingsController::VALIDATIOPN_RESULTS)) ? //
            $this->request->getArgument(UserSettingsController::VALIDATIOPN_RESULTS) : //
            new ValidationResults();
        }
        return $this->_validationResults;
    }

    public function __construct(FrontendUserRepository $frontendUserRepository, FrontendUserService $frontendUserService)
    {
        $this->frontendUserRepository = $frontendUserRepository;
        $this->frontendUserService = $frontendUserService;
    }

    /**
     *
     * @param FrontendUser $user
     * @return NULL|Object
     *
     */
    public function saveAction(FrontendUser $currentUser = null): ?Object
    {
        /** @var ValidationResults $validationResults **/
        $validationResults = $this->getValidationResults();

        if ($currentUser == null) {
            return GeneralUtility::makeInstance(ForwardResponse::class, 'show');
        } else if ($this->frontendUserService->isLogged()) {
            if ($this->frontendUserService->getCurrentUserUid() != $currentUser->getUid()) {
                $validationResults->addError('onlyCurrentUserIsEditable');
            }
        } else {
            $validationResults->addError('notLogged');
        }

        if (! key_exists($currentUser->getNewsletterSetting(), UserNewsletterOptions::LABEL)) {
            $validationResults->addError('invalidNewsletterSetting');
        }

        if (! $validationResults->hasErrors()) {
            $this->frontendUserRepository->update($currentUser);
            $validationResults->addInfo('savingSuccessful');
        }
        return GeneralUtility::makeInstance(ForwardResponse::class, 'save')->withArguments([
            UserSettingsController::VALIDATIOPN_RESULTS => $validationResults
        ]);
    }

    /**
     * Show the user settings
     *
     * @return NULL|Object
     */
    public function showAction(): ?Object
    {
        $validationResults = $this->getValidationResults();

        if ($this->frontendUserService->isLogged()) {
            $this->view->assign(UserSettingsController::CURRENT_USER, $this->frontendUserService->getCurrentUser());
            $this->view->assign(UserSettingsController::NEWSLETTER_OPTIONS, $this->getTranslatedNewsletterOptions());
        } else {
            $validationResults->addError('notLogged');
        }
        $this->view->assign(UserSettingsController::VALIDATIOPN_RESULTS, $validationResults);
        return null;
    }

    /**
     *
     * @return string[]
     */
    private function getTranslatedNewsletterOptions(): array
    {
        $return = array();

        foreach (UserNewsletterOptions::LABEL as $k => $v) {
            $return[$k] = LocalizationUtility::translate('editProfile.form.newsletterOption.' . $v, 'CyNewsLetter');
        }

        return $return;
    }
}
