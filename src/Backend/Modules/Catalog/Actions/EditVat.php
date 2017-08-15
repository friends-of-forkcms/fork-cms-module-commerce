<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Catalog\Domain\Vat\Exception\VatNotFound;
use Backend\Modules\Catalog\Domain\Vat\Exception\VatValueNotFound;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\Catalog\Domain\Vat\VatType;
use Backend\Modules\Catalog\Domain\Vat\VatRepository;
use Backend\Modules\Catalog\Domain\Vat\Command\Update;
use Backend\Modules\Catalog\Domain\Vat\Event\Updated;
use Symfony\Component\Form\Form;

/**
 * This is the edit vat-action, it will display a form to edit a vat
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditVat extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $vat = $this->getVat();

        $form = $this->getForm($vat);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $vat->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteVat'
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if ( ! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('vat', $vat);

            $this->parse();
            $this->display();

            return;
        }

        /** @var Update $updateVat */
        $updateVat = $this->updateVat($form);

        $this->get('event_dispatcher')->dispatch(
            Updated::EVENT_NAME,
            new Updated($updateVat->getVatEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report'    => 'edited',
                    'var'       => $updateVat->title,
                    'highlight' => 'row-' . $updateVat->getVatEntity()->getId(),
                ]
            )
        );
    }

    private function getVat(): Vat
    {
        /** @var VatRepository $vatRepository */
        $vatRepository = $this->get('catalog.repository.vat');

        try {
            return $vatRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (VatNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
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

    private function getForm(Vat $vat): Form
    {
        $form = $this->createForm(
            VatType::class,
            new Update($vat)
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateVat(Form $form): Update
    {
        /** @var Update $updateVat */
        $updateVat = $form->getData();

        // The command bus will handle the saving of the vat in the database.
        $this->get('command_bus')->handle($updateVat);

        return $updateVat;
    }
}
