<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod\Command;

use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\PaymentMethod\Exception\PaymentMethodNotFound;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Commerce\Domain\PaymentMethod\PaymentMethodRepository;
use Backend\Modules\Commerce\PaymentMethods\Base\DataTransferObject;
use Common\ModulesSettings;

class UpdatePaymentMethodHandler
{
    private PaymentMethodRepository $paymentMethodRepository;
    private ModulesSettings $settings;
    private Locale $locale;

    public function __construct(PaymentMethodRepository $paymentMethodRepository, ModulesSettings $settings)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->settings = $settings;
        $this->locale = Locale::workingLocale();
    }

    public function handle(DataTransferObject $dataTransferObject): void
    {
        // Save the payment method to Fork Settings
        $this->setData($dataTransferObject, true);

        // Install/update the payment method
        $paymentMethod = PaymentMethod::fromDataTransferObject($dataTransferObject);
        $this->paymentMethodRepository->add($paymentMethod);
        $dataTransferObject->setPaymentMethod($paymentMethod);
    }

    /**
     * Store data transfer object with the form data.
     */
    private function setData(DataTransferObject $dataTransferObject, bool $includeLanguage): void
    {
        // Get the public vars
        $properties = get_class_vars(get_class($dataTransferObject));
        $skipProperties = get_class_vars(DataTransferObject::class);

        // Assign the properties to object transfer object
        foreach ($properties as $property => $value) {
            // Skip the values that are saved already on the PaymentMethod entity
            if (array_key_exists($property, $skipProperties)) {
                continue;
            }

            $key = $this->getBaseKey($dataTransferObject, $includeLanguage) . '_' . $property;
            $value = $dataTransferObject->{$property};

            $this->settings->set('Commerce', $key, $value);
        }
    }

    /**
     * Get the settings base key.
     */
    private function getBaseKey(DataTransferObject $dataTransferObject, bool $includeLanguage): string
    {
        $key = $dataTransferObject->module;

        if ($includeLanguage) {
            $key .= '_' . $this->locale;
        }

        return $key;
    }
}
