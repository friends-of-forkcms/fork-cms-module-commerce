<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\Vat\Command\UpdateVat;
use Backend\Modules\Commerce\Domain\Vat\Event\Updated;
use Backend\Modules\Commerce\Domain\Vat\Exception\VatNotFound;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\Domain\Vat\VatRepository;
use Backend\Modules\Commerce\Domain\Vat\VatType;
use Symfony\Component\Form\Form;

/**
 * This is the edit vat-action, it will display a form to edit a vat.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditVat extends BackendBaseActionEdit
{
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
                'action' => 'DeleteVat',
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('vat', $vat);

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateVat $updateVat */
        $updateVat = $this->updateVat($form);

        $this->get('event_dispatcher')->dispatch(
            Updated::EVENT_NAME,
            new Updated($updateVat->getVatEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'edited',
                    'var' => $updateVat->title,
                    'highlight' => 'row-' . $updateVat->getVatEntity()->getId(),
                ]
            )
        );
    }

    private function getVat(): Vat
    {
        /** @var VatRepository $vatRepository */
        $vatRepository = $this->get('commerce.repository.vat');

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
            new UpdateVat($vat)
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateVat(Form $form): UpdateVat
    {
        /** @var UpdateVat $updateVat */
        $updateVat = $form->getData();

        // The command bus will handle the saving of the vat in the database.
        $this->get('command_bus')->handle($updateVat);

        return $updateVat;
    }
}
