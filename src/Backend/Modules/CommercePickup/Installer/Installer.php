<?php

namespace Backend\Modules\CommercePickup\Installer;

use Backend\Core\Installer\ModuleInstaller;
use Backend\Core\Language\Language;

class Installer extends ModuleInstaller
{
    public function install(): void
    {
        $this->addModule('CommercePickup');
        $this->importLocale(__DIR__ . '/Data/locale.xml');
        $this->registerCommerceShipmentMethod($this->getModule());
    }

    /**
     * Register this module in the Commerce module as available shipment method
     */
    private function registerCommerceShipmentMethod(string $module): void
    {
        foreach ($this->getLanguages() as $language) {
            Language::setLocale($language);
            $name = ucfirst(Language::lbl($this->getModule()));
            $this->getDatabase()->execute(
                'INSERT INTO commerce_shipment_methods (name, module, language) VALUES (?, ?, ?)',
                [$name, $module, $language]
            );
        }
    }
}
