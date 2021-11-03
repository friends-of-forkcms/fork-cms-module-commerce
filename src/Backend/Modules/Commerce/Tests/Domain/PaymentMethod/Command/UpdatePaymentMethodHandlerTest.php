<?php

namespace Backend\Modules\Commerce\Tests\Domain\PaymentMethod\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\PaymentMethod\Command\UpdatePaymentMethod;
use Backend\Modules\Commerce\Domain\PaymentMethod\Command\UpdatePaymentMethodHandler;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodRepository;
use Common\ModulesSettings;
use ForkCMS\App\BaseModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UpdatePaymentMethodHandlerTest extends TestCase
{
    /** @var ModulesSettings|MockObject */
    private $settingsMock;
    /** @var PaymentMethodRepository|MockObject */
    private $repositoryMock;

    protected function setUp(): void
    {
        // Mock the ModulesSettings and Locale static methods
        $this->settingsMock = $this->getMockBuilder(ModulesSettings::class)->disableOriginalConstructor()->getMock();
        $this->settingsMock->method('get')->with('Core', 'languages', ['en'])->willReturn(['en']);
        $container = $this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock();
        $container->method('get')->with('fork.settings')->willReturn($this->settingsMock);
        $container->method('getParameter')->with('site.default_language')->willReturn('en');
        BaseModel::setContainer($container);

        // Mock PaymentMethodRepository
        $this->repositoryMock = $this
            ->getMockBuilder(PaymentMethodRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /** @test */
    public function it_can_handle_a_payment_method_update(): void
    {
        // Arrange
        $paymentMethod = new UpdatePaymentMethod(null, Locale::fromString('en'));
        $paymentMethod->name = 'MyCustomPaymentMethod';
        $paymentMethod->module = 'MyCustomPaymentModule';

        // Spies
        $this->repositoryMock->expects($this->once())->method('add');
        $this->settingsMock->expects($this->never())->method('set');

        // Act
        $handler = new UpdatePaymentMethodHandler($this->repositoryMock, $this->settingsMock);
        $handler->handle($paymentMethod);

        // Assert
        $this->assertInstanceOf(PaymentMethod::class, $paymentMethod->getPaymentMethod());
        $this->assertEquals($paymentMethod->name, $paymentMethod->getPaymentMethod()->getName());
        $this->assertEquals($paymentMethod->module, $paymentMethod->getPaymentMethod()->getModule());
        $this->assertEquals($paymentMethod->isEnabled, $paymentMethod->getPaymentMethod()->isEnabled());
    }

    /** @test */
    public function it_can_handle_a_payment_method_update_with_custom_settings(): void
    {
        // Arrange
        // We use an anonymous class to add a custom class property
        $paymentMethod = new class(null, Locale::fromString('en')) extends UpdatePaymentMethod {
            public ?string $myCustomProperty = 'customvalue';
        };
        $paymentMethod->name = 'MyCustomPaymentMethodWithSettings';
        $paymentMethod->module = 'MyCustomPaymentModuleWithSettings';

        // Spies
        $this->repositoryMock->expects($this->once())->method('add');
        $this->settingsMock
            ->expects($this->once())
            ->method('set')
            ->with('Commerce', 'MyCustomPaymentModuleWithSettings_en_myCustomProperty', 'customvalue');

        // Act
        $handler = new UpdatePaymentMethodHandler($this->repositoryMock, $this->settingsMock);
        $handler->handle($paymentMethod);

        // Assert
        $this->assertInstanceOf(PaymentMethod::class, $paymentMethod->getPaymentMethod());
        $this->assertEquals($paymentMethod->name, $paymentMethod->getPaymentMethod()->getName());
        $this->assertEquals($paymentMethod->module, $paymentMethod->getPaymentMethod()->getModule());
        $this->assertEquals($paymentMethod->isEnabled, $paymentMethod->getPaymentMethod()->isEnabled());
    }
}
