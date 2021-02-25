<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\ProductOptionValue\Event\DeletedProductOptionValue;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Commerce\Domain\ProductOptionValue\Command\DeleteProductOptionValue as DeleteCommand;
use Backend\Modules\Commerce\Domain\SpecificationValue\Exception\SpecificationValueNotFound;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * This action will delete a product option value
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class DeleteProductOptionValue extends BackendBaseActionDelete
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        $deleteForm = $this->createForm(DeleteType::class, null, ['module' => $this->getModule()]);
        $deleteForm->handleRequest($this->getRequest());
        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            $this->redirect(
                BackendModel::createUrlForAction(
                    'Index',
                    null,
                    null,
                    [
                        'error' => 'non-existing',
                    ]
                )
            );

            return;
        }
        $deleteFormData = $deleteForm->getData();

        $productOptionValue = $this->getProductOptionValue((int)$deleteFormData['id']);

        try {
            // The command bus will handle the saving of the content block in the database.
            $this->get('command_bus')->handle(new DeleteCommand($productOptionValue));

            $this->get('event_dispatcher')->dispatch(
                DeletedProductOptionValue::EVENT_NAME,
                new DeletedProductOptionValue($productOptionValue)
            );

            $this->redirect(
                $this->getBackLink(
                    [
                        'id' => $productOptionValue->getProductOption()->getId(),
                        'report' => 'deleted',
                        'var' => $productOptionValue->getTitle()
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
            'EditProductOption',
            null,
            null,
            $parameters
        );
    }

    private function getProductOptionValue(int $id): ProductOptionValue
    {
        try {
            return $this->get('commerce.repository.product_option_value')->findOneById(
                $id
            );
        } catch (SpecificationValueNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }
}
