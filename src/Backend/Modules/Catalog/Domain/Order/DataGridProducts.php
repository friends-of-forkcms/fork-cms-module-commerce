<?php

namespace Backend\Modules\Catalog\Domain\Order;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Language;
use Backend\Modules\Catalog\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Catalog\Domain\Product\Product;

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
        $query = 'SELECT i.id, i.title as product, i.sku as article_number, i.amount, i.price, i.total
                    FROM catalog_order_products i WHERE i.order_id = ? ORDER BY i.id ASC';

        parent::__construct($query, [$order->getId()]);

        // assign column functions
        $this->setColumnHidden('id');
        $this->setColumnFunction(array(self::class, 'getProductOptions'), ['[product]', '[id]'], 'product', true);
        $this->setColumnFunction(array(self::class, 'getFormatPrice'), '[total]', 'total', true);
        $this->setColumnFunction(array(self::class, 'getFormatPrice'), '[price]', 'price', true);
    }

    public static function getHtml(Order $order): string
    {
        return (new self($order))->getContent();
    }

    public static function getFormatPrice($price)
    {
        return '&euro; ' .number_format($price, 2, ',', '.');
    }

    public static function getProductOptions($title, $id)
    {
        $orderProduct = self::getOrderProduct($id);

        if (!$orderProduct) {
            return $title;
        }

        $titleString = [];

        // Check if the dimensions should be displayed
        if ($orderProduct->getType() == Product::TYPE_DIMENSIONS) {
            $titleString[] = '<h5 class="mt-0" style="margin-top:0;">' . $title . ' - ' . $orderProduct->getWidth() .'mm x ' . $orderProduct->getHeight() .'mm</h5>';
            $titleString[] = '<i><strong>' . ucfirst(Language::lbl('ProductionDimensions')) .'</strong> ' . $orderProduct->getOrderWidth().'mm x '. $orderProduct->getOrderHeight() .'mm</i>';
        } else {
            $titleString[] = '<h5 class="mt-0" style="margin-top:0;">' . $title .'</h5>';
        }

        // Add extra product options
        foreach ($orderProduct->getProductOptions() as $productOption) {
            $productOptionTitle = '<strong>'.$productOption->getTitle() .'</strong> - '. $productOption->getValue();

            if ($productOption->getSku()) {
                $productOptionTitle .= ' <strong>(' . $productOption->getSku().')</strong>';
            }

            $titleString[] = $productOptionTitle;
        }

        $notifications = [];
        foreach ($orderProduct->getProductNotifications() as $notification) {
            $notifications[] .= '<p class="m-0 text-warning" style="margin:0;">'. $notification->getMessage() .'</p>';
        }

        return implode('<br />', $titleString) . implode('', $notifications);
    }

    private static function getOrderProduct($id): ?OrderProduct
    {
        $repository = BackendModel::get('catalog.repository.order_product');

        return $repository->findOneById($id);
    }
}
