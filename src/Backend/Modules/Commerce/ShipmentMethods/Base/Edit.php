<?php

namespace Backend\Modules\Commerce\ShipmentMethods\Base;

use Backend\Core\Engine\Model;
use Backend\Core\Engine\TwigTemplate;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\ShipmentMethod\Exception\ShipmentMethodNotFound;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethod;
use Backend\Modules\Commerce\Domain\ShipmentMethod\ShipmentMethodRepository;
use Common\Exception\RedirectException;
use Common\ModulesSettings;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Edit
{
    protected Request $request;
    protected string $name;
    protected TwigTemplate $template;
    protected Locale $locale;
    protected ModulesSettings $settings;
    protected ShipmentMethodRepository $shipmentMethodRepository;
    protected EntityManager $entityManager;

    public function __construct()
    {
        $this->locale = Locale::workingLocale();
        $this->settings = Model::get('fork.settings');
        $this->shipmentMethodRepository = Model::get('commerce.repository.shipment_method');
        $this->entityManager = Model::getContainer()->get('doctrine.orm.entity_manager');
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setTemplate(TwigTemplate $template): void
    {
        $this->template = $template;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getTemplate(): TwigTemplate
    {
        return $this->template;
    }

    public function getTemplateName(): string
    {
        return '/Commerce/ShipmentMethods/' . $this->name . '/Layout/Edit.html.twig';
    }

    public function execute(): void
    {
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type    FQCN of the form type class i.e: MyClass::class
     * @param mixed  $data    The initial data for the form
     * @param array  $options Options for the form
     */
    public function createForm(string $type, $data = null, array $options = []): Form
    {
        return Model::get('form.factory')->create($type, $data, $options);
    }

    protected function updateData(Form $form): void
    {
        // Get the form data
        $data = $form->getData();

        // Install our shipment method or not
        $this->installShipmentMethod($data->installed);

        // Set available payment methods
        $this->saveSetting('available_payment_methods', $data->available_payment_methods->toArray());
    }

    /**
     * Save settings for our current shipment method.
     */
    protected function saveSetting(string $name, $value, bool $includeLanguage = true): void
    {
        $baseKey = $this->getBaseKey($includeLanguage);

        $this->settings->set('Commerce', $baseKey . '_' . $name, $value);
    }

    /**
     * Populate data transfer object with data from the database.
     */
    protected function getData(DataTransferObject $dataTransferObject, bool $includeLanguage = true): DataTransferObject
    {
        // Get the public vars
        $properties = get_object_vars($dataTransferObject);

        // Assign the properties to object transfer object
        foreach ($properties as $property => $defaultValue) {
            // Skip the installed var
            if ($property === 'installed') {
                continue;
            }

            $key = $this->getBaseKey($includeLanguage) . '_' . $property;;
            $value = $this->getDeserializedValueFromSettings($key, $defaultValue);

            $dataTransferObject->{$property} = $value;
        }

        // Check if our shipment method exists
        try {
            $this->shipmentMethodRepository->findOneByNameAndLocale($this->name, $this->locale);
            $dataTransferObject->installed = true;
        } catch (ShipmentMethodNotFound $e) {
            $dataTransferObject->installed = false;
        }

        return $dataTransferObject;
    }

    /**
     * Redirect to a given URL
     * This is a helper method as the actual implementation is located in the url class.
     *
     * @param string $url  the URL to redirect to
     * @param int    $code the redirect code, default is 302 which means this is a temporary redirect
     *
     * @throws RedirectException
     */
    public function redirect(string $url, int $code = Response::HTTP_FOUND): void
    {
        Model::get('url')->redirect($url, $code);
    }

    /**
     * Generate the data grid row key to highlight this shipment method.
     */
    public function getDataGridRowKey(): string
    {
        return 'row-shipment_method_' . $this->name;
    }

    protected function installShipmentMethod(bool $install): void
    {
        if ($install === true) {
            try {
                $shipmentMethod = $this->shipmentMethodRepository->findOneByNameAndLocale($this->name, $this->locale);
            } catch (ShipmentMethodNotFound $e) {
                $shipmentMethod = new ShipmentMethod(
                    $this->name,
                    $this->locale
                );
            }

            $this->shipmentMethodRepository->add($shipmentMethod);
        } else {
            $this->shipmentMethodRepository->removeByNameAndLocale($this->name, $this->locale);
        }
    }

    private function getBaseKey(bool $includeLanguage): string
    {
        $key = $this->name;

        if ($includeLanguage) {
            $key .= '_' . $this->locale;
        }

        return $key;
    }

    /**
     * Fetch the shipment method value from Fork settings
     */
    protected function getDeserializedValueFromSettings($key, $defaultValue)
    {
        $value = $this->settings->get('Commerce', $key, $defaultValue);

        // E.g. VAT reference
        // If it's an unmanaged doctrine entity, try to make it managed, which is needed for the form values
        if (is_object($value)) {
            $value = $this->refreshUnmanagedValue($value);
        }

        // E.g. payment options
        // If it's an array of unmanaged doctrine entities, try to make them managed, which is needed for the form values
        if (is_array($value)) {
            $deserializedValue = new ArrayCollection();
            foreach ($value as $item) {
                $deserializedValue->add($this->refreshUnmanagedValue($item));
            }
            return $deserializedValue;
        }

        return $value;
    }

    /**
     * Note: need a better way for (de)serializing from/to Doctrine entities!
     */
    private function refreshUnmanagedValue($value)
    {
        $class = get_class($value);

        try {
            $identifierFieldName = $this->entityManager->getClassMetadata($class)->getSingleIdentifierFieldName();

            // Try to refresh the object to a managed doctrine object
            if ($identifierFieldName && $this->entityManager->getMetadataFactory()->getMetadataFor($class)) {
                $identifierGetterName = "get$identifierFieldName";
                return $this->entityManager->find($class, $value->$identifierGetterName());
            }
        } catch (ORMException | MappingException $e) {}

        return $value;
    }
}
