<?php

namespace Backend\Modules\Commerce\Tests\Twig\Extensions;

use Backend\Modules\Commerce\Twig\Extensions\FormatMoneyExtension;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;

class FormatMoneyExtensionTest extends TestCase
{
    /** @test */
    public function it_can_list_the_correct_filters(): void
    {
        $extension = new FormatMoneyExtension();
        $filters = $extension->getFilters();
        $this->assertContainsOnlyInstancesOf(TwigFilter::class, $filters);
        $this->assertEquals('format_money', $filters[0]->getName());
        $this->assertEquals('format_money_decimal', $filters[1]->getName());
    }

    /**
     * @test
     * @dataProvider moneyProvider
     */
    public function it_can_format_money($money, $localeCode, $output): void
    {
        $extension = new FormatMoneyExtension();
        $this->assertEquals($output, $extension->formatMoney($money, $localeCode));
    }

    public static function moneyProvider(): array
    {
        return [
            [Money::EUR(5), 'nl_BE', '€ 0,05'],
            [Money::EUR(50), 'nl_BE', '€ 0,50'],
            [Money::EUR(500), 'nl_BE', '€ 5,00'],
            [Money::EUR(5000), 'nl_BE', '€ 50,00'],
            [Money::EUR(5), 'nl', '€ 0,05'],
            [Money::EUR(50), 'nl', '€ 0,50'],
            [Money::EUR(500), 'nl', '€ 5,00'],
            [Money::EUR(5000), 'nl', '€ 50,00'],
            [Money::EUR(6135), 'nl', '€ 61,35'],
            [Money::EUR(-6135), 'nl', '€ -61,35'],
            [Money::EUR(7505), 'nl', '€ 75,05'],
            [Money::USD(7505), 'nl_BE', 'US$ 75,05'],
            [Money::USD(5), 'en_US', '$0.05'],
            [Money::USD(50), 'en_US', '$0.50'],
            [Money::USD(500), 'en_US', '$5.00'],
            [Money::USD(5000), 'en_US', '$50.00'],
            [Money::USD(5), 'en', '$0.05'],
            [Money::USD(50), 'en', '$0.50'],
            [Money::USD(500), 'en', '$5.00'],
            [Money::USD(5000), 'en', '$50.00'],
            [Money::USD(6135), 'en', '$61.35'],
            [Money::USD(-6135), 'en', '-$61.35'],
        ];
    }

    /**
     * @test
     * @dataProvider moneyDecimalProvider
     */
    public function it_can_format_money_as_decimal_value($money, $output): void
    {
        $extension = new FormatMoneyExtension();
        $this->assertEquals($output, $extension->formatMoneyDecimal($money));
    }

    public static function moneyDecimalProvider(): array
    {
        return [
            [Money::EUR(5), '0.05'],
            [Money::EUR(50), '0.50'],
            [Money::EUR(500), '5.00'],
            [Money::EUR(5000), '50'],
            [Money::EUR(6135), '61.35'],
            [Money::EUR(-6135), '-61.35'],
            [Money::EUR(7505), '75.05'],
            [Money::USD(7505), '75.05'],
            [Money::USD(5), '0.05'],
            [Money::USD(50), '0.50'],
            [Money::USD(500), '5.00'],
            [Money::USD(5000), '50'],
            [Money::USD(6135), '61.35'],
            [Money::USD(-6135), '-61.35'],
        ];
    }
}
