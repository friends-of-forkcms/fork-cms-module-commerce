<?php

namespace Backend\Modules\Catalog\Actions;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;

/**
 * This edit action allows you to edit a specific payment method
 *
 * @author Jacob van Dam <j.vandam@jvdict.nl>
 */
class EditPaymentMethod extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute(): void
    {
        parent::execute();

        // Set the payment method id
        $name = str_replace('payment_method_', '', $this->getRequest()->get('id'));

        $className = $this->getPaymentMethodAction($name);

        // First check if our class exists
        if (!class_exists($className)) {
            throw new \Exception("Class {$name} is not found!");
        }

        // Load our class
        /**
         * @var \Backend\Modules\Catalog\PaymentMethods\Base\Edit $paymentMethod
         */
        $paymentMethod = new $className();

        // Set some required parameters
        $paymentMethod->setRequest($this->getRequest());
        $paymentMethod->setName($name);
        $paymentMethod->setTemplate($this->template);

        // Execute the payment method
        $paymentMethod->execute();

        // Set the required template
        $this->template = $paymentMethod->getTemplate();
        $this->display($paymentMethod->getTemplateName());
    }
    /**
     * @param string $name
     *
     * @return string
     */
    private function getPaymentMethodAction(string $name): string
    {
        return "\\Backend\\Modules\\Catalog\\PaymentMethods\\{$name}\\Actions\\Edit";
    }
}
