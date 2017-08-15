<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Catalog\Domain\Vat\VatType;
use Backend\Modules\Catalog\Domain\Vat\Command\Create;
use Backend\Modules\Catalog\Domain\Vat\Event\Created;
use Symfony\Component\Form\Form;

/**
 * This is the add vat-action, it will display a form to create a new vat
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AddVat extends BackendBaseActionAdd
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();
        if ( ! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());

            $this->parse();
            $this->display();

            return;
        }

        $createVat = $this->createVat($form);

        $this->get('event_dispatcher')->dispatch(
            Created::EVENT_NAME,
            new Created($createVat->getVatEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'added',
                    'var'    => $createVat->title,
                ]
            )
        );

        return;
    }

    private function createVat(Form $form): Create
    {
        $createVat = $form->getData();

        // The command bus will handle the saving of the brand in the database.
        $this->get('command_bus')->handle($createVat);

        return $createVat;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Vats',
            null,
            null,
            $parameters
        );
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            VatType::class,
            new Create()
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
