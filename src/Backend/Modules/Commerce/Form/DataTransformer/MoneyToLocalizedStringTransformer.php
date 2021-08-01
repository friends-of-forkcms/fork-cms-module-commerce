<?php

namespace Backend\Modules\Commerce\Form\DataTransformer;

use Money\Currencies;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exception\ParserException;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;

/**
 * Transforms between a normalized format and a localized money string.
 * Useful as ModelTransformer on MoneyType form fields, since it directly maps the value to a Money object.
 */
class MoneyToLocalizedStringTransformer extends NumberToLocalizedStringTransformer
{
    private Currency $currency;
    private Currencies $currencies;

    public function __construct(?Currency $currency = null, int $scale = 2, bool $grouping = false, Currencies $currencies = null)
    {
        $this->currency = $currency ?? new Currency('EUR');
        $this->currencies = $currencies ?? new ISOCurrencies();
        parent::__construct($scale, $grouping);
    }

    /**
     * Transforms a normalized format into a localized money string that can be rendered in the form.
     */
    public function transform($value): ?string
    {
        /** @var Money|null $value */
        if ($value === null) {
            return null;
        }

        $moneyFormatter = new DecimalMoneyFormatter($this->currencies);

        return parent::transform($moneyFormatter->format($value));
    }

    /**
     * Transforms a localized money string into a normalized format.
     */
    public function reverseTransform($value): Money
    {
        $value = parent::reverseTransform((string) $value);
        $moneyParser = new DecimalMoneyParser($this->currencies);

        try {
            return $moneyParser->parse(sprintf('%.53f', $value), $this->currency);
        } catch (ParserException $e) {
            throw new TransformationFailedException($e->getMessage());
        }
    }
}
