<?php

namespace Frontend\Modules\Commerce\Actions;

use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Commerce\Engine\Model as FrontendCommerceModel;

class Brand extends FrontendBaseBlock
{
    private array $record;
    private array $products;

    /**
     * The pagination array
     * It will hold all needed parameters, some of them need initialization.
     */
    protected array $pagination = ['limit' => 10, 'offset' => 0, 'requested_page' => 1, 'num_items' => null, 'num_pages' => null];

    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
        $this->getData();
        $this->parse();
    }

    private function getData(): void
    {
        $parameters = $this->url->getParameters();
        $url = end($parameters);

        if ($url === null) {
            $this->redirect(FrontendNavigation::getURL(404));
        }

        // get by URL
        $this->record = FrontendCommerceModel::getBrandFromUrl($url);

        if (empty($this->record)) {
            $this->redirect(FrontendNavigation::getURL(404));
        }

        // get products
        $this->products = FrontendCommerceModel::getAllByBrand($this->record['id']);

        // requested page
        $requestedPage = $this->url->getParameter('page', 'int', 1);

        // set URL and limit
        $this->pagination['url'] = FrontendNavigation::getURLForBlock($this->getModule(), 'category') . '/' . $this->record['url'];
        $this->pagination['limit'] = $this->get('fork.settings')->get('commerce', 'overview_num_items', 10);

        // populate count fields in pagination
        $this->pagination['num_items'] = FrontendCommerceModel::getCategoryCount($this->record['id']);
        $this->pagination['num_pages'] = (int) ceil($this->pagination['num_items'] / $this->pagination['limit']);

        // num pages is always equal to at least 1
        if ($this->pagination['num_pages'] === 0) {
            $this->pagination['num_pages'] = 1;
        }

        // redirect if the request page doesn't exist
        if ($requestedPage > $this->pagination['num_pages'] || $requestedPage < 1) {
            $this->redirect(FrontendNavigation::getURL(404));
        }

        // populate calculated fields in pagination
        $this->pagination['requested_page'] = $requestedPage;
        $this->pagination['offset'] = ($this->pagination['requested_page'] * $this->pagination['limit']) - $this->pagination['limit'];
    }

    protected function parse(): void
    {
        // add css
        $this->header->addCSS('/src/Frontend/Modules/' . $this->getModule() . '/Layout/Css/Commerce.css');

        // add breadcrumb
        $this->breadcrumb->addElement($this->record['title'], $this->record['full_url']);

        // hide action title
        $this->template->assign('hideContentTitle', true);

        // show the title
        $this->template->assign('title', $this->record['title']);

        // set meta
        $this->header->setPageTitle($this->record['meta_title'], ($this->record['meta_title_overwrite'] === 'Y'));
        $this->header->addMetaDescription($this->record['meta_description'], ($this->record['meta_description_overwrite'] === 'Y'));
        $this->header->addMetaKeywords($this->record['meta_keywords'], ($this->record['meta_keywords_overwrite'] === 'Y'));

        // advanced SEO-attributes
        if (isset($this->record['meta_data']['seo_index'])) {
            $this->header->addMetaData(['name' => 'robots', 'content' => $this->record['meta_data']['seo_index']]);
        }
        if (isset($this->record['meta_data']['seo_follow'])) {
            $this->header->addMetaData(['name' => 'robots', 'content' => $this->record['meta_data']['seo_follow']]);
        }

        // assign items
        $this->template->assign('products', $this->products);
        $this->template->assign('record', $this->record);

        // parse the pagination
        $this->parsePagination();
    }
}
