<?php

namespace Frontend\Modules\Commerce\Actions;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Account\AccountRepository;
use Backend\Modules\Commerce\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\Order\OrderRepository;
use Common\Exception\RedirectException;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Language\Language;
use Frontend\Modules\Profiles\Engine\Profile;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class GuestOrderTracking extends FrontendBaseBlock
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
     * Execute the action.
     *
     * @throws RedirectException
     * @throws \Exception
     */
    public function execute(): void
    {
        parent::execute();

        if ($this->getRequest()->query->has('order_id') && $this->getRequest()->query->has('email')) {
            try {
                // We need an deleted entity, there for disable the softdelete
                $em = $this->get('doctrine.orm.entity_manager');
                $em->getFilters()->disable('softdeleteable');

                $order = $this->getOrderRepository()->findByIdAndEmailAddress(
                    $this->getRequest()->query->getInt('order_id'),
                    $this->getRequest()->query->get('email')
                );

                $this->detail($order);
            } catch (OrderNotFound $e) {
                $this->redirect(Navigation::getUrlForBlock($this->getModule(), $this->getAction()));
            }
        } else {
            $this->overview();
        }
    }

    private function overview(): void
    {
        $this->loadTemplate('GuestOrderTracking');

        $form = $this->getGuestOrderForm();
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            return;
        }

        $formData = $form->getData();
        $order = $this->getOrderRepository()->findByIdAndEmailAddress($formData['order_id'], $formData['email']);

        // Current customer has a profile redirect to profiles page
        if ($order->getAccount()->getProfileId()) {
            $this->redirect(
                Navigation::getUrlForBlock(
                    $this->getModule(),
                    'CustomerOrders'
                )
                .'?order_id='.$order->getId()
            );

            return;
        }

        // Redirect to detail page
        $this->redirect(
            Navigation::getUrlForBlock(
                $this->getModule(),
                $this->getAction()
            )
            .'?order_id='.$order->getId().'&email='.$formData['email']
        );
    }

    private function detail(Order $order): void
    {
        $this->loadTemplate('GuestOrderDetail');

        $this->template->assign('order', $order);

        $this->breadcrumb->addElement(ucfirst(Language::lbl('Order')).' - '.$order->getId());
    }

    private function getGuestOrderForm(): Form
    {
        $callback = function ($object, ExecutionContextInterface $context, $payload) {
            $formData = $context->getRoot()->getData();

            if (!$formData['email']) {
                return;
            }

            if ($formData['order_id'] === null) {
                return;
            }

            try {
                $this->getOrderRepository()->findByIdAndEmailAddress($formData['order_id'], $formData['email']);
            } catch (OrderNotFound $e) {
                $context->buildViolation(ucfirst(Language::err('CantFindOrderWithGivenData')))
                    ->addViolation();
            }
        };

        /**
         * @var Form $form
         */
        $form = $this->get('form.factory')->createNamed('guest_tracking')
            ->add(
                'order_id',
                NumberType::class,
                [
                    'label' => 'lbl.OrderNumber',
                    'required' => true,
                    'scale' => 0,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'err.FieldIsRequired',
                        ]),
                        new Callback($callback),
                    ],
                    'invalid_message' => 'err.ThisValueIsInvalid',
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'lbl.EmailAddress',
                    'required' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'err.FieldIsRequired',
                        ]),
                        new Email([
                            'message' => 'err.EmailIsInvalid',
                        ]),
                    ],
                ]
            );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    /**
     * @param string $path      the path for the template to use
     * @param bool   $overwrite Should the template overwrite the default?
     */
    protected function loadTemplate(string $path = null, bool $overwrite = false): void
    {
        // no template given, so we should build the path
        if ($path === null) {
            $path = $this->getAction();
        }
        $path = $this->getModule().'/Layout/Templates/Customer/'.$path.'.html.twig';

        parent::loadTemplate($path, $overwrite);
    }

    private function getAccountRepository(): AccountRepository
    {
        return $this->get('commerce.repository.account');
    }

    private function getOrderRepository(): OrderRepository
    {
        return $this->get('commerce.repository.order');
    }
}
