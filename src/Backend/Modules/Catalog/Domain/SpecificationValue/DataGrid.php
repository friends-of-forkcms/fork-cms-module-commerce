<?php

namespace Backend\Modules\Catalog\Domain\SpecificationValue;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\Specification\Specification;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(Specification $specification)
    {
        parent::__construct(
            'SELECT c.id, c.value, (SELECT COUNT(*) FROM `catalog_products_specification_values` WHERE `specification_value_id` = c.id) as products
					 FROM catalog_specification_values AS c
					 WHERE c.specification_id = :specification',
            ['specification' => $specification->getId()]
        );


        // sequence
        $this->enableSequenceByDragAndDrop();
        $this->setAttributes(array('data-action' => 'SequenceSpecificationValues'));

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditSpecificationValue')) {
            $editUrl = Model::createUrlForAction('EditSpecificationValue', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('value', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Specification $specification): string
    {
        return (new self($specification))->getContent();
    }
}
