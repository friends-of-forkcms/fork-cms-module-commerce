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
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $this->product = $this->getProduct();

        $form = $this->getForm();
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

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
        $parameters = array_merge([
            'id' => $this->product->getId(),
        ], $parameters);

        return BackendModel::createUrlForAction(
            'Edit',
            null,
            null,
            $parameters
        ) . '#tabOptions';
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            ProductOptionType::class,
            new CreateProductOption()
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function getProduct(): Product
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
    }
}
