<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Brand\DataGrid;

/**
 * This is the categories-action, it will display the overview of categories
 *
 * @author Wado Cosman <waldo@comsa.be>
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class Brands extends BackendBaseActionIndex
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
