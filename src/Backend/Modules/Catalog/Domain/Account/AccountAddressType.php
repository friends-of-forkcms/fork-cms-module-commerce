<?php

namespace Backend\Modules\Catalog\Domain\Account;

use Backend\Modules\Catalog\Domain\Country\Country;
use Backend\Modules\Catalog\Domain\OrderAddress\OrderAddressDataTransferObject;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'street',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.Street',
                'attr' => [
                    'placeholder' => 'lbl.YourStreet',
                ],
            ]
        )->add(
            'house_number',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.HouseNumber',
                'attr' => [
                    'placeholder' => 'lbl.YourHouseNumber',
                ],
            ]
        )->add(
            'house_number_addition',
            TextType::class,
            [
                'required' => false,
                'label' => 'lbl.HouseNumberAddition',
                'attr' => [
                    'placeholder' => 'lbl.YourHouseNumberAddition',
                ],
            ]
        )->add(
            'city',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.City',
                'attr' => [
                    'placeholder' => 'lbl.YourCity',
                ],
            ]
        )->add(
            'zip_code',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.ZipCode',
                'attr' => [
                    'placeholder' => 'lbl.YourZipCode',
                ],
            ]
        )->add(
            'country',
            EntityType::class,
            [
                'required' => true,
                'label' => 'lbl.Country',
                'class' => Country::class,
                'choice_label' => 'getName',
                'placeholder' => 'lbl.YourCountryPlaceholder',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => OrderAddressDataTransferObject::class
            ]
        );
    }
}
