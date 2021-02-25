<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryRepository;
use Backend\Modules\Commerce\Domain\Category\CategoryType;
use Backend\Modules\Commerce\Domain\Category\Command\UpdateCategory;
use Backend\Modules\Commerce\Domain\Category\Event\Updated;
use Backend\Modules\Commerce\Domain\Category\Exception\CategoryNotFound;
use Symfony\Component\Filesystem\Filesystem;
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

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('form', $form->createView());
            $this->template->assign('category', $category);

            $this->parse();
            $this->display();

            return;
        }

        /** @var UpdateCategory $updateCategory */
        $updateCategory = $this->updateCategory($form);

        $this->get('event_dispatcher')->dispatch(
            Updated::EVENT_NAME,
            new Updated($updateCategory->getCategoryEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'edited',
                    'var' => $updateCategory->title,
                    'highlight' => 'row-' . $updateCategory->getCategoryEntity()->getId(),
                ]
            )
        );
    }

    public function parse(): void
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

    private function getCategory(): Category
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->get('commerce.repository.category');

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
            new UpdateCategory($category),
            [
                'current_category' => $category,
                'google_taxonomies' => $this->getGoogleTaxonomies(),
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function updateCategory(Form $form): UpdateCategory
    {
        /** @var UpdateCategory $updateCategory */
        $updateCategory = $form->getData();

        // The command bus will handle the saving of the category in the database.
        $this->get('command_bus')->handle($updateCategory);

        return $updateCategory;
    }

    private function getGoogleTaxonomies()
    {
        $filesystem = new Filesystem();
        $kernelRootDir = $this->getKernel()->getRootDir();
        $googleTaxonomyFile = $kernelRootDir . '/../src/Backend/Modules/Commerce/GoogleTaxonomy/'
            . Locale::workingLocale()->getLocale()
            . '/taxonomies.txt';

        $categories = [];
        if ($filesystem->exists($googleTaxonomyFile)) {
            $lines = explode("\n", file_get_contents($googleTaxonomyFile));
            foreach ($lines as $line) {
                if (!preg_match('/^([0-9]+) - (.*)/', $line, $matches)) {
                    continue;
                }

                $categories[$line] = $matches[1];
            }
        }

        return $categories;
    }
}
