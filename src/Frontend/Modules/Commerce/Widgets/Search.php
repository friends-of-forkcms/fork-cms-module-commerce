<?php

namespace Frontend\Modules\Commerce\Widgets;

use Backend\Modules\Commerce\Domain\Search\SearchDataTransferObject;
use Backend\Modules\Commerce\Domain\Search\SearchType;
use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Engine\Navigation;
use Symfony\Component\Form\Form;

/**
 * This is a widget with the Commerce-search form.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Search extends FrontendBaseWidget
{
    /**
     * Execute the extra.
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
     * Load the search form.
     */
    private function getSearchForm(): Form
    {
        $form = $this->createForm(
            SearchType::class,
            new SearchDataTransferObject($this->getRequest()),
            [
                'action' => Navigation::getUrlForBlock('Commerce', 'Search'),
            ]
        );

        $form->handleRequest($this->getRequest());

        return $form;
    }
}
