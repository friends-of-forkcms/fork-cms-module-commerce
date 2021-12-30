<?php

namespace Backend\Modules\Commerce\Domain\Brand;

use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(Locale $locale)
    {
        parent::__construct(
            'SELECT b.id, b.title, COUNT(p.id) AS num_products, b.sequence
            FROM commerce_brands AS b
            LEFT JOIN commerce_products AS p ON p.brandId = b.id
            WHERE b.language = :language
            GROUP BY b.id',
            ['language' => $locale]
        );

        // sequence
        $this->enableSequenceByDragAndDrop();
        $this->setAttributes(['data-action' => 'SequenceBrands']);

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditBrand')) {
            $editUrl = Model::createUrlForAction('EditBrand', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('title', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale): string
    {
        return (new self($locale))->getContent();
    }
}
