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
        $builder
            ->add('company_name', TextType::class, [
                'required' => false,
                'label' => 'lbl.CompanyName',
            ])
            ->add(TextType::class, 'first_name', [
                'required' => true,
                'label' => 'lbl.FirstName',
            ])
            ->add(TextType::class, 'last_name', [
                'required' => true,
                'label' => 'lbl.LastName',
            ])
            ->add(EmailType::class, 'email_address', [
                'required' => true,
                'label' => 'lbl.EmailAddress',
            ])
            ->add(TextType::class, 'phone', [
                'required' => true,
                'label' => 'lbl.Phone',
            ])
            ->add(TextType::class, 'street', [
                'required' => true,
                'label' => 'lbl.Street',
            ])
            ->add(TextType::class, 'house_number', [
                'required' => true,
                'label' => 'lbl.HouseNumber',
            ])
            ->add(TextType::class, 'house_number_addition', [
                'required' => false,
                'label' => 'lbl.HouseNumberAddition',
            ])
            ->add(TextType::class, 'city', [
                'required' => true,
                'label' => 'lbl.City',
            ])
            ->add(TextType::class, 'zip_code', [
                'required' => true,
                'label' => 'lbl.ZipCode',
            ])
            ->add(CheckboxType::class, 'same_shipping_address', [
                'required' => false,
                'label' => 'lbl.InvoiceAndShipmentAddressAreTheSame',
            ])
            ->add(RepeatedType::class, 'password', [
                'type' => PasswordType::class,
                'required' => true,
                'label' => 'lbl.Password',
                'first_options' => ['label' => 'lbl.Password'],
                'second_options' => ['label' => 'lbl.RepeatPassword'],
            ]);
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
