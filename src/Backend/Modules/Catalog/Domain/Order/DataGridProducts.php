<?php

namespace Backend\Modules\Catalog\Domain\Order;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Language\Locale;

class DataGridProducts extends DataGridDatabase
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
        $query = 'SELECT i.title as product, i.sku as article_number, i.amount, i.price, i.total
                    FROM catalog_order_products i WHERE i.order_id = ? ORDER BY i.id ASC';

        parent::__construct($query, [$order->getId()]);

        // assign column functions
        $this->setColumnFunction(array(self::class, 'getFormatPrice'), '[total]', 'total', true);
        $this->setColumnFunction(array(self::class, 'getFormatPrice'), '[price]', 'price', true);
    }

    public static function getHtml(Order $order): string
    {
        return (new self($order))->getContent();
    }

    public function getFormatPrice($price)
    {
        return '&euro; ' .number_format($price, 2, ',', '.');
    }
}
