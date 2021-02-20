<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Product\Exception\ProductNotFound;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;
use Backend\Modules\Catalog\Domain\ProductOption\Command\CreateProductOption;
use Backend\Modules\Catalog\Domain\ProductOption\Event\CreatedProductOption;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOptionType;
use Backend\Modules\Catalog\Domain\ProductOptionValue\Exception\ProductOptionValueNotFound;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValue;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValueRepository;
use Symfony\Component\Form\Form;

/**
 * This is the add product option-action, it will display a form to create a new product option
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AddProductOption extends BackendBaseActionAdd
{
    /**
     * @var Product
     */
    private $product;
    /**
     * @var ProductOptionValue
     */
    private $productOptionValue;

    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $this->loadData();

        $form = $this->getForm();
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('backLink', $this->getBackLink());

            $this->parse();
            $this->display();

            return;
        }

        $createProductOption = $this->createProductOption($form);

        $this->get('event_dispatcher')->dispatch(
            CreatedProductOption::EVENT_NAME,
            new CreatedProductOption($createProductOption->getProductOptionEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'added',
                    'var' => $createProductOption->title,
                ]
            )
        );
        return;
    }

    private function createProductOption(Form $form): CreateProductOption
    {
        $createProductOption = $form->getData();
        $createProductOption->product = $this->product;

        // The command bus will handle the saving of the brand in the database.
        $this->get('command_bus')->handle($createProductOption);

        return $createProductOption;
    }

    private function getBackLink(array $parameters = []): string
    {
        if ($this->productOptionValue) {
            $parameters = array_merge($parameters, [
                'id' => $this->productOptionValue->getId(),
            ]);

            return BackendModel::createUrlForAction(
                    'EditProductOptionValue',
                    null,
                    null,
                    $parameters
                ) . '#tabSubOptions';
        }

        $parameters = array_merge($parameters, [
            'id' => $this->product->getId(),
        ]);

        return BackendModel::createUrlForAction(
            'Edit',
            null,
            null,
            $parameters
        ) . '#tabOptions';
    }

    private function getForm(): Form
    {
        $createProductOption = new CreateProductOption();
        $createProductOption->parent_product_option_value = $this->productOptionValue;

        $form = $this->createForm(
            ProductOptionType::class,
            $createProductOption,
            [
                'product' => $this->product,
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function loadData()
    {
        if ($this->getRequest()->query->has('product')) {
            $this->product = $this->getProduct();
        }

        if ($this->getRequest()->query->has('product_option_value')) {
            $this->productOptionValue = $this->getProductOptionValue();
            $this->product = $this->productOptionValue->getProductOption()->getProduct();
        }
    }

    private function getProduct(): ?Product
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->get('catalog.repository.product');

        try {
            return $productRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('product'),
                Locale::workingLocale()
            );
        } catch (ProductNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }

        return null;
    }

    private function getProductOptionValue(): ?ProductOptionValue
    {
        /** @var ProductOptionValueRepository $productOptionValueRepository */
        $productOptionValueRepository = $this->get('catalog.repository.product_option_value');

        try {
            return $productOptionValueRepository->findOneById(
                $this->getRequest()->query->getInt('product_option_value')
            );
        } catch (ProductOptionValueNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }

        return null;
    }
}
