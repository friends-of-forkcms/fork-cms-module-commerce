<?php

namespace Backend\Modules\Catalog\Domain\ProductOptionValue;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Modules\Catalog\Domain\ProductOption\ProductOption;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(ProductOption $productOption)
    {
        parent::__construct(
            'SELECT c.id, c.title, c.price, c.default_value, c.sequence
					 FROM catalog_product_option_values AS c
					 WHERE c.product_option_id = :product_option
					 GROUP BY c.sequence ASC',
            ['product_option' => $productOption->getId()]
        );

        // Data grid options
        $this->setColumnFunction([DataGridFunctions::class, 'showBool'], ['[default_value]'], 'default_value');
        $this->setColumnsHidden(array('sequence'));
        $this->enableSequenceByDragAndDrop();
        $this->setAttributes(array('data-action' => 'SequenceProductOptionValues'));

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditProductOptionValue')) {
            $editUrl = Model::createUrlForAction('EditProductOptionValue', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('title', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(ProductOption $productOption): string
    {
        return (new self($productOption))->getContent();
    }
}
