<?php

namespace Backend\Modules\Commerce\PaymentMethods\Base\Checkout;

use Common\Core\Model;
use Common\ModulesSettings;
use Frontend\Core\Language\Locale;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

abstract class Options
{
    protected string $name;
    protected string $option;
    protected ModulesSettings $settings;
    protected ?Locale $language;

    public function __construct(string $name, string $option)
    {
        $this->name = $name;
        $this->option = $option;
        $this->language = Locale::frontendLanguage();
        $this->settings = Model::get('fork.settings');
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type    FQCN of the form type class i.e: MyClass::class
     * @param mixed  $data    The initial data for the form
     * @param array  $options Options for the form
     */
    protected function createForm(string $type, $data = null, array $options = []): Form
    {
        return Model::get('form.factory')->create($type, $data, $options);
    }

    /**
     * Get a setting.
     *
     * @param mixed $defaultValue
     * @param bool  $includeLanguage
     *
     * @return mixed
     */
    protected function getSetting(string $key, $defaultValue = null, $includeLanguage = true)
    {
        $baseKey = $this->name;

        if ($includeLanguage) {
            $baseKey .= '_' . $this->language->getLocale();
        }

        return $this->settings->get('Commerce', $baseKey . '_' . $key, $defaultValue);
    }

    /**
     * Populate our form with extra form fields.
     */
    abstract public function addFields(FormInterface $form): void;
}
