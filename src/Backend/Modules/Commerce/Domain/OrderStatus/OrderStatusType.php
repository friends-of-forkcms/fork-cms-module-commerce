<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus;

use Backend\Core\Engine\Model;
use Common\ModulesSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $emailTemplates = $this->getEmailTemplates();

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'lbl.Title',
            ])
            ->add('mail_subject', TextType::class, [
                'required' => false,
                'label' => 'lbl.MailSubject',
            ])
            ->add('company_mail_subject', TextType::class, [
                'required' => false,
                'label' => 'lbl.CompanyMailSubject',
            ])
            ->add('color', ColorType::class, [
                'required' => false,
                'label' => 'lbl.Color',
            ])
            ->add('template', ChoiceType::class, [
                'required' => false,
                'label' => 'lbl.EmailTemplate',
                'placeholder' => 'lbl.SelectATemplate',
                'choices' => $emailTemplates,
            ])
            ->add('company_template', ChoiceType::class, [
                'required' => false,
                'label' => 'lbl.CompanyEmailTemplate',
                'placeholder' => 'lbl.SelectATemplate',
                'choices' => $emailTemplates,
            ])
            ->add('send_email', CheckboxType::class, [
                'required' => false,
                'label' => 'lbl.SendEmail',
            ])
            ->add('send_company_email', CheckboxType::class, [
                'required' => false,
                'label' => 'lbl.SendCompanyEmail',
            ])
            ->add('pdf_invoice', CheckboxType::class, [
                'required' => false,
                'label' => 'lbl.AttachInvoiceToEmail',
            ])
            ->add('pdf_packing_slip', CheckboxType::class, [
                'required' => false,
                'label' => 'lbl.AttachPackingSlipToEmail',
            ])
            ->add('paid', CheckboxType::class, [
                'required' => false,
                'label' => 'lbl.OrderIsPaid',
            ])
            ->add('shipped', CheckboxType::class, [
                'required' => false,
                'label' => 'lbl.OrderIsShipped',
            ])
            ->add('download_invoice', CheckboxType::class, [
                'required' => false,
                'label' => 'lbl.AllowDownloadInvoice',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => OrderStatusDataTransferObject::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'order_status';
    }

    /**
     * Get the available email templates.
     */
    private function getEmailTemplates(): array
    {
        /**
         * @var ModulesSettings
         */
        $settings = Model::get('fork.settings');
        $theme = $settings->get('Core', 'theme');
        $emailTemplatePath = '/Commerce/Layout/Templates/Mails/Order';

        $folders = [
            FRONTEND_MODULES_PATH . $emailTemplatePath,
            FRONTEND_THEMES_PATH . '/' . $theme . '/' . $emailTemplatePath,
        ];

        $templates = [];

        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                continue;
            }

            $files = scandir($folder);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $templates[$file] = $emailTemplatePath . '/' . $file;
            }
        }

        return $templates;
    }
}
