<?php

namespace Backend\Modules\Commerce\Domain\ProductOptionValue;

use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Modules\Commerce\Domain\ProductOption\ProductOption;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(ProductOption $productOption)
    {
        parent::__construct(
            '
            SELECT
                c.id,
                IF(po.type = :typeBetween, CONCAT_WS(:titleSeparator, CONCAT(IFNULL(po.prefix, ""), c.start, IFNULL(po.suffix, "")), CONCAT(IFNULL(po.prefix, ""), c.end, IFNULL(po.suffix, ""))), c.title) as title,
                c.impactType,
                c.percentage,
                c.price,
                c.defaultValue,
                c.sequence,
                (SELECT(count(*)) FROM commerce_product_options i WHERE i.productOptionValueId = c.id) as sub_options
            FROM commerce_product_option_values AS c
            INNER JOIN commerce_product_options AS po ON po.id = c.productOptionId
            WHERE c.productOptionId = :product_option',
            [
                'titleSeparator' => ' - ',
                'typeBetween' => ProductOption::DISPLAY_TYPE_BETWEEN,
                'product_option' => $productOption->getId(),
            ]
        );

        // Data grid options
        $this->setColumnFunction([DataGridFunctions::class, 'showBool'], ['[defaultValue]'], 'defaultValue');
        $this->setColumnFunction([self::class, 'getImpactTypeText'], ['[impactType]'], 'impactType');
        $this->setColumnsHidden(['sequence']);
        $this->enableSequenceByDragAndDrop();
        $this->setAttributes(['data-action' => 'SequenceProductOptionValues']);

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

    public static function getImpactTypeText($value)
    {
        switch ($value) {
            case ProductOptionValue::IMPACT_TYPE_ADD:
            default:
                $returnValue = ucfirst(Language::lbl('Add'));

                break;
            case ProductOptionValue::IMPACT_TYPE_SUBTRACT:
                $returnValue = ucfirst(Language::lbl('SubTract'));

                break;
        }

        return $returnValue;
    }
}
