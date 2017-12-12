<?php

namespace Backend\Modules\Catalog\PaymentMethods\Base\Checkout;

use Common\Core\Model;
use Common\ModulesSettings;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

abstract class Options
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $option;

    /**
     * @var ModulesSettings
     */
    protected $settings;

    /**
     * @var Language
     */
    protected $language;

    /**
     * Options constructor.
     *
     * @param string $name
     * @param string $option
     */
    public function __construct(string $name, string $option)
    {
        $this->name = $name;
        $this->option = $option;
        $this->settings = Model::get('fork.settings');
        $this->language = Locale::frontendLanguage();
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
    protected function createForm(string $type, $data = null, array $options = []): Form
    {
        return Model::get('form.factory')->create($type, $data, $options);
    }

    /**
     * Get a setting
     *
     * @param string $key
     * @param mixed $defaultValue
     * @param boolean $includeLanguage
     *
     * @return mixed
     */
    protected function getSetting(string $key, $defaultValue = null, $includeLanguage = true)
    {
        $baseKey = $this->name;

        if ($includeLanguage) {
            $baseKey .= '_'. $this->language->getLocale();
        }

        return $this->settings->get('Catalog', $baseKey .'_'. $key, $defaultValue);
    }

    /**
     * Populate our form with extra form fields
     *
     * @param FormInterface $form
     *
     * @return void
     */
    public abstract function addFields(FormInterface $form): void;
}
