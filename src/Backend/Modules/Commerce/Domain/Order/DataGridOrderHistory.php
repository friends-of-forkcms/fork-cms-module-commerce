<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\DataGridFunctions;

class DataGridOrderHistory extends DataGridDatabase
{
    /**
     * DataGrid constructor.
     *
     * @throws \Exception
     */
    public function __construct(Order $order)
    {
        $query = '
            SELECT UNIX_TIMESTAMP(i.createdAt) as `date`, s.title
            FROM commerce_order_histories i
            INNER JOIN commerce_order_statuses s ON s.id = i.orderStatusId
            WHERE i.orderId = ? ORDER BY i.createdAt DESC';

        parent::__construct($query, [$order->getId()]);

        // assign column functions
        $this->setColumnFunction([new DataGridFunctions(), 'getDate'], '[date]', 'date', true);
    }

    public static function getHtml(Order $order): string
    {
        return (new self($order))->getContent();
    }
}
