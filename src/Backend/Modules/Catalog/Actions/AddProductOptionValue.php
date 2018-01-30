<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Catalog\Domain\ProductOption\Exception\ProductOptionNotFound;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOptionRepository;
use Backend\Modules\Catalog\Domain\ProductOptionValue\Command\CreateProductOptionValue;
use Backend\Modules\Catalog\Domain\ProductOptionValue\ProductOptionValueType;
use Backend\Modules\Catalog\Domain\ProductOptionValue\Event\CreatedProductOptionValue;
use Symfony\Component\Form\Form;

/**
 * This is the add product option value-action, it will display a form to create a new product option value
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AddProductOptionValue extends BackendBaseActionAdd
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

        $form = $this->getForm();
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());

            $this->parse();
            $this->display();

            return;
        }

        $createProductOptionValue = $this->createProductOptionValue($form);

        $this->get('event_dispatcher')->dispatch(
            CreatedProductOptionValue::EVENT_NAME,
            new CreatedProductOptionValue($createProductOptionValue->getProductOptionValueEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'id' => $this->productOption->getId(),
                    'report' => 'added',
                    'var' => $createProductOptionValue->title,
                ]
            )
        );
        return;
    }

    private function getProductOption(): ProductOption
    {
        /** @var ProductOptionRepository $productOptionRepository */
        $productOptionRepository = $this->get('catalog.repository.product_option');

        try {
            return $productOptionRepository->findOneById(
                $this->getRequest()->query->getInt('product_option')
            );
        } catch (ProductOptionNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function createProductOptionValue(Form $form): CreateProductOptionValue
    {
        $createProductOptionValue = $form->getData();
        $createProductOptionValue->productOption = $this->productOption;

        // The command bus will handle the saving of the brand in the database.
        $this->get('command_bus')->handle($createProductOptionValue);

        return $createProductOptionValue;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'EditProductOption',
            null,
            null,
            $parameters
        ) . '#tabValues';
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            ProductOptionValueType::class,
            new CreateProductOptionValue()
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
