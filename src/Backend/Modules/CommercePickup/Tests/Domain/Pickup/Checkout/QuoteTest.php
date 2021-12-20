<?php

namespace Backend\Modules\CommercePickup\Tests\Domain\Pickup\Checkout;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Cart\Factory\CartFactory;
use Backend\Modules\Commerce\Domain\PaymentMethod\Factory\PaymentMethodFactory;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\Settings\CommerceModuleSettingsRepository;
use Backend\Modules\Commerce\Domain\Vat\Exception\VatNotFound;
use Backend\Modules\Commerce\Domain\Vat\Factory\VatFactory;
use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\CommerceCashOnDelivery\Domain\CashOnDelivery\Factory\CashOnDeliveryPaymentMethodFactory;
use Backend\Modules\CommercePickup\Domain\Pickup\Checkout\Quote;
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
    public function it_returns_a_quote_for_a_pickup_shipment(): void
    {
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = CashOnDeliveryPaymentMethodFactory::new()->create()->object();
        /** @var Cart $cart */
        $cart = CartFactory::new()->create()->object();
        $address = $cart->getShipmentAddress();

        // Mock the repository that fetches payment method & shipment method data from modules_settings
        $commerceModuleSettingsRepositoryMock = $this->getModuleSettingsMock(
            new ArrayCollection([$paymentMethod]),
            Money::EUR(0),
            VatFactory::new()->create()->forceSet('id', 1)->object()
        );

        $checkoutQuote = new Quote('Pickup shipment', $cart, $address, $commerceModuleSettingsRepositoryMock);
        $this->assertEquals([
            'Pickup shipment' => [
                'label' => 'Pickup shipment (€0.00)',
                'name' => 'Pickup shipment',
                'price' => Money::EUR(0),
                'vat' => ['id' => 1, 'price' => Money::EUR(0)],
                'available_payment_methods' => new ArrayCollection([$paymentMethod]),
            ],
        ], $checkoutQuote->getQuote());
    }

    /** @test */
    public function it_should_throw_an_exception_if_vat_is_invalid(): void
    {
        /** @var Cart $cart */
        $cart = CartFactory::new()->create()->object();
        $address = $cart->getShipmentAddress();

        // Mock the repository that fetches payment method & shipment method data from modules_settings
        $commerceModuleSettingsRepositoryMock = $this->getModuleSettingsMock(
            new ArrayCollection(),
            Money::EUR(0),
            null // invalid VAT
        );

        $checkoutQuote = new Quote('Pickup shipment', $cart, $address, $commerceModuleSettingsRepositoryMock);
        $this->expectException(VatNotFound::class);
        $checkoutQuote->getQuote();
    }

    private function getModuleSettingsMock(ArrayCollection $availablePaymentMethods, Money $price, ?Vat $vat)
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
