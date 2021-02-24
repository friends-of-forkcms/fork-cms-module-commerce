<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\DataGrid as BackendDataGridDB;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Country\DataGrid;

/**
 * This is the countries action, it will display the overview of countries.
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Countries extends BackendBaseActionIndex
{
    protected BackendDataGridDB $dataGrid;

    public function execute(): void
    {
        parent::execute();

        $this->template->assign('dataGrid', DataGrid::getHtml(Locale::workingLocale()));

        $this->parse();
        $this->display();
    }
}
