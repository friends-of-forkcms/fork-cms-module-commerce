<?php

namespace Backend\Modules\Commerce\Domain\OrderAddress;

use Backend\Modules\Commerce\Domain\Country\Country;
use Backend\Modules\Commerce\Domain\OrderAddress\OrderAddressDataTransferObject;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'company_name',
            TextType::class,
            [
                'required' => false,
                'label' => 'lbl.CompanyName',
                'attr' => [
                    'placeholder' => 'lbl.YourCompanyName',
                ],
            ]
        )->add(
            'first_name',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.FirstName',
                'attr' => [
                    'placeholder' => 'lbl.YourFirstName',
                ],
            ]
        )->add(
            'last_name',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.LastName',
                'attr' => [
                    'placeholder' => 'lbl.YourLastName',
                ],
            ]
        )->add(
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
        $resolver->setDefaults([
            'data_class' => OrderAddressDataTransferObject::class,
        ]);
    }
}
