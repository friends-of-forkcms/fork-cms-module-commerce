<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\ProductOption\Exception\ProductOptionNotFound;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOptionRepository;
use Backend\Modules\Commerce\Domain\ProductOption\Command\UpdateProductOption;
use Backend\Modules\Commerce\Domain\ProductOption\Event\UpdatedProductOption;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOptionType;
use Backend\Modules\Commerce\Domain\ProductOptionValue\DataGrid;
use Symfony\Component\Form\Form;

/**
 * This is the edit productoption-action, it will display a form to edit a product option
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditProductOption extends BackendBaseActionEdit
{
    /**
     * @var ProductOption
     */
    private $productOption;

    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $this->productOption = $this->getProductOption();

        $form = $this->getForm($this->productOption);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $this->productOption->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteProductOption',
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if ( ! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('productOption', $this->productOption);
            $this->template->assign('productOptionValuesDataGrid', DataGrid::getHtml($this->productOption));
            $this->template->assign('backLink', $this->getBackLink());

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateProductOption $updateProductOption */
        $updateProductOption = $this->updateProductOption($form);

        $this->get('event_dispatcher')->dispatch(
            UpdatedProductOption::EVENT_NAME,
            new UpdatedProductOption($updateProductOption->getProductOptionEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'id'        => $updateProductOption->getProductOptionEntity()->getProduct()->getId(),
                    'report'    => 'edited',
                    'highlight' => 'row-' . $updateProductOption->getProductOptionEntity()->getId(),
                ]
            )
        );
    }

    private function getProductOption(): ProductOption
    {
        /** @var ProductOptionRepository $productOptionRepository */
        $productOptionRepository = $this->get('commerce.repository.product_option');

        try {
            return $productOptionRepository->findOneById(
                $this->getRequest()->query->getInt('id')
            );
        } catch (ProductOptionNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        if ($this->productOption->getParentProductOptionValue()) {
            $parameters = array_merge($parameters, [
                'id' => $this->productOption->getParentProductOptionValue()->getId(),
            ]);

            return BackendModel::createUrlForAction(
                    'EditProductOptionValue',
                    null,
                    null,
                    $parameters
                ) . '#tabSubOptions';
        }

        $parameters = array_merge($parameters, [
            'id' => $this->productOption->getProduct()->getId(),
        ]);

        return BackendModel::createUrlForAction(
            'Edit',
            null,
            null,
            $parameters
        ) . '#tabOptions';
    }

    private function getForm(ProductOption $productOption): Form
    {
        $form = $this->createForm(
            ProductOptionType::class,
            new UpdateProductOption($productOption),
            [
                'product' => $this->productOption->getProduct(),
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateProductOption(Form $form): UpdateProductOption
    {
        /** @var UpdateProductOption $updateProductOption */
        $updateProductOption = $form->getData();

        // The command bus will handle the saving of the specification in the database.
        $this->get('command_bus')->handle($updateProductOption);

        return $updateProductOption;
    }
}
