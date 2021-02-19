<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Category\CategoryType;
use Backend\Modules\Catalog\Domain\Category\Command\CreateCategory;
use Backend\Modules\Catalog\Domain\Category\Event\Created;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Form;

/**
 * This is the add category-action, it will display a form to create a new category
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class AddCategory extends BackendBaseActionAdd
{
    /**
     * Execute the action
     */
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

        $createCategory = $this->createCategory($form);

        $this->get('event_dispatcher')->dispatch(
            Created::EVENT_NAME,
            new Created($createCategory->getCategoryEntity())
        );

        $this->redirect(
            $this->getBackLink(
                [
                    'report' => 'added',
                    'var' => $createCategory->title,
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

    private function createCategory(Form $form): CreateCategory
    {
        $createCategory = $form->getData();

        // The command bus will handle the saving of the category in the database.
        $this->get('command_bus')->handle($createCategory);

        return $createCategory;
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

    private function getForm(): Form
    {
        $form = $this->createForm(
            CategoryType::class,
            new CreateCategory(),
            [
                'categories' => $this->get('catalog.repository.category')->getTree(Locale::workingLocale()),
                'google_taxonomies' => $this->getGoogleTaxonomies(),
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function getGoogleTaxonomies()
    {
        $filesystem = new Filesystem();
        $kernelRootDir = $this->getKernel()->getRootDir();
        $googleTaxonomyFile = $kernelRootDir . '/../src/Backend/Modules/Catalog/GoogleTaxonomy/'
            . Locale::workingLocale()->getLocale()
            . '/taxonomies.txt';

        $categories = [];
        $query = strtolower($this->getRequest()->request->get('q'));
        if ($filesystem->exists($googleTaxonomyFile)) {
            $lines = explode("\n", file_get_contents($googleTaxonomyFile));
            foreach ($lines as $line) {
                if (!preg_match('/^([0-9]+) - (.*)/', $line, $matches)) {
                    continue;
                }

                if (strpos(strtolower($line), strtolower($query)) !== false) {
                    $categories[] = [
                        'id' => $matches[1],
                        'value' => $line,
                    ];
                }
            }
        }

        return $categories;
    }
}
