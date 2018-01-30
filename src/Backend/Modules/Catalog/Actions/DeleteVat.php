<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Catalog\Domain\Vat\Exception\VatNotFound;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\Catalog\Domain\Vat\Event\Deleted;
use Backend\Modules\Catalog\Domain\Vat\Command\DeleteVat as DeleteCommand;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * This action will delete a vat
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class DeleteVat extends BackendBaseActionDelete
{
    /**
     * Execute the action
     */
    public function execute(): void
    {

        $deleteForm = $this->createForm(DeleteType::class, null, ['module' => $this->getModule()]);
        $deleteForm->handleRequest($this->getRequest());
        if ( ! $deleteForm->isSubmitted() || ! $deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $vat = $this->getVat((int)$deleteFormData['id']);

        try {
            // The command bus will handle the saving of the content block in the database.
            $this->get('command_bus')->handle(new DeleteCommand($vat));

            $this->get('event_dispatcher')->dispatch(
                Deleted::EVENT_NAME,
                new Deleted($vat)
            );

            $this->redirect($this->getBackLink(['report' => 'deleted', 'var' => $vat->getTitle()]));
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->redirect($this->getBackLink(['error' => 'products-connected']));
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

    private function getVat(int $id): Vat
    {
        try {
            return $this->get('catalog.repository.vat')->findOneByIdAndLocale(
                $id,
                Locale::workingLocale()
            );
        } catch (VatNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }
}
