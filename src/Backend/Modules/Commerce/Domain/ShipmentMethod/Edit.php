<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Exception\ShipmentMethodNotFound;
use Common\ModulesSettings;
use Doctrine\ORM\EntityManager;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class Edit extends BackendBaseActionEdit
{
    protected string $module;
    protected Locale $locale;
    protected ModulesSettings $settings;
    protected ShipmentMethodRepository $shipmentMethodRepository;
    protected EntityManager $entityManager;
    protected MessageBus $commandBus;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);
        $this->locale = Locale::workingLocale();
        $this->settings = Model::get('fork.settings');
        $this->shipmentMethodRepository = Model::get('commerce.repository.shipment_method');
        $this->entityManager = Model::get('doctrine.orm.entity_manager');
        $this->commandBus = Model::get('command_bus');
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

    /**
     * Populate data transfer object with data from the database.
     */
    protected function getData(ShipmentMethodDataTransferObject $dataTransferObject, bool $includeLanguage = true): ShipmentMethodDataTransferObject
    {
        // Get the public vars
        $properties = get_class_vars(get_class($dataTransferObject));
        $skipProperties = get_class_vars(ShipmentMethodDataTransferObject::class);

        // Assign the properties to object transfer object
        foreach ($properties as $property => $defaultValue) {
            // Skip the values that are saved already on the ShipmentMethod entity
            if (array_key_exists($property, $skipProperties)) {
                continue;
            }

            $key = $this->getBaseKey($includeLanguage) . '_' . $property;
            $value = $this->settings->get('Commerce', $key, $defaultValue);
            $dataTransferObject->{$property} = $value;
        }

        return $dataTransferObject;
    }

    /**
     * Get the settings base key.
     */
    private function getBaseKey(bool $includeLanguage): string
    {
        $key = $this->module;

        if ($includeLanguage) {
            $key .= '_' . $this->locale;
        }

        return $key;
    }
}
