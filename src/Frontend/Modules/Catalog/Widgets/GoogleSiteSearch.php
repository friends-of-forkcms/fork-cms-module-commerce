<?php

namespace Frontend\Modules\Catalog\Widgets;

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Engine\Navigation;

/**
 * This is a widget which informs Google about the search options
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class GoogleSiteSearch extends FrontendBaseWidget
{
    /**
     * Execute the extra
     */
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();

        $this->template->assign('searchUrl', Navigation::getUrlForBlock('Catalog', 'Search'));
    }
}
