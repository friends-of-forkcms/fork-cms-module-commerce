<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

use Backend\Modules\Commerce\Domain\Cart\Event\CartUpdated;
use Backend\Modules\Commerce\Domain\OrderAddress\Command\CreateOrderAddress;
use Backend\Modules\Commerce\Domain\OrderAddress\Command\UpdateOrderAddress;
use Backend\Modules\Commerce\Domain\OrderAddress\Event\Created;
use Backend\Modules\Commerce\Domain\OrderAddress\Event\Updated;
use Backend\Modules\Commerce\Domain\OrderAddress\Exception\OrderAddressNotFound;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddressRepository;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddressType;
use Common\Uri;
use Frontend\Core\Language\Language;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;

class Addresses extends Step
{
    public static $stepIdentifier = 'addresses';

    /**
     * @var Form
     */
    private $form;

    /**
     * @var Form
     */
    private $addressForm;

    /**
     * @var OrderAddress
     */
    private $address;

    public function init()
    {
        $this->setStepName(Language::lbl('Address'));
        $this->reachable = $this->getAccount() !== null;

        if ($this->cart->getShipmentAddress()) {
            $this->complete = true;
        }
    }

    /**
     * @throws ChangeStepException
     */
    public function execute(): void
    {
        $this->nextStep->invalidateStep();

        if ($this->getRequest()->query->has('edit')) {
            try {
                $this->address = $this->getOrderAddressRepository()->findByIdAndAccount(
                    $this->getRequest()->query->getInt('edit'),
                    $this->getAccount()
                );
            } catch (OrderAddressNotFound $e) {
                throw new ChangeStepException($this, $this);
            }
        }

        if ($this->getRequest()->query->has('add') || $this->getRequest()->query->has('edit')) {
            $this->addressForm = $this->handleAddressForm($this->getAddressForm());

            return;
        }

        $this->form = $this->handleForm($this->getForm());

        if ($this->form->isSubmitted()) {
            if ($this->form->isValid()) {
                $this->goToNextStep();
            } else {
                $this->complete = false;
            }
        }
    }

    public function render()
    {
        if ($this->getRequest()->query->has('add') || $this->getRequest()->query->has('edit')) {
            $this->template->assign('form', $this->getAddressForm()->createView());
            $this->template->assign('address', $this->address);
            $this->template->assign('step', $this);

            return $this->template->getContent('Commerce/Layout/Templates/Checkout/Step/AddressForm.html.twig');
        } else {
            $this->template->assign('form', $this->form->createView());
        }

        return parent::render();
    }

    private function handleForm(Form $form): Form
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getNormData();
            $this->cart->setShipmentAddress($data->shipment_address);

            $invoiceAddress = null;
            if (!$data->same_invoice_address) {
                $invoiceAddress = $data->invoice_address;
            }
            $this->cart->setInvoiceAddress($invoiceAddress);

            if (!$this->cart->getAccount()) {
                $this->cart->setAccount($this->getAccount());
            }

            $this->getCartRepository()->save($this->cart);

            $this->get('event_dispatcher')->dispatch(
                CartUpdated::EVENT_NAME,
                new CartUpdated($this->cart)
            );

            $this->getNextStep()->invalidateStep();
        }

        return $form;
    }

    private function getFormData()
    {
        $data = new \stdClass();
        $data->shipment_address = $this->cart->getShipmentAddress();
        $data->invoice_address = $this->cart->getInvoiceAddress();
        $data->same_invoice_address = true;

        if ($data->invoice_address) {
            $data->same_invoice_address = $data->shipment_address->getId() == $data->invoice_address->getId();
        }

        return $data;
    }

    private function getForm(): Form
    {
        $form = $this->createFormBuilder($this->getFormData())->add(
            'shipment_address',
            EntityType::class,
            [
                'class' => OrderAddress::class,
                'label' => 'lbl.ShipmentAddress',
                'choice_label' => function (OrderAddress $address) {
                    return $address->getFirstName();
                },
                'choices' => $this->getAccount()->getAddresses(),
                'block_name' => 'order_address',
                'expanded' => true,
                'constraints' => [new Required(), new NotBlank()],
                'attr' => [
                    'edit_link' => $this->getUrl().'?edit=',
                ],
            ]
        )->add(
            'invoice_address',
            EntityType::class,
            [
                'class' => OrderAddress::class,
                'label' => 'lbl.InvoiceAddress',
                'choice_label' => function (OrderAddress $address) {
                    return $address->getFirstName();
                },
                'choices' => $this->getAccount()->getAddresses(),
                'block_name' => 'order_address',
                'expanded' => true,
                'attr' => [
                    'edit_link' => $this->getUrl().'?edit=',
                ],
            ]
        )->add(
            'same_invoice_address',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'lbl.SameInvoiceAddress',
            ]
        )->getForm();

        // Assign current request to form
        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function handleAddressForm(Form $form): Form
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->account = $this->getAccount();

            if ($this->getRequest()->query->has('edit')) {
                $this->get('command_bus')->handle($data);
                $this->get('event_dispatcher')->dispatch(
                    Updated::EVENT_NAME,
                    new Updated($data->getOrderAddressEntity())
                );
            } else {
                $this->get('command_bus')->handle($data);
                $this->get('event_dispatcher')->dispatch(
                    Created::EVENT_NAME,
                    new Created($data->getOrderAddressEntity())
                );
            }

            throw new ChangeStepException($this, $this);
        }

        return $form;
    }

    private function getAddressForm(): Form
    {
        $addressData = new CreateOrderAddress();

        if ($this->address) {
            $addressData = new UpdateOrderAddress($this->address);
        }

        $form = $this->createForm(OrderAddressType::class, $addressData);

        $form->handleRequest($this->getRequest());

        return $form;
    }

    public function getUrl(): ?string
    {
        return parent::getUrl().'/'.Uri::getUrl(Language::lbl('Addresses'));
    }

    private function getOrderAddressRepository(): OrderAddressRepository
    {
        return $this->get('commerce.repository.order_address');
    }
}
