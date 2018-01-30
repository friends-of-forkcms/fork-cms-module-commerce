<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Catalog\Domain\ProductOption\Event\DeletedProductOption;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\ProductOption\Command\DeleteProductOption as DeleteCommand;
use Backend\Modules\Catalog\Domain\SpecificationValue\Exception\SpecificationValueNotFound;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * This action will delete a product option value
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class DeleteProductOption extends BackendBaseActionDelete
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

        $productOption = $this->getProductOption((int)$deleteFormData['id']);

        try {
            // The command bus will handle the saving of the content block in the database.
            $this->get('command_bus')->handle(new DeleteCommand($productOption));

            $this->get('event_dispatcher')->dispatch(
                DeletedProductOption::EVENT_NAME,
                new DeletedProductOption($productOption)
            );

            $this->redirect(
                $this->getBackLink(
                    [
                        'id' => $productOption->getProduct()->getId(),
                        'report' => 'deleted',
                        'var' => $productOption->getTitle()
                    ]
                ) . '#tabOptions'
            );
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->redirect($this->getBackLink(['error' => 'products-connected']));
        }
    }


    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Edit',
            null,
            null,
            $parameters
        );
    }

    private function getProductOption(int $id): ProductOption
    {
        try {
            return $this->get('catalog.repository.product_option')->findOneById(
                $id
            );
        } catch (SpecificationValueNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }
}
