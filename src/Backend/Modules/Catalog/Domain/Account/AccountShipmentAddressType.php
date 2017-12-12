<?php

namespace Backend\Modules\Catalog\Domain\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountShipmentAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'first_name',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.FirstName',
            ]
        )->add(
            'last_name',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.LastName',
            ]
        )->add(
            'phone',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.Phone',
            ]
        )->add(
            'street',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.Street',
            ]
        )->add(
            'house_number',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.HouseNumber',
            ]
        )->add(
            'house_number_addition',
            TextType::class,
            [
                'required' => false,
                'label'    => 'lbl.HouseNumberAddition',
            ]
        )->add(
            'city',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.City',
            ]
        )->add(
            'zip_code',
            TextType::class,
            [
                'required' => true,
                'label'    => 'lbl.ZipCode',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => AccountShipmentAddressDataTransferObject::class
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'account_shipment_address';
    }
}
