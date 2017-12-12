<?php

namespace Backend\Modules\Catalog\PaymentMethods\Mollie\Checkout;

use Backend\Modules\Catalog\PaymentMethods\Base\Checkout\Options as BaseOptions;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;

class Options extends BaseOptions
{
    /**
     * {@inheritdoc}
     */
    public function addFields(FormInterface $form): void
    {
        $mollie = new \Mollie_API_Client();
        $mollie->setApiKey($this->getSetting('apiKey'));

        // Get all the issuers
        $issuers = [];
        foreach ($mollie->issuers->all() as $issuer) {
            $issuers[$issuer->name] = $issuer->id;
        }

        $form->add(
            'issuer',
            ChoiceType::class,
            [
                'required' => true,
                'label'    => 'lbl.ChooseYourBank',
                'choices'  => $issuers
            ]
        );
    }
}
