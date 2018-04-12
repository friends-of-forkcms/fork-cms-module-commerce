<?php

namespace Frontend\Modules\Catalog\Widgets;

use Backend\Modules\Catalog\Domain\Search\SearchDataTransferObject;
use Backend\Modules\Catalog\Domain\Search\SearchType;
use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Engine\Navigation;
use Symfony\Component\Form\Form;

/**
 * This is a widget with the Catalog-search form
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Search extends FrontendBaseWidget
{
    /**
     * Execute the extra
     */
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();

        // Get the search form
        $form = $this->getSearchForm();

        // Assign JS
        $this->addJS('classie.js');
        $this->addJS('uisearch.js');
        $this->addJS('Search.js');

        // Assign the form to our view
        $this->template->assign('form', $form->createView());
    }

    /**
     * Load the search form
     *
     * @return Form
     */
    private function getSearchForm(): Form
    {
        $form = $this->createForm(
            SearchType::class,
            new SearchDataTransferObject($this->getRequest()),
            [
                'action' => Navigation::getUrlForBlock('Catalog', 'Search'),
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
