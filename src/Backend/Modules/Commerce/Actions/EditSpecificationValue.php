<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\SpecificationValue\Command\UpdateSpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\Event\UpdatedSpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\Exception\SpecificationValueNotFound;
use Backend\Modules\Commerce\Domain\SpecificationValue\ProductOptionValueRepository;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValue;
use Backend\Modules\Commerce\Domain\SpecificationValue\SpecificationValueType;
use Symfony\Component\Form\Form;

/**
 * This is the edit specification-action, it will display a form to edit a specification.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditSpecificationValue extends BackendBaseActionEdit
{
    public function execute(): void
    {
        parent::execute();

        $specificationValue = $this->getSpecificationValue();

        $form = $this->getForm($specificationValue);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $specificationValue->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteSpecificationValue',
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('specificationValue', $specificationValue);

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateSpecificationValue $updateSpecificationValue */
        $updateSpecificationValue = $this->updateSpecificationValue($form);

        $this->get('event_dispatcher')->dispatch(
            UpdatedSpecificationValue::EVENT_NAME,
            new UpdatedSpecificationValue($updateSpecificationValue->getSpecificationValueEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'id' => $updateSpecificationValue->getSpecificationValueEntity()->getSpecification()->getId(),
                    'report' => 'edited',
                    'var' => $updateSpecificationValue->value,
                    'highlight' => 'row-' . $updateSpecificationValue->getSpecificationValueEntity()->getId(),
                ]
            ) . '#tabValues'
        );
    }

    private function getSpecificationValue(): SpecificationValue
    {
        /** @var ProductOptionValueRepository $specificationValueRepository */
        $specificationValueRepository = $this->get('commerce.repository.specification_value');

        try {
            return $specificationValueRepository->findOneById(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (SpecificationValueNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'EditSpecification',
            null,
            null,
            $parameters
        );
    }

    private function getForm(SpecificationValue $specificationValue): Form
    {
        $form = $this->createForm(
            SpecificationValueType::class,
            new UpdateSpecificationValue($specificationValue)
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateSpecificationValue(Form $form): UpdateSpecificationValue
    {
        /** @var UpdateSpecificationValue $updateSpecificationValue */
        $updateSpecificationValue = $form->getData();

        // The command bus will handle the saving of the specification in the database.
        $this->get('command_bus')->handle($updateSpecificationValue);

        return $updateSpecificationValue;
    }
}
