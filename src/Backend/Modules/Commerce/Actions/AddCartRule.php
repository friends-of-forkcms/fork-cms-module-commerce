<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Commerce\Domain\CartRule\CartRuleType;
use Backend\Modules\Commerce\Domain\CartRule\Command\CreateCartRule;
use Backend\Modules\Commerce\Domain\CartRule\Event\CartRuleCreated;
use Symfony\Component\Form\Form;

/**
 * This is the add order-status-action, it will display a form to create a new order status.
 */
class AddCartRule extends BackendBaseActionAdd
{
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            $this->parse();
            $this->display();

            return;
        }

        $createCartRule = $this->createCartRule($form);

        $this->get('event_dispatcher')->dispatch(
            CartRuleCreated::EVENT_NAME,
            new CartRuleCreated($createCartRule->getCartRuleEntity())
        );

        $this->redirect(
            $this->getBackLink([
                'report' => 'added',
                'var' => $createCartRule->title,
            ])
        );
    }

    private function createCartRule(Form $form): CreateCartRule
    {
        $createCartRule = $form->getData();

        // The command bus will handle the saving of the brand in the database.
        $this->get('command_bus')->handle($createCartRule);

        return $createCartRule;
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

    private function getForm(): Form
    {
        $form = $this->createForm(
            CartRuleType::class,
            new CreateCartRule()
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
