<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Brand\BrandRepository;
use Backend\Modules\Commerce\Domain\Brand\BrandType;
use Backend\Modules\Commerce\Domain\Brand\Command\UpdateBrand;
use Backend\Modules\Commerce\Domain\Brand\Event\Updated;
use Backend\Modules\Commerce\Domain\Brand\Exception\BrandNotFound;
use Symfony\Component\Form\Form;

/**
 * This is the edit brand action, it will display a form to edit an existing brand.
 */
class EditBrand extends BackendBaseActionEdit
{
    public function execute(): void
    {
        parent::execute();

        $brand = $this->getBrand();

        $form = $this->getForm($brand);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $brand->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteBrand',
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('brand', $brand);

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateBrand $updateBrand */
        $updateBrand = $this->updateBrand($form);

        $this->get('event_dispatcher')->dispatch(
            Updated::EVENT_NAME,
            new Updated($updateBrand->getBrandEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'edited',
                    'var' => $updateBrand->title,
                    'highlight' => 'row-' . $updateBrand->getBrandEntity()->getId(),
                ]
            )
        );
    }

    private function getBrand(): Brand
    {
        /** @var BrandRepository $brandRepository */
        $brandRepository = $this->get('commerce.repository.brand');

        try {
            return $brandRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (BrandNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Brands',
            null,
            null,
            $parameters
        );
    }

    private function getForm(Brand $brand): Form
    {
        $form = $this->createForm(
            BrandType::class,
            new UpdateBrand($brand)
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateBrand(Form $form): UpdateBrand
    {
        /** @var UpdateBrand $updateBrand */
        $updateBrand = $form->getData();

        // The command bus will handle the saving of the brand in the database.
        $this->get('command_bus')->handle($updateBrand);

        return $updateBrand;
    }
}
