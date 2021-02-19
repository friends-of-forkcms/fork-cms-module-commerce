<?php

namespace Backend\Modules\Catalog\Domain\PaymentMethod;

use Backend\Modules\Catalog\PaymentMethods\Base\Checkout\Options;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PaymentMethodSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'preSubmit'
        );
    }

    private function addPaymentMethodForm(FormInterface $form, ?string $paymentMethod)
    {
        try {
            $options = $this->getPaymentMethodOptions($paymentMethod);

            $options->addFields($form);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * Get the options class
     *
     * @param string $paymentMethod
     *
     * @return Options
     * @throws \Exception
     *
     */
    private function getPaymentMethodOptions(?string $paymentMethod): Options
    {
        $method = explode('.', $paymentMethod);

        if (count($method) != 2) {
            throw new \Exception('Invalid payment method');
        }

        $className = "\\Backend\\Modules\\Catalog\\PaymentMethods\\{$method[0]}\\Checkout\\Options";

        if (!class_exists($className)) {
            throw new \Exception('Class ' . $className . ' not found');
        }

        /**
         * @var Options $class
         */
        $class = new $className($method[0], $method[1]);

        return $class;
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $paymentMethod = array_key_exists('payment_method', $data) ? $data['payment_method'] : null;

        $this->addPaymentMethodForm($form, $paymentMethod);
    }
}
