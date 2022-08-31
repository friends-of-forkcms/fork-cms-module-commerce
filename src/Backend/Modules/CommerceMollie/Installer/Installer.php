<?php

namespace Backend\Modules\CommerceMollie\Installer;

use Backend\Core\Engine\Model;
use Backend\Core\Installer\ModuleInstaller;
use Backend\Core\Language\Language;
use Backend\Modules\CommerceMollie\Domain\Payment\MolliePayment;

class Installer extends ModuleInstaller
{
    public function install(): void
    {
        $this->configureEntities();
        $this->addModule('CommerceMollie');
        $this->importLocale(__DIR__ . '/Data/locale.xml');
        $this->registerCommercePaymentMethod($this->getModule());
    }

    private function configureEntities(): void
    {
        Model::get('fork.entity.create_schema')->forEntityClasses([
            MolliePayment::class,
        ]);
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
