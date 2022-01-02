<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Language;
use Backend\Modules\Commerce\Domain\OrderProduct\OrderProduct;
use Backend\Modules\Commerce\Domain\Product\Product;
use Money\Currency;
use Money\Money;
use Tbbc\MoneyBundle\Formatter\MoneyFormatter;

class DataGridProducts extends DataGridDatabase
{
    /**
     * DataGrid constructor.
     *
     * @throws \Exception
     */
    public function __construct(Order $order)
    {
        $query = '
            SELECT
                i.id,
                i.title as product,
                i.sku as article_number,
                i.amount,
                i.priceAmount AS price,
                i.priceCurrencyCode,
                i.totalAmount AS total,
                i.totalCurrencyCode
            FROM commerce_order_products i
            WHERE i.orderId = ?
            ORDER BY i.id ASC';

        parent::__construct($query, [$order->getId()]);

        // assign column functions
        $this->setColumnHidden('id');
        $this->setColumnFunction('htmlspecialchars', ['[product]'], 'product', false);
        $this->setColumnFunction('htmlspecialchars', ['[sku]'], 'sku', false);
        $this->setColumnFunction([self::class, 'getProductOptions'], ['[product]', '[id]'], 'product', true);
        $this->setColumnFunction([self::class, 'getFormattedMoney'], ['[total]', '[totalCurrencyCode]'], 'total', true);
        $this->setColumnFunction([self::class, 'getFormattedMoney'], ['[price]', '[priceCurrencyCode]'], 'price', true);
        $this->setColumnsHidden(['totalCurrencyCode']);
        $this->setColumnsHidden(['priceCurrencyCode']);
    }

    public static function getHtml(Order $order): string
    {
        return (new self($order))->getContent();
    }

    public static function getProductOptions($title, $id)
    {
        $orderProduct = self::getOrderProduct($id);

        if (!$orderProduct) {
            return $title;
        }

        $titleString = [];

        // Check if the dimensions should be displayed
        if ($orderProduct->getType() === Product::TYPE_DIMENSIONS) {
            $titleString[] = '<h5 class="mt-0" style="margin-top:0;">' . $title . ' - ' . $orderProduct->getWidth() . 'mm x ' . $orderProduct->getHeight() . 'mm</h5>';
            $titleString[] = '<i><strong>' . ucfirst(Language::lbl('ProductionDimensions')) . '</strong> ' . $orderProduct->getOrderWidth() . 'mm x ' . $orderProduct->getOrderHeight() . 'mm</i>';
        } else {
            $titleString[] = '<h5 class="mt-0" style="margin-top:0;">' . $title . '</h5>';
        }

        // Add extra product options
        foreach ($orderProduct->getProductOptions() as $productOption) {
            $productOptionTitle = '<strong>' . $productOption->getTitle() . '</strong> - ' . $productOption->getValue();

            if ($productOption->getSku()) {
                $productOptionTitle .= ' <strong>(' . $productOption->getSku() . ')</strong>';
            }

            $titleString[] = $productOptionTitle;
        }

        $notifications = [];
        foreach ($orderProduct->getProductNotifications() as $notification) {
            $notifications[] .= '<p class="m-0 text-warning" style="margin:0;">' . $notification->getMessage() . '</p>';
        }

        return implode('<br />', $titleString) . implode('', $notifications);
    }

    private static function getOrderProduct($id): ?OrderProduct
    {
        $repository = BackendModel::get('commerce.repository.order_product');

        return $repository->findOneById($id);
    }

    public static function getFormattedMoney(int $amount, string $currencyCode): string
    {
        $money = new Money($amount, new Currency($currencyCode));
        $moneyFormatter = new MoneyFormatter();

        return $moneyFormatter->localizedFormatMoney($money);
    }
}
