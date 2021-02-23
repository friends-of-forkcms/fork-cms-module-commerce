<?php

namespace Frontend\Modules\Commerce\Actions;

use Backend\Modules\Commerce\Domain\Account\AccountCustomerDataTransferObject;
use Backend\Modules\Commerce\Domain\Account\AccountCustomerType;
use Backend\Modules\Commerce\Domain\Account\Command\CreateAccount;
use Backend\Modules\Commerce\Domain\Account\Event\Created;
use Backend\Modules\Commerce\Domain\OrderAddress\Command\CreateOrderAddress;
use Common\Core\Model;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Language\Language;
use Frontend\Modules\Profiles\Engine\Authentication as FrontendProfilesAuthentication;
use Frontend\Modules\Profiles\Engine\Model as FrontendProfilesModel;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

/**
 * This controller allows to the user to register
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Register extends FrontendBaseBlock
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        if (FrontendProfilesAuthentication::isLoggedIn()) {
            throw new InsufficientAuthenticationException('You can\'t register when you are logged in');
        }

        $this->loadTemplate();
        if ($this->getRequest()->query->getBoolean('registered')) {
            $this->template->assign('registered', true);
            return;
        }

        $form = $this->handleForm($this->getForm());

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('hasFormError', $form->isSubmitted() && !$form->isValid());
            $this->template->assign('form', $form->createView());

            return;
        }

        $this->redirect($this->url->getQueryString() . '?registered=true');
    }

    /**
     * Get the account form
     *
     * @return Form
     */
    private function getForm(): Form
    {
        $accountData = new CreateAccount();
        $accountData->shipment_address = new CreateOrderAddress();

        $form = $this->createForm(
            AccountCustomerType::class,
            $accountData,
            [
                'add_invoice_address' => false,
                'password_required' => true,
                'validation_groups' => ['Default', 'register'],
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function handleForm(Form $form): Form
    {
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $form;
        }

        /**
         * @var CreateAccount $createAccount
         */
        $createAccount = $form->getData();

        // When there is an password set check if the user exists
        if (FrontendProfilesModel::existsByEmail($createAccount->email_address)) {
            $form->get('email_address')->addError(new FormError(Language::getError('EmailExists')));

            return $form;
        }

        $activationKey = FrontendProfilesModel::getEncryptedString(
            uniqid(microtime(), true),
            FrontendProfilesModel::getRandomString()
        );

        $profile = [
            'email' => $createAccount->email_address,
            'password' => FrontendProfilesModel::encryptPassword($createAccount->password),
            'status' => 'inactive',
            'display_name' => '',
            'registered_on' => Model::getUTCDate(),
            'last_login' => Model::getUTCDate(null, 0),
            'url' => '',
        ];

        $createAccount->profile_id = FrontendProfilesModel::insert($profile);

        FrontendProfilesModel::setSettings(
            $createAccount->profile_id,
            [
                'language' => LANGUAGE,
                'activation_key' => $activationKey,
            ]
        );

        $this->get('command_bus')->handle($createAccount);
        $this->get('event_dispatcher')->dispatch(
            Created::EVENT_NAME,
            new Created($createAccount->getAccountEntity())
        );

        $this->saveAddress($createAccount);

        return $form;
    }

    /**
     * @param AccountCustomerDataTransferObject $data
     */
    private function saveAddress(AccountCustomerDataTransferObject $data)
    {
        $account = $data->getAccountEntity();
        $data->shipment_address->account = $account;
        $data->shipment_address->first_name = $account->getFirstName();
        $data->shipment_address->last_name = $account->getLastName();
        $data->shipment_address->company_name = $account->getCompanyName();

        $this->get('command_bus')->handle($data->shipment_address);
    }
}
