<?php

namespace Backend\Modules\Commerce\Domain\ProductOption;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Modules\Commerce\Domain\Product\Product;
use Backend\Modules\Commerce\Domain\Product\ProductType;
use Backend\Modules\Commerce\Domain\ProductOptionValue\ProductOptionValue;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(Product $product, ProductOptionValue $productOptionValue = null)
    {
        $query = 'SELECT i.id, i.title, i.type, i.required, i.custom_value_allowed, i.sequence
		        FROM commerce_product_options AS i
		        WHERE i.product_id = :product';
        $parameters = [
            'product' => $product->getId(),
        ];

        if ($productOptionValue) {
            $query .= ' AND i.product_option_value_id = :product_option_value_id ';
            $parameters['product_option_value_id'] = $productOptionValue->getId();
        } else {
            $query .= ' AND i.product_option_value_id IS NULL ';
        }

        parent::__construct($query, $parameters);

        // our JS needs to know an id, so we can highlight it
        $this->setRowAttributes(array('id' => 'row-[id]'));
        $this->setColumnFunction([DataGridFunctions::class, 'showBool'], ['[required]'], 'required');
        $this->setColumnFunction([DataGridFunctions::class, 'showBool'], ['[custom_value_allowed]'], 'custom_value_allowed');
        $this->setColumnFunction([self::class, 'getType'], ['[type]'], 'type');
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

    public static function getHtmlProductOptionValue(ProductOptionValue $productOptionValue): string
    {
        return (new self($productOptionValue->getProductOption()->getProduct(), $productOptionValue))
            ->getContent();
    }

    public static function getType($type)
    {
        $types = [];

        foreach (ProductOptionType::$typeChoices as $name => $key) {
            $nameParts = explode('.', $name);
            $types[$key] = $nameParts[1];
        }

        if (!array_key_exists($type, $types)) {
            return ucfirst(Language::lbl('Unknown'));
        }

        return ucfirst(Language::lbl($types[$type]));
    }
}
