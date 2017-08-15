<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Language\Locale;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\DataGrid as BackendDataGridDB;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Modules\Catalog\Domain\Specification\DataGrid;
use Backend\Modules\Catalog\Domain\Specification\Specification;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;
 
/**
 * This is the specifications action, it will display the overview of specifications
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Willem van Dam <w.vandam@jvdict.nl>
 */
class Specifications extends BackendBaseActionIndex
{
    /**
     * DataGrid
     *
     * @var	BackendDataGridDB
     */
    protected $dataGrid;

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
