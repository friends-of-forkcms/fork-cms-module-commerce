<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\CartRule\DataGrid;

class CartRules extends BackendBaseActionIndex
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

	    $this->template->assign('dataGrid', DataGrid::getHtml(Locale::workingLocale()));

	    $this->parse();
	    $this->display();
    }
}
