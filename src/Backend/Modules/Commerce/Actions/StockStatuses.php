<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\DataGrid as BackendDataGridDB;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\StockStatus\DataGrid;

/**
 * This is the vats action, it will display the overview of vats.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class StockStatuses extends BackendBaseActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $this->template->assign('dataGrid', DataGrid::getHtml(Locale::workingLocale()));

        $this->parse();
        $this->display();
    }
}
