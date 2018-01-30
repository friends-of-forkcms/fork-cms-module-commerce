<?php

namespace Backend\Modules\Catalog\Domain\Order;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    /**
     * DataGrid constructor.
     *
     * @param Locale $locale
     * @param int|null $status
     *
     * @throws \Exception
     * @throws \SpoonDatagridException
     */
    public function __construct(Locale $locale, int $status = null)
    {
        $query = 'SELECT i.id as order_number, CONCAT_WS(" ", a.first_name, a.last_name) as name, i.total,
            (SELECT s.title FROM catalog_order_statuses s INNER JOIN catalog_order_histories h ON h.order_status_id = s.id WHERE h.order_id = i.id ORDER BY h.created_at DESC LIMIT 1) as order_status
            , UNIX_TIMESTAMP(i.date) as `order_date`
            FROM catalog_orders AS i INNER JOIN catalog_order_addresses a ON a.id = i.invoice_address_id';

        parent::__construct($query);

        // assign column functions
        $this->setColumnFunction(array(new DataGridFunctions(), 'getTimeAgo'), '[order_date]', 'order_date', true);
        $this->setColumnFunction(array(self::class, 'getFormatPrice'), '[total]', 'total', true);

        // sorting
        $this->setSortingColumns(array('order_date', 'order_number'), 'order_date');
        $this->setSortParameter('desc');

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditOrder')) {
            $editUrl = Model::createUrlForAction('EditOrder', null, null, ['id' => '[order_number]'], false);
            $this->setColumnURL('order_number', $editUrl);
            $this->setColumnURL('name', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale, int $status = null): string
    {
        return (new self($locale, $status = null))->getContent();
    }

    public function getFormatPrice($price)
    {
        return '&euro; ' .number_format($price, 2, ',', '.');
    }
}
