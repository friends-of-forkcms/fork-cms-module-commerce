<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Catalog\Domain\Specification\Exception\SpecificationNotFound;
use Backend\Modules\Catalog\Domain\Specification\Specification;
use Backend\Modules\Catalog\Domain\Specification\SpecificationType;
use Backend\Modules\Catalog\Domain\Specification\SpecificationRepository;
use Backend\Modules\Catalog\Domain\Specification\Command\UpdateSpecification;
use Backend\Modules\Catalog\Domain\Specification\Event\Updated;
use Backend\Modules\Catalog\Domain\SpecificationValue\DataGrid;
use Symfony\Component\Form\Form;

/**
 * This is the edit specification-action, it will display a form to edit a specification
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Willem van Dam <w.vandam@jvdict.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditSpecification extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $specification = $this->getSpecification();

        $form = $this->getForm($specification);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $specification->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteSpecification'
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if ( ! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('specification', $specification);
            $this->template->assign('dataGridValues', DataGrid::getHtml($specification));

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateSpecification $updateSpecification */
        $updateSpecification = $this->updateSpecification($form);

        $this->get('event_dispatcher')->dispatch(
            Updated::EVENT_NAME,
            new Updated($updateSpecification->getSpecificationEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report'    => 'edited',
                    'var'       => $updateSpecification->title,
                    'highlight' => 'row-' . $updateSpecification->getSpecificationEntity()->getId(),
                ]
            )
        );
    }

    private function getSpecification(): Specification
    {
        /** @var SpecificationRepository $specificationRepository */
        $specificationRepository = $this->get('catalog.repository.specification');

        try {
            return $specificationRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (SpecificationNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Specification',
            null,
            null,
            $parameters
        );
    }

    private function getForm(Specification $specification): Form
    {
        $form = $this->createForm(
            SpecificationType::class,
            new UpdateSpecification($specification)
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateSpecification(Form $form): UpdateSpecification
    {
        /** @var UpdateSpecification $updateSpecification */
        $updateSpecification = $form->getData();

        // The command bus will handle the saving of the specification in the database.
        $this->get('command_bus')->handle($updateSpecification);

        return $updateSpecification;
    }
}
