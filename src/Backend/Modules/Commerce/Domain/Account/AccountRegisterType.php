<?php

namespace Backend\Modules\Commerce\Domain\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'company_name',
            TextType::class,
            [
                'required' => false,
                'label' => 'lbl.CompanyName',
            ]
        )->add(
            'first_name',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.FirstName',
            ]
        )->add(
            'last_name',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.LastName',
            ]
        )->add(
            'email_address',
            EmailType::class,
            [
                'required' => true,
                'label' => 'lbl.EmailAddress',
            ]
        )->add(
            'phone',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.Phone',
            ]
        )->add(
            'street',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.Street',
            ]
        )->add(
            'house_number',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.HouseNumber',
            ]
        )->add(
            'house_number_addition',
            TextType::class,
            [
                'required' => false,
                'label' => 'lbl.HouseNumberAddition',
            ]
        )->add(
            'city',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.City',
            ]
        )->add(
            'zip_code',
            TextType::class,
            [
                'required' => true,
                'label' => 'lbl.ZipCode',
            ]
        )->add(
            'same_shipping_address',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'lbl.InvoiceAndShipmentAddressAreTheSame',
            ]
        )->add(
            'password',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'required' => true,
                'label' => 'lbl.Password',
                'first_options' => ['label' => 'lbl.Password'],
                'second_options' => ['label' => 'lbl.RepeatPassword'],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => AccountRegisterDataTransferObject::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'account_register';
    }
}
