<?php

namespace Backend\Modules\CommerceCashOnDelivery\Installer;

use Backend\Core\Installer\ModuleInstaller;
use Backend\Core\Language\Language;

class Installer extends ModuleInstaller
{
    public function install(): void
    {
        $this->addModule('CommerceCashOnDelivery');
        $this->importLocale(__DIR__ . '/Data/locale.xml');
        $this->registerCommercePaymentMethod($this->getModule());
    }

    /**
     * Register this module in the Commerce module as available payment method
     */
    private function registerCommercePaymentMethod(string $module): void
    {
        foreach ($this->getLanguages() as $language) {
            Language::setLocale($language);
            $name = ucfirst(Language::lbl($this->getModule()));
            $this->getDatabase()->execute(
                'INSERT INTO commerce_payment_methods (name, module, language) VALUES (?, ?, ?)',
                [$name, $module, $language]
            );
        }
    }
}
