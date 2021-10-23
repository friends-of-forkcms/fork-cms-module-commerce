<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\CartRule\CartRule;
use Backend\Modules\Commerce\Domain\CartRule\CartRuleRepository;
use Backend\Modules\Commerce\Domain\CartRule\CartRuleType;
use Backend\Modules\Commerce\Domain\CartRule\Command\UpdateCartRule;
use Backend\Modules\Commerce\Domain\CartRule\Event\CartRuleUpdated;
use Backend\Modules\Commerce\Domain\CartRule\Exception\CartRuleNotFound;
use Symfony\Component\Form\Form;

class EditCartRule extends BackendBaseActionEdit
{
    public function execute(): void
    {
        parent::execute();

        $cartRule = $this->getCartRule();

        $form = $this->getForm($cartRule);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $cartRule->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteCartRule',
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('cartRule', $cartRule);

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateCartRule $updateCartRule */
        $updateCartRule = $this->updateCartRule($form);

        $this->get('event_dispatcher')->dispatch(
            CartRuleUpdated::EVENT_NAME,
            new CartRuleUpdated($updateCartRule->getCartRuleEntity())
        );

        $this->redirect(
            $this->getBackLink([
                'report' => 'edited',
                'var' => $updateCartRule->title,
                'highlight' => 'row-' . $updateCartRule->getCartRuleEntity()->getId(),
            ])
        );
    }

    private function getCartRule(): CartRule
    {
        /** @var CartRuleRepository cartRuleRepository */
        $cartRuleRepository = $this->get('commerce.repository.cart_rule');

        try {
            return $cartRuleRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (CartRuleNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'CartRules',
            null,
            null,
            $parameters
        );
    }

    private function getForm(CartRule $cartRule): Form
    {
        $form = $this->createForm(
            CartRuleType::class,
            new UpdateCartRule($cartRule),
            [
                'validation_groups' => ['Default', 'Edit'],
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateCartRule(Form $form): UpdateCartRule
    {
        /** @var UpdateCartRule $updateCartRule */
        $updateCartRule = $form->getData();

        $this->get('command_bus')->handle($updateCartRule);

        return $updateCartRule;
    }
}
