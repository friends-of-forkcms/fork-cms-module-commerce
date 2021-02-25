<?php

namespace Backend\Modules\Commerce\Domain\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'email',
            EmailType::class,
            [
                'required' => true,
                'label' => 'lbl.Email',
            ]
        )->add(
            'password',
            PasswordType::class,
            [
                'required' => true,
                'label' => 'lbl.Password',
            ]
        )->add(
            'remember',
            CheckboxType::class,
            [
                'required' => false,
                'label' => 'lbl.RememberMe',
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'account_login';
    }
}
