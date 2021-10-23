<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\SpecificationValue\Command\DeleteSpecificationValue as DeleteCommand;
use Backend\Modules\Commerce\Domain\SpecificationValue\Event\DeletedSpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\Exception\SpecificationValueNotFound;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * This action will delete a specification value.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class DeleteSpecificationValue extends BackendBaseActionDelete
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

        $specificationValue = $this->getSpecificationValue((int) $deleteFormData['id']);

        try {
            // The command bus will handle the saving of the content block in the database.
            $this->get('command_bus')->handle(new DeleteCommand($specificationValue));

            $this->get('event_dispatcher')->dispatch(
                DeletedSpecificationValue::EVENT_NAME,
                new DeletedSpecificationValue($specificationValue)
            );

            $this->redirect(
                $this->getBackLink(
                    [
                        'id' => $specificationValue->getSpecification()->getId(),
                        'report' => 'deleted',
                        'var' => $specificationValue->getValue(),
                    ]
                ) . '#tabValues'
            );
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->redirect($this->getBackLink(['error' => 'products-connected']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'EditSpecification',
            null,
            null,
            $parameters
        );
    }

    private function getSpecificationValue(int $id): SpecificationValue
    {
        try {
            return $this->get('commerce.repository.specification_value')->findOneById(
                $id
            );
        } catch (SpecificationValueNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }
}
