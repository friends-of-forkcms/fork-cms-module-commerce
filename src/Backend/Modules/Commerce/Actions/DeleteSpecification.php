<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\Specification\Command\DeleteSpecification as DeleteCommand;
use Backend\Modules\Commerce\Domain\Specification\Event\Deleted;
use Backend\Modules\Commerce\Domain\Specification\Exception\SpecificationValueNotFound;
use Backend\Modules\Commerce\Domain\Specification\Specification;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * This action will delete a specification.
 *
 * @author Tijs Verkoyen <tijs@verkoyen.eu>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Willem van Dam <w.vandam@jvdict.nl>
 */
class DeleteSpecification extends BackendBaseActionDelete
{
    public function execute(): void
    {
        $deleteForm = $this->createForm(DeleteType::class, null, ['module' => $this->getModule()]);
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $specification = $this->getSpecification((int) $deleteFormData['id']);

        try {
            // The command bus will handle the saving of the content block in the database.
            $this->get('command_bus')->handle(new DeleteCommand($specification));

            $this->get('event_dispatcher')->dispatch(
                Deleted::EVENT_NAME,
                new Deleted($specification)
            );

            $this->redirect($this->getBackLink(['report' => 'deleted', 'var' => $specification->getTitle()]));
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->redirect($this->getBackLink(['error' => 'products-connected']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Specifications',
            null,
            null,
            $parameters
        );
    }

    private function getSpecification(int $id): Specification
    {
        try {
            return $this->get('commerce.repository.specification')->findOneByIdAndLocale(
                $id,
                Locale::workingLocale()
            );
        } catch (SpecificationValueNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }
}
