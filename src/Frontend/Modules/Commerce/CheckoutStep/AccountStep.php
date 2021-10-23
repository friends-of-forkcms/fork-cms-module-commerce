<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

use Backend\Modules\Commerce\Domain\Account\AccountCustomerDataTransferObject;
use Backend\Modules\Commerce\Domain\Account\AccountCustomerType;
use Backend\Modules\Commerce\Domain\Account\Command\CreateAccount;
use Backend\Modules\Commerce\Domain\Account\Command\UpdateAccount;
use Backend\Modules\Commerce\Domain\Account\Event\Created;
use Backend\Modules\Commerce\Domain\Account\Event\Updated;
use Backend\Modules\Commerce\Domain\Cart\Event\CartUpdated;
use Backend\Modules\Commerce\Domain\OrderAddress\Command\CreateOrderAddress;
use Backend\Modules\Commerce\Domain\OrderAddress\Command\UpdateOrderAddress;
use Common\Core\Model;
use Common\Uri;
use Frontend\Core\Language\Language;
use Frontend\Modules\Profiles\Engine\Authentication;
use Frontend\Modules\Profiles\Engine\Model as FrontendProfilesModel;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;

/**
 * The AccountStep is shown when the user chooses to continue as guest and has to fill in his user and address details
 * with optional possibility to enter a password to create an account.
 */
class AccountStep extends Step
{
    public static string $stepIdentifier = 'account';
    protected bool $reachable = true;
    private Form $accountForm;

    public function init(): void
    {
        $this->setStepName(Language::lbl('Address'));

        if ($this->cart->getAccount()) {
            $this->complete = true;
        }
    }

    /**
     * @throws ChangeStepException
     */
    public function execute(): void
    {
        $this->accountForm = $this->handleAccountForm($this->getAccountForm());

        if ($this->accountForm->isSubmitted()) {
            if ($this->accountForm->isValid()) {
                $this->goToNextStep();
            } else {
                $this->complete = false;
            }
        }

        if ($this->getNextStep()) {
            $this->getNextStep()->invalidateStep();
        }
    }

    public function render(): string
    {
        $this->template->assign('accountForm', $this->accountForm->createView());

        return parent::render();
    }

    private function getAccountForm(): Form
    {
        $accountData = new CreateAccount();
        $accountData->shipment_address = new CreateOrderAddress();
        $accountData->invoice_address = new CreateOrderAddress();

        if ($this->cart->getAccount()) {
            $accountData = new UpdateAccount($this->cart->getAccount());

            if ($this->cart->getShipmentAddress()) {
                $accountData->shipment_address = new UpdateOrderAddress($this->cart->getShipmentAddress());
            }

            if ($this->cart->getInvoiceAddress()) {
                $accountData->same_invoice_address = false;
                $accountData->invoice_address = new UpdateOrderAddress($this->cart->getInvoiceAddress());
            } else {
                $accountData->invoice_address = new CreateOrderAddress();
            }
        }

        $form = $this->createForm(AccountCustomerType::class, $accountData);

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function handleAccountForm(Form $form): Form
    {
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var CreateAccount $createAccount
             */
            $createAccount = $form->getData();

            // When there is a password set check if the user exists
            if ($createAccount->password && FrontendProfilesModel::existsByEmail($createAccount->email_address)) {
                $form->get('email_address')->addError(new FormError(Language::getError('EmailExists')));

                return $form;
            }

            if ($createAccount->password) {
                $profile = [
                    'email' => $createAccount->email_address,
                    'password' => FrontendProfilesModel::encryptPassword($createAccount->password),
                    'status' => 'active',
                    'display_name' => '',
                    'registered_on' => Model::getUTCDate(),
                    'last_login' => Model::getUTCDate(null, 0),
                    'url' => '',
                ];

                $profile['id'] = FrontendProfilesModel::insert($profile);

                $createAccount->profile_id = $profile['id'];
                Authentication::login($profile['id'], true);
            }

            // The command bus will handle the saving of the account in the database.
            if ($this->cart->getAccount()) {
                $this->get('command_bus')->handle($createAccount);
                $this->get('event_dispatcher')->dispatch(
                    Updated::EVENT_NAME,
                    new Updated($createAccount->getAccountEntity())
                );
            } else {
                $this->get('command_bus')->handle($createAccount);
                $this->get('event_dispatcher')->dispatch(
                    Created::EVENT_NAME,
                    new Created($createAccount->getAccountEntity())
                );

                $this->cart->setAccount($createAccount->getAccountEntity());
            }

            $this->saveShipmentAddress($createAccount);
            $this->saveInvoiceAddress($createAccount);

            $this->getCartRepository()->save($this->cart);

            $this->get('event_dispatcher')->dispatch(
                CartUpdated::EVENT_NAME,
                new CartUpdated($this->cart)
            );

            parent::invalidateStep();
        }

        return $form;
    }

    private function saveShipmentAddress(AccountCustomerDataTransferObject $data): void
    {
        $account = $this->cart->getAccount();
        $data->shipment_address->account = $account;
        $data->shipment_address->first_name = $account->getFirstName();
        $data->shipment_address->last_name = $account->getLastName();
        $data->shipment_address->company_name = $account->getCompanyName();

        if ($this->cart->getShipmentAddress()) {
            $this->get('command_bus')->handle($data->shipment_address);
        } else {
            $this->get('command_bus')->handle($data->shipment_address);
            $this->cart->setShipmentAddress($data->shipment_address->getOrderAddressEntity());
        }
    }

    private function saveInvoiceAddress(AccountCustomerDataTransferObject $data): void
    {
        if ($data->same_invoice_address) {
            $this->cart->setInvoiceAddress(null);

            return;
        }

        $account = $this->cart->getAccount();
        $data->invoice_address->account = $account;
        $data->invoice_address->first_name = $account->getFirstName();
        $data->invoice_address->last_name = $account->getLastName();
        $data->invoice_address->company_name = $account->getCompanyName();

        if ($this->cart->getInvoiceAddress()) {
            $this->get('command_bus')->handle($data->invoice_address);
        } else {
            $this->get('command_bus')->handle($data->invoice_address);
            $this->cart->setInvoiceAddress($data->invoice_address->getOrderAddressEntity());
        }
    }

    public function getUrl(): ?string
    {
        return parent::getUrl() . '/' . Uri::getUrl(Language::lbl('Address'));
    }
}
