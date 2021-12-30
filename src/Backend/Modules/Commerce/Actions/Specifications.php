<?php

namespace Backend\Modules\Commerce\Actions;

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Specification\DataGrid;

/**
 * This is the specifications action, it will display the overview of specifications.
 */
class Specifications extends BackendBaseActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $this->template->assign('dataGrid', DataGrid::getHtml(Locale::workingLocale()));

        $this->parse();
        $this->display();
    }
}
