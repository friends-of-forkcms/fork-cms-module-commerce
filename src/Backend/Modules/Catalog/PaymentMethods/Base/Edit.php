<?php

namespace Backend\Modules\Catalog\PaymentMethods\Base;

use Backend\Core\Engine\Model;
use Backend\Core\Engine\TwigTemplate;
use Backend\Core\Language\Locale;
use Backend\Modules\Catalog\Domain\PaymentMethod\Exception\PaymentMethodNotFound;
use Backend\Modules\Catalog\Domain\PaymentMethod\PaymentMethod;
use Backend\Modules\Catalog\Domain\PaymentMethod\PaymentMethodRepository;
use Common\Exception\RedirectException;
use Common\ModulesSettings;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Edit
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var TwigTemplate $template
     */
    protected $template;

    /**
     * @var Locale $locale
     */
    protected $locale;

    /**
     * @var ModulesSettings $settings
     */
    protected $settings;

    /**
     * @var PaymentMethodRepository $paymentMethodRepository
     */
    protected $paymentMethodRepository;

    /**
     * @var boolean $installed
     */
    protected $installed = false;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Initiate required data
     */
    public function __construct()
    {
        $this->locale = Locale::workingLocale();
        $this->settings = Model::get('fork.settings');
        $this->paymentMethodRepository = Model::get('catalog.repository.payment_method');
        $this->entityManager = Model::get('doctrine.orm.entity_manager');
    }

    /**
     * Set the request object
     *
     * @param Request $request
     *
     * @return void
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * Set the payment method name
     *
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Set the twig template which is used to handle our template
     *
     * @param TwigTemplate $template
     *
     * @return void
     */
    public function setTemplate(TwigTemplate $template): void
    {
        $this->template = $template;
    }

    /**
     * Get the current request
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get template with all the required assigments
     *
     * @return TwigTemplate
     */
    public function getTemplate(): TwigTemplate
    {
        return $this->template;
    }

    /**
     * Get the template name based on the current payment method
     *
     * @return string
     */
    public function getTemplateName(): string
    {
        return '/Catalog/PaymentMethods/' . $this->name . '/Layout/Edit.html.twig';
    }

    /**
     * Execute this controller
     *
     * @return void
     */
    public function execute(): void
    {
        $this->checkInstallation();
    }

    /**
     * Check if is installed
     *
     * @return void
     */
    private function checkInstallation()
    {
        try {
            $this->paymentMethodRepository->findOneByNameAndLocale($this->name, $this->locale);
            $this->installed = true;
        } catch (PaymentMethodNotFound $e) {
            $this->installed = false;
        }
    }

    /**
     * Install the payment method
     *
     * @return void
     */
    protected function install(): void
    {
        try {
            $paymentMethod = $this->paymentMethodRepository->findOneByNameAndLocale($this->name, $this->locale);
        } catch (PaymentMethodNotFound $e) {
            $paymentMethod = new PaymentMethod(
                $this->name,
                $this->locale
            );
        }

        $this->paymentMethodRepository->add($paymentMethod);
    }

    /**
     * Uninstall the payment method
     */
    protected function uninstall(): void
    {
        $this->paymentMethodRepository->removeByNameAndLocale($this->name, $this->locale);
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type FQCN of the form type class i.e: MyClass::class
     * @param mixed $data The initial data for the form
     * @param array $options Options for the form
     *
     * @return Form
     */
    public function createForm(string $type, $data = null, array $options = []): Form
    {
        return Model::get('form.factory')->create($type, $data, $options);
    }

    /**
     * Save settings for our current payment method
     *
     * @param string $name
     * @param mixed $value
     * @param bool $includeLanguage
     *
     * @return void
     */
    protected function saveSetting(string $name, $value, bool $includeLanguage = true): void
    {
        $baseKey = $this->getBaseKey($includeLanguage);

        $this->settings->set('Catalog', $baseKey . '_' . $name, $value);
    }

    /**
     * Populate data transfer object with data from the database
     *
     * @param DataTransferObject $dataTransferObject
     * @param bool $includeLanguage
     *
     * @return DataTransferObject
     */
    protected function getData(DataTransferObject $dataTransferObject, bool $includeLanguage = true): DataTransferObject
    {
        // Get the public vars
        $properties = get_object_vars($dataTransferObject);

        // Assign the properties to object transfer object
        foreach ($properties as $property => $defaultValue) {
            // Skip the installed var
            if ($property == 'installed') {
                continue;
            }

            $key = $this->getBaseKey($includeLanguage) . '_' . $property;
            $value = $this->settings->get('Catalog', $key, $defaultValue);
            $dataTransferObject->{$property} = $value;
        }

        // Check if our payment method exists
        try {
            $this->paymentMethodRepository->findOneByNameAndLocale($this->name, $this->locale);
            $dataTransferObject->installed = true;
        } catch (PaymentMethodNotFound $e) {
            $dataTransferObject->installed = false;
        }

        return $dataTransferObject;
    }

    /**
     * Store data transfer object with the form data
     *
     * @param DataTransferObject $dataTransferObject
     * @param bool $includeLanguage
     *
     * @return void
     */
    protected function setData(DataTransferObject $dataTransferObject, bool $includeLanguage): void
    {
        // Get the public vars
        $properties = get_object_vars($dataTransferObject);

        // Assign the properties to object transfer object
        foreach ($properties as $property => $value) {
            // Skip the installed var
            if ($property == 'installed') {
                continue;
            }

            $key = $this->getBaseKey($includeLanguage) . '_' . $property;
            $value = $dataTransferObject->{$property};

            $this->settings->set('Catalog', $key, $value);
        }
    }

    /**
     * Redirect to a given URL
     *
     * This is a helper method as the actual implementation is located in the url class
     *
     * @param string $url The URL to redirect to.
     * @param int $code The redirect code, default is 302 which means this is a temporary redirect.
     *
     * @throws RedirectException
     */
    public function redirect(string $url, int $code = Response::HTTP_FOUND): void
    {
        Model::get('url')->redirect($url, $code);
    }

    /**
     * Generate the data grid row key to highlight this payment method
     *
     * @return string
     */
    public function getDataGridRowKey(): string
    {
        return 'row-payment_method_' . $this->name;
    }

    /**
     * Install the current payment method
     *
     * @param bool $install
     *
     * @return void
     */
    protected function installPaymentMethod(bool $install): void
    {
        if ($install === true && $this->installed === false) {
            $this->install();
        }

        if ($install === false && $this->installed === true) {
            $this->uninstall();
        }
    }

    /**
     * Get the settings base key
     *
     * @param bool $includeLanguage
     *
     * @return string
     */
    private function getBaseKey(bool $includeLanguage): string
    {
        $key = $this->name;

        if ($includeLanguage) {
            $key .= '_' . $this->locale;
        }

        return $key;
    }
}
