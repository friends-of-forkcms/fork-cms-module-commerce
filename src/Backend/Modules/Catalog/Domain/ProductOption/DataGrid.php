<?php

namespace Backend\Modules\Catalog\Domain\ProductOption;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Modules\Catalog\Domain\Product\Product;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(Product $product)
    {
            parent::__construct(
                'SELECT i.id, i.title, i.required, i.sequence
		 FROM catalog_product_options AS i
		 WHERE i.product_id = :product
		 ORDER BY i.sequence ASC',
                ['product' => $product->getId()]
            );

        // our JS needs to know an id, so we can highlight it
        $this->setRowAttributes(array('id' => 'row-[id]'));
        $this->setColumnFunction([DataGridFunctions::class, 'showBool'], ['[required]'], 'required');
        $this->setColumnsHidden(array('sequence'));
        $this->enableSequenceByDragAndDrop();
        $this->setAttributes(array('data-action' => 'SequenceProductOptions'));

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditProductOption')) {
            $editUrl = Model::createUrlForAction('EditProductOption', null, null,
                ['id' => '[id]', 'product' => $product->getId()], false);
            $this->setColumnURL('title', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Product $product): string
    {
        return (new self($product))->getContent();
    }
}
