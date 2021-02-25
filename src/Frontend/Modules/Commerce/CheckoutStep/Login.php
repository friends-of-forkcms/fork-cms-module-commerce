<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

use Backend\Modules\Commerce\Domain\Account\AccountLoginType;
use Backend\Modules\Commerce\Domain\Cart\Event\CartUpdated;
use Common\Uri;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Language as FL;
use Frontend\Modules\Commerce\CheckoutStep\Account as AccountStep;
use Frontend\Modules\Profiles\Engine\Authentication;
use Frontend\Modules\Profiles\Engine\Authentication as FrontendProfilesAuthentication;
use Frontend\Modules\Profiles\Engine\Model as FrontendProfilesModel;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;

class Login extends Step
{
    /**
     * @var string
     */
    public static $stepIdentifier = 'login';

    /**
     * @var bool
     */
    protected $reachable = true;

    /**
     * @var bool
     */
    protected $showBreadcrumbs = false;

    /**
     * @var Form
     */
    private $loginForm;

    public function init()
    {
        $this->setStepName('Login');

        // This step is always complete
        $this->complete = true;
    }

    /**
     * @throws ChangeStepException
     */
    public function execute(): void
    {
        $this->loginForm = $this->handleLoginForm($this->getLoginForm());
    }

    public function render()
    {
        $this->template->assign('loginForm', $this->loginForm->createView());
        $this->template->assign('accountUrl', $this->getAccountUrl());

        return parent::render();
    }

    /**
     * Get the login form
     *
     * @return Form
     */
    private function getLoginForm(): Form
    {
        $form = $this->createForm(AccountLoginType::class);

        $form->handleRequest($this->getRequest());

        return $form;
    }

    /**
     * @param Form $form
     * @return Form
     * @throws ChangeStepException
     */
    private function handleLoginForm(Form $form): Form
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $password = $form->get('password')->getData();
            $remember = $form->get('remember')->getData();

            if (!FrontendProfilesModel::verifyPassword($email, $password)) {
                $errorString = sprintf(
                    Language::getError('Profiles' . \SpoonFilter::toCamelCase(FrontendProfilesAuthentication::LOGIN_INVALID) . 'Login'),
                    Navigation::getUrlForBlock('Profiles', 'ResendActivation')
                );

                $form->get('email')->addError(new FormError($errorString));

                return $form;
            }

            $loginStatus = FrontendProfilesAuthentication::getLoginStatus($email, $password);
            if ($loginStatus !== FrontendProfilesAuthentication::LOGIN_ACTIVE) {
                $errorString = sprintf(
                    FL::getError('Profiles' . \SpoonFilter::toCamelCase($loginStatus) . 'Login'),
                    FrontendNavigation::getUrlForBlock('Profiles', 'ResendActivation')
                );

                $form->get('email')->addError(new FormError($errorString));

                return $form;
            }

            // We did some checks now need to check if the form is still valid
            if ($form->isValid()) {
                $profileId = FrontendProfilesModel::getIdByEmail($email);

                FrontendProfilesModel::setSetting($profileId, 'login_attempts', 0);
                Authentication::login($profileId, $remember);

                // Assign cart to account
                $this->cart->setAccount($this->getAccount());
                $this->getCartRepository()->save($this->cart);

                $this->get('event_dispatcher')->dispatch(
                    CartUpdated::EVENT_NAME,
                    new CartUpdated($this->cart)
                );

                $this->goToNextStep();

                return $form;
            }
        }

        return $form;
    }

    private function getAccountUrl(): string
    {
        $accountStep = new AccountStep();

        return $accountStep->getUrl();
    }

    public function getUrl(): ?string
    {
        return parent::getUrl() . '/' . Uri::getUrl(Language::lbl('Login'));
    }
}
