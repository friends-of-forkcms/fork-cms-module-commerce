<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\PaymentMethod\Exception\PaymentMethodNotFound;
use Common\ModulesSettings;
use Doctrine\ORM\EntityManager;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class Edit extends BackendBaseActionEdit
{
    protected string $module;
    protected Locale $locale;
    protected ModulesSettings $settings;
    protected PaymentMethodRepository $paymentMethodRepository;
    protected bool $installed = false;
    protected EntityManager $entityManager;
    protected MessageBus $commandBus;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);
        $this->locale = Locale::workingLocale();
        $this->settings = Model::get('fork.settings');
        $this->paymentMethodRepository = Model::get('commerce.repository.payment_method');
        $this->entityManager = Model::get('doctrine.orm.entity_manager');
        $this->commandBus = Model::get('command_bus');
    }

    protected function getPaymentMethod(): PaymentMethod
    {
        try {
            return $this->paymentMethodRepository->findOneByIdAndLocale(
                $this->getRequest()->query->getInt('id'),
                Locale::workingLocale()
            );
        } catch (PaymentMethodNotFound $e) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));
        }
    }

    private function getBackLink(array $parameters = []): string
    {
        return Model::createUrlForAction('PaymentMethods', null, null, $parameters);
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
     * Save settings for our current payment method.
     */
    protected function saveSetting(string $name, $value, bool $includeLanguage = true): void
    {
        $baseKey = $this->getBaseKey($includeLanguage);

        $this->settings->set('Commerce', $baseKey . '_' . $name, $value);
    }

    /**
     * Populate data transfer object with data from the database.
     */
    protected function getData(PaymentMethodDataTransferObject $dataTransferObject, bool $includeLanguage = true): PaymentMethodDataTransferObject
    {
        // Get the public vars
        $properties = get_class_vars(get_class($dataTransferObject));
        $skipProperties = get_class_vars(PaymentMethodDataTransferObject::class);

        // Assign the properties to object transfer object
        foreach ($properties as $property => $defaultValue) {
            // Skip the values that are saved already on the PaymentMethod entity
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
     * Store data transfer object with the form data.
     */
    protected function setData(PaymentMethodDataTransferObject $dataTransferObject, bool $includeLanguage): void
    {
        // Get the public vars
        $properties = get_object_vars($dataTransferObject);
        $skipProperties = get_class_vars(PaymentMethodDataTransferObject::class);

        // Assign the properties to object transfer object
        foreach ($properties as $property => $value) {
            // Skip the values that are saved already on the PaymentMethod entity
            if (array_key_exists($property, $skipProperties)) {
                continue;
            }

            $key = $this->getBaseKey($includeLanguage) . '_' . $property;
            $value = $dataTransferObject->{$property};

            $this->settings->set('Commerce', $key, $value);
        }
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
