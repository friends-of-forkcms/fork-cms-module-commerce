<?php

namespace Backend\Modules\Commerce\Layout\Templates\Helper;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig helper to display a Money object in a human-readable format.
 * @see https://www.moneyphp.org/en/stable/features/formatting.html
 */
class FormatMoneyExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_money', [$this, 'formatMoney']),
            new TwigFilter('format_money_decimal', [$this, 'formatMoneyDecimal']),
        ];
    }

    /**
     * Formats a Money object to value with currency e.g. €1.00. Requires the intl extension!
     */
    public function formatMoney(Money $money, string $localeCode = FRONTEND_LANGUAGE): string
    {
        $currencies = new ISOCurrencies();

        $numberFormatter = new \NumberFormatter($localeCode, \NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

        return $moneyFormatter->format($money);
    }

    /**
     * Formats a Money object to a decimal format e.g. 1.00
     */
    public function formatMoneyDecimal(Money $money): float
    {
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return (float) $moneyFormatter->format($money);
    }
}
