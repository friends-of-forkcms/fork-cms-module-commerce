<?php

namespace Backend\Modules\Commerce\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityTypeExtension extends AbstractType
{
    public function getParent()
    {
        return EntityType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['help'] = $options['help'] ?? '';
        $view->vars['allow_custom_value'] = $options['allow_custom_value'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'help' => null,
            'allow_custom_value' => false,
        ]);
    }
}
