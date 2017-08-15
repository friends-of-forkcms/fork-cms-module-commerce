<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Language;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Product\Command\Create;
use Backend\Modules\Catalog\Domain\Product\Event\Created;
use Backend\Modules\Catalog\Domain\Product\ProductType;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;
use League\Flysystem\Adapter\Local;
use Symfony\Component\Form\Form;

/**
 * This is the add-action, it will display a form to create a new product
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Add extends BackendBaseActionAdd
{
    /**
     * Execute the actions
     */
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();
        if ( ! $form->isSubmitted() || ! $form->isValid()) {
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
                    'report'    => 'added',
                    'var'       => $createProduct->title,
                    'highlight' => 'row-' . $createProduct->getProductEntity()->getId(),
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

    private function createProduct(Form $form): Create
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
            new Create(),
            [
                'categories' => $this->get('catalog.repository.category')->getTree(Locale::workingLocale())
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
