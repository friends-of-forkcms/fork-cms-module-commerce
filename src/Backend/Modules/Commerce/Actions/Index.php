<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Category\CategoryRepository;
use Backend\Modules\Commerce\Domain\Category\Exception\CategoryNotFound;
use Backend\Modules\Commerce\Domain\Product\FilterType;
use Backend\Modules\Commerce\Domain\Product\DataGrid;
use Common\Exception\RedirectException;

/**
 * This is the index-action (default), it will display the overview of products
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Index extends BackendBaseActionIndex
{
    /**
     * The category where is filtered on
     *
     * @var    array
     */
    private $category;

    /**
     * An sku number to filter on
     *
     * @var string
     */
    private $sku;

    /**
     * The id of the category where is filtered on
     *
     * @var    int
     */
    private $categoryId;

    /**
     * Execute the action
     *
     * @throws RedirectException
     * @throws \Exception
     */
    public function execute(): void
    {
        parent::execute();

        $this->categoryId = $this->getRequest()->query->getInt('category', null);
        $this->sku = $this->getRequest()->query->get('sku');
        $categoryRepository = $this->getCategoryRepository();

        if ($this->categoryId) {
            try {
                $this->category = $categoryRepository->findOneByIdAndLocale($this->categoryId, Locale::workingLocale());
            } catch (CategoryNotFound $e) {
                $this->redirect($this->getBackLink());
                return;
            }
        }

        $this->template->assign(
            'dataGrid',
            DataGrid::getHtml(
                Locale::workingLocale(),
                $this->category,
                $this->sku,
                $this->getRequest()->query->getInt('offset')
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
                'sku' => $this->sku,
            ],
            [
                'categories' => $this->getCategoryRepository()->getTree(Locale::workingLocale())
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

            if ($data['sku']) {
                $parameters['sku'] = $data['sku'];
            }

            // redirect to a filtered page
            $this->redirect(
                $this->getBackLink($parameters)
            );
        }

        // assign the form to our template
        $this->template->assign('form', $filterForm->createView());
    }

    /**
     * @param array $parameters
     *
     * @return string
     * @throws \Exception
     */
    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction(
            'Index',
            null,
            null,
            $parameters
        );
    }

    /**
     * @return CategoryRepository
     */
    private function getCategoryRepository(): CategoryRepository
    {
        return $this->get('commerce.repository.category');
    }
}
