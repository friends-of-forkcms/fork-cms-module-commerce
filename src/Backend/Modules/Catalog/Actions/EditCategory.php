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
use Backend\Modules\Catalog\Domain\Category\Category;
use Backend\Modules\Catalog\Domain\Category\CategoryRepository;
use Backend\Modules\Catalog\Domain\Category\CategoryType;
use Backend\Modules\Catalog\Domain\Category\Command\Update;
use Backend\Modules\Catalog\Domain\Category\Event\Updated;
use Backend\Modules\Catalog\Domain\Category\Exception\CategoryNotFound;
use Symfony\Component\Form\Form;

/**
 * This is the edit category action, it will display a form to edit an existing category.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditCategory extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        $category = $this->getCategory();

        $form = $this->getForm($category);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $category->getId()],
            [
                'module' => $this->getModule(),
                'action' => 'DeleteCategory'
            ]
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        if ( ! $form->isSubmitted() || ! $form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('category', $category);

            $this->parse();
            $this->display();

            return;
        }

        /** @var Update $updateCategory */
        $updateCategory = $this->updateCategory($form);

        $this->get('event_dispatcher')->dispatch(
            Updated::EVENT_NAME,
            new Updated($updateCategory->getCategoryEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report'    => 'edited',
                    'var'       => $updateCategory->title,
                    'highlight' => 'row-' . $updateCategory->getCategoryEntity()->getId(),
                ]
            )
        );
    }

    private function getCategory(): Category
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->get('catalog.repository.category');

        try {
            return $categoryRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (CategoryNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Categories',
            null,
            null,
            $parameters
        );
    }

    private function getForm(Category $category): Form
    {
        $form = $this->createForm(
            CategoryType::class,
            new Update($category)
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateCategory(Form $form): Update
    {
        /** @var Update $updateCategory */
        $updateCategory = $form->getData();

        // The command bus will handle the saving of the category in the database.
        $this->get('command_bus')->handle($updateCategory);

        return $updateCategory;
    }
}
