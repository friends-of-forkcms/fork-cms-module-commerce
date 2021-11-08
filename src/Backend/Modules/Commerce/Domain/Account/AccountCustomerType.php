<?php

namespace Backend\Modules\Commerce\Domain\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountCustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('company_name', TextType::class, [
                'required' => false,
                'label' => 'lbl.CompanyName',
                'attr' => [
                    'placeholder' => 'lbl.YourCompanyName',
                ],
            ])
            ->add('first_name', TextType::class, [
                'required' => true,
                'label' => 'lbl.FirstName',
                'attr' => [
                    'placeholder' => 'lbl.YourFirstName',
                ],
            ])
            ->add('last_name', TextType::class, [
                'required' => true,
                'label' => 'lbl.LastName',
                'attr' => [
                    'placeholder' => 'lbl.YourLastName',
                ],
            ])
            ->add('email_address', EmailType::class, [
                'required' => true,
                'label' => 'lbl.EmailAddress',
                'attr' => [
                    'placeholder' => 'lbl.YourEmailAddress',
                ],
            ])
            ->add('phone', TextType::class, [
                'required' => true,
                'label' => 'lbl.Phone',
                'attr' => [
                    'placeholder' => 'lbl.YourPhoneNumber',
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'err.NotMatchingPasswords',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => $options['password_required'],
                'label' => 'lbl.Password',
                'first_options' => [
                    'label' => 'lbl.Password',
                    'attr' => [
                        'placeholder' => 'lbl.YourPassword',
                    ],
                ],
                'second_options' => [
                    'label' => 'lbl.RepeatPassword',
                    'attr' => [
                        'placeholder' => 'lbl.RepeatYourPassword',
                    ],
                ],
            ])
            ->add('shipment_address', AccountAddressType::class);

        if ($options['add_invoice_address']) {
            $builder
                ->add('invoice_address', AccountAddressType::class)
                ->add('same_invoice_address', CheckboxType::class, [
                    'required' => false,
                    'label' => 'lbl.SameInvoiceAddress',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AccountCustomerDataTransferObject::class,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                $groups = ['Default'];

                if (!$data->same_invoice_address) {
                    $groups[] = 'DifferentInvoiceAddress';
                }

                return $groups;
            },
            'add_invoice_address' => true,
            'password_required' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'account_customer';
    }
}
