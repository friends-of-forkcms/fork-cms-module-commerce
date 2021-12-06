<?php

namespace Backend\Modules\CommerceCashOnDelivery\Tests\Domain\CashOnDelivery\Checkout;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\Factory\CartFactory;
use Backend\Modules\Commerce\Domain\PaymentMethod\Factory\PaymentMethodFactory;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\Settings\CommerceModuleSettingsRepository;
use Backend\Modules\Commerce\Domain\Vat\Factory\VatFactory;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery\Checkout\Quote;
use Common\ModulesSettings;
use Doctrine\Common\Collections\ArrayCollection;
use ForkCMS\App\BaseModel;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zenstruck\Foundry\Test\Factories;

class QuoteTest extends TestCase
{
    use Factories;

    protected function setUp(): void
    {
        // Set globals
        if (!defined('FRONTEND_LANGUAGE')) {
            define('FRONTEND_LANGUAGE', 'en');
        }

        // The ProductDTO reaches out to the container for fork settings and the product repository, so we have to mock this.
        $forkSettings = $this->getMockBuilder(ModulesSettings::class)->disableOriginalConstructor()->getMock();
        $forkSettings->method('get')->willReturn(['en']); // both for language and active_language

        // Let the container return our mocks
        $container = $this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock();
        $container->method('getParameter')->with('site.default_language')->willReturn('en');
        $container
            ->method('get')
            ->willReturnMap([
                ['fork.settings', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $forkSettings],
            ]);
        BaseModel::setContainer($container);
    }

    /** @test */
    public function it_returns_no_quote_if_payment_is_not_allowed_based_on_shipment_method(): void
    {
        /** @var Cart $cart */
        $cart = CartFactory::new()->create(['shipment_method' => 'CommercePickup.Pickup shipment'])->object();
        $address = $cart->getShipmentAddress();

        // Mock the repository that fetches payment method & shipment method data from modules_settings
        $commerceModuleSettingsRepositoryMock = $this->getModuleSettingsMock(
            new ArrayCollection([]),
            Money::EUR(0),
            VatFactory::new()->create()->forceSet('id', 1)->object()
        );

        // Create a quote. Should be empty because the shipment method does not allow any payment method
        $checkoutQuote = new Quote('CommerceCashOnDelivery', $cart, $address, $commerceModuleSettingsRepositoryMock);
        $this->assertEmpty($checkoutQuote->getQuote());
    }

    /** @test */
    public function it_returns_a_quote_if_payment_is_allowed_based_on_shipment_method(): void
    {
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = PaymentMethodFactory::new()->isCashOnDelivery()->create()->object();
        /** @var Cart $cart */
        $cart = CartFactory::new()->create(['shipment_method' => 'CommercePickup.Pickup shipment'])->object();
        $address = $cart->getShipmentAddress();

        // Mock the repository that fetches payment method & shipment method data from modules_settings
        $commerceModuleSettingsRepositoryMock = $this->getModuleSettingsMock(
            new ArrayCollection([$paymentMethod]),
            Money::EUR(0),
            VatFactory::new()->create()->forceSet('id', 1)->object()
        );

        // Create a quote
        $checkoutQuote = new Quote($paymentMethod->getName(), $cart, $address, $commerceModuleSettingsRepositoryMock);
        $this->assertEquals([
            'Cash on delivery' => [
                'label' => 'Cash on delivery (€0.00)',
                'name' => 'Cash on delivery',
                'price' => Money::EUR(0),
            ],
        ], $checkoutQuote->getQuote());
    }

    private function getModuleSettingsMock(ArrayCollection $availablePaymentMethods, Money $price, Vat $vat)
    {
        $commerceModuleSettingsRepositoryMock = $this->getMockBuilder(CommerceModuleSettingsRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commerceModuleSettingsRepositoryMock
            ->method('getSetting')
            ->willReturnCallback(function ($moduleName, $property) use ($availablePaymentMethods, $price, $vat) {
                switch ($property) {
                    case 'availablePaymentMethods':
                        return $availablePaymentMethods;
                    case 'price':
                        return $price;
                    case 'vat':
                        return $vat;
                }
            });

        return $commerceModuleSettingsRepositoryMock;
    }
}
