<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryRepository;
use Backend\Modules\Commerce\Domain\Category\DataGrid;
use Backend\Modules\Commerce\Domain\Category\Exception\CategoryNotFound;
use Backend\Modules\Commerce\Domain\Category\FilterType;

/**
 * This is the categories-action, it will display the overview of categories.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Categories extends BackendBaseActionIndex
{
    private ?Category $category = null;

    public function execute(): void
    {
        parent::execute();

        // set category id
        $categoryId = (int) $this->getRequest()->query->get('category', null);

        $categoryRepository = $this->getCategoryRepository();

        if ($categoryId) {
            try {
                $this->category = $categoryRepository->findOneByIdAndLocale($categoryId, Locale::workingLocale());
            } catch (CategoryNotFound $e) {
                $this->redirect($this->getBackLink());

                return;
            }
        }

        $this->template->assign('dataGrid', DataGrid::getHtml(Locale::workingLocale(), $this->category));

        $this->loadFilterForm();
        $this->parse();
        $this->display();
    }

    private function loadFilterForm(): void
    {
        $filterForm = $this->createForm(
            FilterType::class,
            [
                'category' => $this->category,
            ],
            [
                'categories' => $this->getCategoryRepository()->getTree(Locale::workingLocale()),
            ]
        );

        $filterForm->handleRequest($this->getRequest());

        // check if the form is submitted and than return with a get
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();

            // build the url parameters when required
            $parameters = [];
            if ($data['category']) {
                $parameters['category'] = $data['category']->getId();
            }

            // redirect to a filtered page
            $this->redirect($this->getBackLink($parameters));
        }

        // assign the form to our template
        $this->template->assign('filterCategory', $filterForm->createView());
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

    private function getCategoryRepository(): CategoryRepository
    {
        return $this->get('commerce.repository.category');
    }
}
