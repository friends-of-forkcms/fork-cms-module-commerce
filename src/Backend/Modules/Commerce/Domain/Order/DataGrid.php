<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use DateTimeInterface;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

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
            $whereFilter[] = 'CONCAT(a.first_name, " ", a.last_name) LIKE :query';
            $params['query'] = "%{$searchQuery}%";
        }
        if ($orderStatus !== null) {
            $whereFilter[] = 's.id = :orderStatusId';
            $params['orderStatusId'] = $orderStatus->getId();
        }
        if ($orderDateStartedAt !== null) {
            $whereFilter[] = 'i.created_on >= :orderDateStartedAt';
            $params['orderDateStartedAt'] = $orderDateStartedAt->setTime(0, 0)->format('Y-m-d H:i:s'); // From start of day
        }
        if ($orderDateEndedAt !== null) {
            $whereFilter[] = 'i.created_on <= :orderDateEndedAt';
            $params['orderDateEndedAt'] = $orderDateEndedAt->setTime(23, 59, 59)->format('Y-m-d H:i:s'); // Until end of day
        }

        // Create the query for our datagrid
        $whereFilterString = implode(' AND ', $whereFilter);
        $query = "
            WITH ranked_order_histories AS (
                SELECT
                    *,
                    ROW_NUMBER() OVER (PARTITION BY order_id ORDER BY created_on DESC) AS order_id_rank
                FROM commerce_order_histories
            )
            SELECT
                i.id as order_number,
                UNIX_TIMESTAMP(i.created_on) as order_date,
                CONCAT(a.first_name, ' ', a.last_name) as name,
                s.title AS order_status,
                s.color AS order_status_color,
                i.invoice_number,
                a.company_name,
                i.total_currency_code,
                i.total_amount AS total
            FROM commerce_orders AS i
            INNER JOIN commerce_order_addresses a ON a.id = i.invoice_address_id
            INNER JOIN ranked_order_histories AS h ON h.order_id = i.id AND order_id_rank = 1 -- Only take the most recent order history
            INNER JOIN commerce_order_statuses AS s ON s.id = h.order_status_id
            WHERE ${whereFilterString}
        ";

        parent::__construct($query, $params);

        // assign column functions
        $this->setColumnsHidden(['company_name', 'order_number', 'order_status_color']);
        $this->setColumnFunction([new DataGridFunctions(), 'getLongDate'], '[order_date]', 'order_date', true);
        $this->setColumnFunction([self::class, 'getFormattedMoney'], ['[total]', '[total_currency_code]'], 'total', true);
        $this->setColumnFunction([self::class, 'getName'], ['[company_name]', '[name]'], 'name');
        $this->setColumnFunction([self::class, 'showColorDot'], ['[order_status]', '[order_status_color]'], 'order_status', true);
        $this->setColumnsHidden(['total_currency_code']);

        // sorting
        $this->setSortingColumns(['order_date', 'invoice_number', 'total', 'order_status'], 'order_date');
        $this->setSortParameter('desc');

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditOrder')) {
            $editUrl = Model::createUrlForAction('EditOrder', null, null, ['id' => '[order_number]'], false);
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
        $currencies = new ISOCurrencies();

        $numberFormatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

        return $moneyFormatter->format($money);
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
