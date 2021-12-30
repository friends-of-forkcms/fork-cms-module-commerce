<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Brand\DataGrid;

/**
 * This is the categories-action, it will display the overview of categories.
 */
class Brands extends BackendBaseActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $this->template->assign('dataGrid', DataGrid::getHtml(Locale::workingLocale()));
        $this->parse();
        $this->display();
    }
}
