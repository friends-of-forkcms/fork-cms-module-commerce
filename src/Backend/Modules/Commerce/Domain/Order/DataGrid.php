<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use DateTimeInterface;
use Money\Currency;
use Money\Money;
use Tbbc\MoneyBundle\Formatter\MoneyFormatter;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridDatabase
{
    public function __construct(
        ?string $searchQuery,
        ?OrderStatus $orderStatus,
        ?DateTimeInterface $orderDateStartedAt,
        ?DateTimeInterface $orderDateEndedAt
    ) {
        // Compose the order filters
        $whereFilter = ['1=1'];
        $params = [];
        if (!empty($searchQuery)) {
            $whereFilter[] = 'CONCAT(a.firstName, " ", a.lastName) LIKE :query';
            $params['query'] = "%{$searchQuery}%";
        }
        if ($orderStatus !== null) {
            $whereFilter[] = 's.id = :orderStatusId';
            $params['orderStatusId'] = $orderStatus->getId();
        }
        if ($orderDateStartedAt !== null) {
            $whereFilter[] = 'i.createdAt >= :orderDateStartedAt';
            $params['orderDateStartedAt'] = $orderDateStartedAt->setTime(0, 0)->format('Y-m-d H:i:s'); // From start of day
        }
        if ($orderDateEndedAt !== null) {
            $whereFilter[] = 'i.createdAt <= :orderDateEndedAt';
            $params['orderDateEndedAt'] = $orderDateEndedAt->setTime(23, 59, 59)->format('Y-m-d H:i:s'); // Until end of day
        }

        // Create the query for our datagrid
        $whereFilterString = implode(' AND ', $whereFilter);
        $query = "
            WITH ranked_order_histories AS (
                SELECT
                    *,
                    ROW_NUMBER() OVER (PARTITION BY orderId ORDER BY createdAt DESC) AS orderIdRank
                FROM commerce_order_histories
            )
            SELECT
                i.id as orderNumber,
                UNIX_TIMESTAMP(i.createdAt) as orderDate,
                CONCAT(a.firstName, ' ', a.lastName) as name,
                s.title AS orderStatus,
                s.color AS orderStatusColor,
                i.invoiceNumber,
                a.companyName,
                i.totalCurrencyCode,
                i.totalAmount AS total
            FROM commerce_orders AS i
            INNER JOIN commerce_order_addresses a ON a.id = i.invoiceAddressId
            INNER JOIN ranked_order_histories AS h ON h.orderId = i.id AND orderIdRank = 1 -- Only take the most recent order history
            INNER JOIN commerce_order_statuses AS s ON s.id = h.orderStatusId
            WHERE ${whereFilterString}
        ";

        parent::__construct($query, $params);

        // assign column functions
        $this->setColumnsHidden(['companyName', 'orderNumber', 'orderStatusColor']);
        $this->setColumnFunction([new DataGridFunctions(), 'getLongDate'], '[orderDate]', 'orderDate', true);
        $this->setColumnFunction([self::class, 'getFormattedMoney'], ['[total]', '[totalCurrencyCode]'], 'total', true);
        $this->setColumnFunction([self::class, 'getName'], ['[companyName]', '[name]'], 'name');
        $this->setColumnFunction([self::class, 'showColorDot'], ['[orderStatus]', '[orderStatusColor]'], 'orderStatus', true);
        $this->setColumnsHidden(['totalCurrencyCode']);

        // sorting
        $this->setSortingColumns(['orderDate', 'invoiceNumber', 'total', 'orderStatus'], 'orderDate');
        $this->setSortParameter('desc');

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditOrder')) {
            $editUrl = Model::createUrlForAction('EditOrder', null, null, ['id' => '[orderNumber]'], false);
            $this->setColumnURL('name', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(
        ?string $searchQuery,
        ?OrderStatus $orderStatus,
        ?DateTimeInterface $orderDateStartedAt,
        ?DateTimeInterface $orderDateEndedAt
    ): string {
        return (new self($searchQuery, $orderStatus, $orderDateStartedAt, $orderDateEndedAt))->getContent();
    }

    public function getName($companyName, $name): string
    {
        if (!$companyName) {
            return $name;
        }

        return $companyName . ' (' . $name . ')';
    }

    public static function getFormattedMoney(int $amount, string $currencyCode): string
    {
        $money = new Money($amount, new Currency($currencyCode));
        $moneyFormatter = new MoneyFormatter();

        return $moneyFormatter->localizedFormatMoney($money);
    }

    /**
     * Show a colored bullet in front of the title
     */
    public static function showColorDot(string $title, ?string $color, string $editUrl = null): string
    {
        $color = $color ?? 'currentColor';

        return <<<HTML
<svg fill="$color" viewBox="0 0 8 8" style="width: 8px; height: 8px; margin-right: 2px;">
  <circle cx="4" cy="4" r="4"></circle>
</svg>
$title
HTML;
    }
}
