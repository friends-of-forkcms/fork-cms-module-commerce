<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Product\Command\CreateProduct;
use Backend\Modules\Commerce\Domain\Product\Event\Created;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductType;
use Symfony\Component\Form\Form;

/**
 * This is the add-action, it will display a form to create a new product.
 */
class Add extends BackendBaseActionAdd
{
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            $this->parse();
            $this->display();

            return;
        }

        $createProduct = $this->createProduct($form);

        $this->get('event_dispatcher')->dispatch(
            Created::EVENT_NAME,
            new Created($createProduct->getProductEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'added',
                    'var' => $createProduct->title,
                    'highlight' => 'row-' . $createProduct->getProductEntity()->getId(),
                ]
            )
        );
    }

    protected function parse(): void
    {
        parent::parse();

        $this->header->addJS('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js', null, false, true);
        $this->header->addJS(sprintf('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/%s.min.js', Locale::workingLocale()), null, true, true);
        $this->header->addJS('Select2Entity.js');
        $this->header->addJS('ProductDimensions.js');

        $this->header->addCSS('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', null, true, false);
        $this->header->addCSS('ProductDimensions.css');

        $this->header->addJsData($this->getModule(), 'types', [
            'default' => Product::TYPE_DEFAULT,
            'dimensions' => Product::TYPE_DIMENSIONS,
        ]);
    }

    private function createProduct(Form $form): CreateProduct
    {
        $createProduct = $form->getData();

        // The command bus will handle the saving of the category in the database.
        $this->get('command_bus')->handle($createProduct);

        return $createProduct;
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

    private function getForm(): Form
    {
        $form = $this->createForm(
            ProductType::class,
            new CreateProduct(null, Locale::workingLocale()),
            [
                'categories' => $this->get('commerce.repository.category')->getTree(Locale::workingLocale()),
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
