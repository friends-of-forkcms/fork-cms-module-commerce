<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Catalog\Domain\Product\Command\UpdateProduct;
use Backend\Modules\Catalog\Domain\Product\Event\Updated;
use Backend\Modules\Catalog\Domain\Product\Exception\ProductNotFound;
use Backend\Modules\Catalog\Domain\Product\Product;
use Backend\Modules\Catalog\Domain\Product\ProductRepository;
use Backend\Modules\Catalog\Domain\Product\ProductType;
use Symfony\Component\Form\Form;

/**
 * This is the edit-action, it will display a form with the product data to edit
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Edit extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $product = $this->getProduct();

        $form = $this->getForm($product);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $product->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'Delete'
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if ( ! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('product', $product);

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateProduct $updateProduct */
        $updateProduct = $this->updateProduct($form);

        $this->get('event_dispatcher')->dispatch(
            Updated::EVENT_NAME,
            new Updated($updateProduct->getProductEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report'    => 'edited',
                    'var'       => $updateProduct->title,
                    'highlight' => 'row-' . $updateProduct->getProductEntity()->getId(),
                ]
            )
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
    }

    private function getProduct(): Product
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->get('catalog.repository.product');

        try {
            return $productRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (ProductNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Index',
            null,
            null,
            $parameters
        );
    }

    private function getForm(Product $product): Form
    {
        $form = $this->createForm(
            ProductType::class,
            new UpdateProduct($product),
            [
                'categories' => $this->get('catalog.repository.category')->getTree(Locale::workingLocale()),
                'product'    => $product
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateProduct(Form $form): UpdateProduct
    {
        /** @var UpdateProduct $updateProduct */
        $updateProduct = $form->getData();

        // The command bus will handle the saving of the product in the database.
        $this->get('command_bus')->handle($updateProduct);

        return $updateProduct;
    }
}
