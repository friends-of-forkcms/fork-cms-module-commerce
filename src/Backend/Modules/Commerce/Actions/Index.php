<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Brand\Brand;
use Backend\Modules\Commerce\Domain\Brand\BrandRepository;
use Backend\Modules\Commerce\Domain\Brand\Exception\BrandNotFound;
use Backend\Modules\Commerce\Domain\Category\Category;
use Backend\Modules\Commerce\Domain\Category\CategoryRepository;
use Backend\Modules\Commerce\Domain\Category\Exception\CategoryNotFound;
use Backend\Modules\Commerce\Domain\Product\DataGrid;
use Backend\Modules\Commerce\Domain\Product\FilterType;
use Common\Exception\RedirectException;

/**
 * This is the index-action (default), it will display the overview of products.
 */
class Index extends BackendBaseActionIndex
{
    private ?Category $category = null;
    private ?Brand $brand = null;
    private ?string $searchQuery = null;

    public function execute(): void
    {
        parent::execute();

        // Filters
        $categoryId = $this->getRequest()->query->getInt('category', null);
        $brandId = $this->getRequest()->query->getInt('brand', null);
        $this->searchQuery = $this->getRequest()->query->get('q');

        if ($categoryId) {
            try {
                $categoryRepository = $this->getCategoryRepository();
                $this->category = $categoryRepository->findOneByIdAndLocale($categoryId, Locale::workingLocale());
            } catch (CategoryNotFound $e) {
                $this->redirect($this->getBackLink());
                return;
            }
        }

        if ($brandId) {
            try {
                $brandRepository = $this->getBrandRepository();
                $this->brand = $brandRepository->findOneByIdAndLocale($brandId, Locale::workingLocale());
            } catch (BrandNotFound $e) {
                $this->redirect($this->getBackLink());
                return;
            }
        }

        $this->template->assign(
            'dataGrid',
            DataGrid::getHtml(
                Locale::workingLocale(),
                $this->category,
                $this->brand,
                $this->searchQuery,
                $this->getRequest()->query->getInt('offset'),
            )
        );

        $this->loadFilterForm();
        $this->parse();
        $this->display();
    }

    /**
     * @throws RedirectException
     * @throws \Exception
     */
    private function loadFilterForm(): void
    {
        $filterForm = $this->createForm(
            FilterType::class,
            [
                'category' => $this->category,
                'brand' => $this->brand,
                'search' => $this->searchQuery,
            ],
            [
                'categories' => $this->getCategoryRepository()->getTree(Locale::workingLocale()),
                'brands' => $this->getBrandRepository()->findAll(),
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

            if ($data['brand']) {
                $parameters['brand'] = $data['brand']->getId();
            }

            if ($data['search']) {
                $parameters['q'] = $data['search'];
            }

            // redirect to a filtered page
            $this->redirect($this->getBackLink($parameters));
        }

        // assign the form to our template
        $this->template->assign('form', $filterForm->createView());
    }

    /**
     * @throws \Exception
     */
    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction('Index', null, null, $parameters);
    }

    private function getCategoryRepository(): CategoryRepository
    {
        return $this->get('commerce.repository.category');
    }

    private function getBrandRepository(): BrandRepository
    {
        return $this->get('commerce.repository.brand');
    }
}
