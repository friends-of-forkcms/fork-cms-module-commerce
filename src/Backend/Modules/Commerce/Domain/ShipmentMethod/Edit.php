<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Settings\CommerceModuleSettingsRepository;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Exception\ShipmentMethodNotFound;
use Doctrine\ORM\EntityManager;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class Edit extends BackendBaseActionEdit
{
    protected string $module;
    protected ShipmentMethodRepository $shipmentMethodRepository;
    protected EntityManager $entityManager;
    protected MessageBus $commandBus;
    protected CommerceModuleSettingsRepository $shipmentMethodSettingsRepository;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);
        $this->shipmentMethodRepository = Model::get('commerce.repository.shipment_method');
        $this->entityManager = Model::get('doctrine.orm.entity_manager');
        $this->commandBus = Model::get('command_bus');
        $this->shipmentMethodSettingsRepository = new CommerceModuleSettingsRepository(
            Model::get('fork.settings'),
            Locale::workingLocale()
        );
    }

    protected function getShipmentMethod(): ShipmentMethod
    {
        try {
            return $this->shipmentMethodRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (ShipmentMethodNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return Model::createUrlForAction('ShipmentMethods', null, null, $parameters);
    }

    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * Get the template name based on the current payment method.
     */
    public function getTemplateName(): string
    {
        return '/' . $this->getModule() . '/Layout/Edit.html.twig';
    }

    public function display(string $template = null): void
    {
        parent::display($template ?? $this->getTemplateName());
    }
}
