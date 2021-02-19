<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Catalog\Domain\ProductOption\DataGrid as ProductOptionDataGrid;
use Backend\Modules\Catalog\Domain\ProductOptionValue\Exception\ProductOptionValueNotFound;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValueRepository;
use Backend\Modules\Catalog\Domain\ProductOptionValue\Command\UpdateProductOptionValue;
use Backend\Modules\Catalog\Domain\ProductOptionValue\Event\UpdatedProductOptionValue;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValueType;
use Symfony\Component\Form\Form;

/**
 * This is the edit productoption-action, it will display a form to edit a product option
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditProductOptionValue extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $productOptionValue = $this->getProductOptionValue();

        $form = $this->getForm($productOptionValue);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $productOptionValue->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteProductOptionValue',
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('productOptionValue', $productOptionValue);
            $this->template->assign('productOption', $productOptionValue->getProductOption());
            $this->template->assign(
                'productOptionsDataGrid',
                ProductOptionDataGrid::getHtmlProductOptionValue($productOptionValue)
            );
            $this->template->assign('backLink', $this->getBackLink([
                    'id' => $productOptionValue->getProductOption()->getId(),
                ]) . '#tabValues');

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateProductOptionValue $updateProductOptionValue */
        $updateProductOptionValue = $this->updateProductOptionValue($form);

        $this->get('event_dispatcher')->dispatch(
            UpdatedProductOptionValue::EVENT_NAME,
            new UpdatedProductOptionValue($updateProductOptionValue->getProductOptionValueEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'id' => $updateProductOptionValue->getProductOptionValueEntity()->getProductOption()->getId(),
                    'report' => 'edited',
                    'highlight' => 'row-' . $updateProductOptionValue->getProductOptionValueEntity()->getId(),
                ]
            ) . '#tabValues'
        );
    }

    protected function parse(): void
    {
        parent::parse();

        $this->header->addJS(
            '/js/vendors/select2.full.min.js',
            null,
            true,
            true
        );

        $this->header->addJS(
            '/js/vendors/' . Locale::workingLocale() . '.js',
            null,
            true,
            true
        );

        $this->header->addJS('Select2Entity.js');

        $this->header->addCSS(
            '/css/vendors/select2.min.css',
            null,
            true,
            false
        );


        // Needs to be here to disable any ckeditor load after adding a collection field
        $this->header->addJsData('Core', 'preferred_editor', '');
    }

    private function getProductOptionValue(): ProductOptionValue
    {
        /** @var ProductOptionValueRepository $productOptionValueRepository */
        $productOptionValueRepository = $this->get('catalog.repository.product_option_value');

        try {
            return $productOptionValueRepository->findOneById(
                $this->getRequest()->query->getInt('id')
            );
        } catch (ProductOptionValueNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
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

    private function getForm(ProductOptionValue $productOptionValue): Form
    {
        $form = $this->createForm(
            ProductOptionValueType::class,
            new UpdateProductOptionValue($productOptionValue),
            [
                'product_option' => $productOptionValue->getProductOption(),
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateProductOptionValue(Form $form): UpdateProductOptionValue
    {
        /** @var UpdateProductOptionValue $updateProductOptionValue */
        $updateProductOptionValue = $form->getData();

        // The command bus will handle the saving of the specification in the database.
        $this->get('command_bus')->handle($updateProductOptionValue);

        return $updateProductOptionValue;
    }
}
