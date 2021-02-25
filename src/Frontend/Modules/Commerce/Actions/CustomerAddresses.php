<?php

namespace Frontend\Modules\Commerce\Actions;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Account\AccountRepository;
use Backend\Modules\Commerce\Domain\OrderAddress\Command\CreateOrderAddress;
use Backend\Modules\Commerce\Domain\OrderAddress\Command\UpdateOrderAddress;
use Backend\Modules\Commerce\Domain\OrderAddress\Event\Created;
use Backend\Modules\Commerce\Domain\OrderAddress\Event\Updated;
use Backend\Modules\Commerce\Domain\OrderAddress\Exception\OrderAddressNotFound;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddress;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddressDataTransferObject;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddressRepository;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddressType;
use Common\Exception\RedirectException;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Language\Language;
use Frontend\Modules\Profiles\Engine\Authentication as FrontendProfilesAuthentication;
use Frontend\Modules\Profiles\Engine\Profile;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

class CustomerAddresses extends FrontendBaseBlock
{
    /**
     * @var Profile
     */
    private $profile;

    /**
     * @var Account
     */
    private $account;

    /**
     * @var OrderAddress
     */
    private $address;

    /**
     * Execute the action
     *
     * @throws RedirectException
     * @throws \Exception
     */
    public function execute(): void
    {
        if (!FrontendProfilesAuthentication::isLoggedIn()) {
            throw new InsufficientAuthenticationException('You need to log in to change your email');
        }

        parent::execute();

        $this->profile = FrontendProfilesAuthentication::getProfile();
        $this->account = $this->getAccountRepository()->findOneByProfile($this->profile);

        if ($this->getRequest()->query->has('add')) {
            $this->add();
        } elseif ($this->getRequest()->query->has('edit')) {
            try {
                $this->address = $this->getOrderAddressRepository()->findByIdAndAccount(
                    $this->getRequest()->query->getInt('edit'),
                    $this->account
                );

                $this->edit();
            } catch (OrderAddressNotFound $e) {
                $this->redirect(Navigation::getUrlForBlock($this->getModule(), $this->getAction()));
            }
        } elseif ($this->getRequest()->query->has('delete')) {
            try {
                $this->address = $this->getOrderAddressRepository()->findByIdAndAccount(
                    $this->getRequest()->query->getInt('delete'),
                    $this->account
                );

                $this->delete();
            } catch (OrderAddressNotFound $e) {
                $this->redirect(Navigation::getUrlForBlock($this->getModule(), $this->getAction()));
            }
        } else {
            $this->overview();
        }
    }

    private function overview(): void
    {
        $this->loadTemplate();
        $this->template->assign('account', $this->account);
    }

    /**
     * @throws RedirectException
     */
    private function add(): void
    {
        $form = $this->getForm(new CreateOrderAddress());

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var CreateOrderAddress $data
             */
            $data = $form->getData();
            $data->account = $this->account;

            $this->get('command_bus')->handle($data);
            $this->get('event_dispatcher')->dispatch(
                Updated::EVENT_NAME,
                new Updated($data->getOrderAddressEntity())
            );

            $this->redirect(Navigation::getUrlForBlock($this->getModule(), $this->getAction()));
        }

        $this->loadTemplate('CustomerAddressAdd');

        $this->template->assign('account', $this->account);
        $this->template->assign('form', $form->createView());

        $this->breadcrumb->addElement(ucfirst(Language::lbl('Add')));
    }

    /**
     * @throws RedirectException
     */
    private function edit(): void
    {
        $form = $this->getForm(new UpdateOrderAddress($this->address));

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->get('command_bus')->handle($data);
            $this->get('event_dispatcher')->dispatch(
                Updated::EVENT_NAME,
                new Updated($data->getOrderAddressEntity())
            );

            $this->redirect(Navigation::getUrlForBlock($this->getModule(), $this->getAction()));
        }

        $this->loadTemplate('CustomerAddressEdit');

        $this->template->assign('account', $this->account);
        $this->template->assign('address', $this->address);
        $this->template->assign('form', $form->createView());

        $this->breadcrumb->addElement(ucfirst(Language::lbl('Edit')));
    }

    /**
     * @throws RedirectException
     */
    private function delete(): void
    {
        $this->getOrderAddressRepository()->remove($this->address);
        $this->redirect(Navigation::getUrlForBlock($this->getModule(), $this->getAction()));
    }

    private function getForm(OrderAddressDataTransferObject $data): Form
    {
        $form = $this->createForm(
            OrderAddressType::class,
            $data
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    /**
     * @param string $path The path for the template to use.
     * @param bool $overwrite Should the template overwrite the default?
     */
    protected function loadTemplate(string $path = null, bool $overwrite = false): void
    {
        // no template given, so we should build the path
        if ($path === null) {
            $path = $this->getAction();
        }
        $path = $this->getModule() . '/Layout/Templates/Customer/' . $path . '.html.twig';

        parent::loadTemplate($path, $overwrite);
    }

    private function getAccountRepository(): AccountRepository
    {
        return $this->get('commerce.repository.account');
    }

    private function getOrderAddressRepository(): OrderAddressRepository
    {
        return $this->get('commerce.repository.order_address');
    }
}
