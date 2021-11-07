<?php

namespace Backend\Modules\Commerce\Tests\Domain\ShipmentMethod\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Command\UpdateShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Command\UpdateShipmentMethodHandler;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodRepository;
use Common\ModulesSettings;
use ForkCMS\App\BaseModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UpdateShipmentMethodHandlerTest extends TestCase
{
    /** @var ModulesSettings&MockObject */
    private $settingsMock;
    /** @var ShipmentMethodRepository&MockObject */
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

        // Mock ShipmentMethodRepository
        $this->repositoryMock = $this
            ->getMockBuilder(ShipmentMethodRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /** @test */
    public function it_can_handle_a_shipment_method_update(): void
    {
        // Arrange
        $shipmentMethod = new UpdateShipmentMethod(null, Locale::fromString('en'));
        $shipmentMethod->name = 'MyCustomShipmentMethod';
        $shipmentMethod->module = 'MyCustomShipmentModule';

        // Spies
        $this->repositoryMock->expects($this->once())->method('add');
        $this->settingsMock->expects($this->never())->method('set');

        // Act
        $handler = new UpdateShipmentMethodHandler($this->repositoryMock, $this->settingsMock);
        $handler->handle($shipmentMethod);

        // Assert
        $this->assertInstanceOf(ShipmentMethod::class, $shipmentMethod->getShipmentMethod());
        $this->assertEquals($shipmentMethod->name, $shipmentMethod->getShipmentMethod()->getName());
        $this->assertEquals($shipmentMethod->module, $shipmentMethod->getShipmentMethod()->getModule());
        $this->assertEquals($shipmentMethod->isEnabled, $shipmentMethod->getShipmentMethod()->isEnabled());
    }

    /** @test */
    public function it_can_handle_a_shipment_method_update_with_custom_settings(): void
    {
        // Arrange
        // We use an anonymous class to add a custom class property
        $shipmentMethod = new class (null, Locale::fromString('en')) extends UpdateShipmentMethod {
            public ?string $myCustomProperty = 'customvalue';
        };
        $shipmentMethod->name = 'MyCustomShipmentMethodWithSettings';
        $shipmentMethod->module = 'MyCustomShipmentModuleWithSettings';

        // Spies
        $this->repositoryMock->expects($this->once())->method('add');
        $this->settingsMock
            ->expects($this->once())
            ->method('set')
            ->with('Commerce', 'MyCustomShipmentModuleWithSettings_en_myCustomProperty', 'customvalue');

        // Act
        $handler = new UpdateShipmentMethodHandler($this->repositoryMock, $this->settingsMock);
        $handler->handle($shipmentMethod);

        // Assert
        $this->assertInstanceOf(ShipmentMethod::class, $shipmentMethod->getShipmentMethod());
        $this->assertEquals($shipmentMethod->name, $shipmentMethod->getShipmentMethod()->getName());
        $this->assertEquals($shipmentMethod->module, $shipmentMethod->getShipmentMethod()->getModule());
        $this->assertEquals($shipmentMethod->isEnabled, $shipmentMethod->getShipmentMethod()->isEnabled());
    }
}
