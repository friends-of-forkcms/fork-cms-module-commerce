<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\DataGridFunctions;

class DataGridOrderHistory extends DataGridDatabase
{
    /**
     * DataGrid constructor.
     *
     * @param Order $order
     *
     * @throws \Exception
     */
    public function __construct(Order $order)
    {
        $query = 'SELECT UNIX_TIMESTAMP(i.created_at) as `date`, s.title
                    FROM commerce_order_histories i INNER JOIN commerce_order_statuses s ON s.id = i.order_status_id
                    WHERE i.order_id = ? ORDER BY i.created_at DESC';

        parent::__construct($query, [$order->getId()]);

        // assign column functions
        $this->setColumnFunction(array(new DataGridFunctions(), 'getDate'), '[date]', 'date', true);
    }

    public static function getHtml(Order $order): string
    {
        return (new self($order))->getContent();
    }
}
