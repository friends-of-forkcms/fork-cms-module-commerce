<?php

namespace Frontend\Modules\Catalog\CheckoutStep;

use Backend\Modules\Catalog\Domain\Account\AccountCustomerDataTransferObject;
use Backend\Modules\Catalog\Domain\Account\AccountCustomerType;
use Backend\Modules\Catalog\Domain\Account\Command\CreateAccount;
use Backend\Modules\Catalog\Domain\Account\Command\UpdateAccount;
use Backend\Modules\Catalog\Domain\Account\Event\Created;
use Backend\Modules\Catalog\Domain\Account\Event\Updated;
use Backend\Modules\Catalog\Domain\Cart\Event\CartUpdated;
use Backend\Modules\Catalog\Domain\OrderAddress\Command\CreateOrderAddress;
use Backend\Modules\Catalog\Domain\OrderAddress\Command\UpdateOrderAddress;
use Common\Core\Model;
use Common\Uri;
use Frontend\Core\Language\Language;
use Frontend\Modules\Profiles\Engine\Authentication;
use Frontend\Modules\Profiles\Engine\Model as FrontendProfilesModel;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;

class Account extends Step
{
    public static $stepIdentifier = 'account';

    protected $reachable = true;

    /**
     * @var Form
     */
    private $accountForm;

    public function init()
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

    public function render()
    {
        $this->template->assign('accountForm', $this->accountForm->createView());
        $this->template->assign('cart', $this->cart);

        return parent::render();
    }

    /**
     * Get the account form
     *
     * @return Form
     */
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

            // When there is an password set check if the user exists
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

    /**
     * @param AccountCustomerDataTransferObject $data
     */
    private function saveShipmentAddress(AccountCustomerDataTransferObject $data)
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

    /**
     * @param AccountCustomerDataTransferObject $data
     */
    private function saveInvoiceAddress(AccountCustomerDataTransferObject $data)
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
