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
use Cylancer\CyNewsletter\Service\FrontendUserService;
use Psr\Http\Message\ResponseInterface;

/**
 * This file is part of the "cy_newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 * 
 */
class UserSettingsController extends ActionController
{

    private const CURRENT_USER = 'currentUser';

    private const VALIDATIOPN_RESULTS = 'validationResults';

    private const NEWSLETTER_OPTIONS = 'newsletterOptions';

    private ?ValidationResults $_validationResults = null;

    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly FrontendUserService $frontendUserService
    ) {
    }

    private function getValidationResults(): ValidationResults
    {
        if ($this->_validationResults == null) {
            $this->_validationResults = ($this->request->hasArgument(UserSettingsController::VALIDATIOPN_RESULTS)) ? //
                $this->request->getArgument(UserSettingsController::VALIDATIOPN_RESULTS) : //
                new ValidationResults();
        }
        return $this->_validationResults;
    }


    public function saveAction(FrontendUser $currentUser = null): ResponseInterface
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

        if (!key_exists($currentUser->getNewsletterSetting(), UserNewsletterOptions::LABEL)) {
            $validationResults->addError('invalidNewsletterSetting');
        }

        if (!$validationResults->hasErrors()) {
            $this->frontendUserRepository->update($currentUser);
            $validationResults->addInfo('savingSuccessful');
        }
        return GeneralUtility::makeInstance(ForwardResponse::class, 'save')->withArguments([
            UserSettingsController::VALIDATIOPN_RESULTS => $validationResults
        ]);
    }

    public function showAction(): ResponseInterface
    {
        $validationResults = $this->getValidationResults();

        if ($this->frontendUserService->isLogged()) {
            $this->view->assign(UserSettingsController::CURRENT_USER, $this->frontendUserService->getCurrentUser());
            $this->view->assign(UserSettingsController::NEWSLETTER_OPTIONS, $this->getTranslatedNewsletterOptions());
        } else {
            $validationResults->addError('notLogged');
        }
        $this->view->assign(UserSettingsController::VALIDATIOPN_RESULTS, $validationResults);
        return $this->htmlResponse();
    }

    private function getTranslatedNewsletterOptions(): array
    {
        $return = [];

        foreach (UserNewsletterOptions::LABEL as $k => $v) {
            $return[$k] = LocalizationUtility::translate('userSettings.form.newsletterOption.' . $v, 'CyNewsletter');
        }
        return $return;
    }
}
