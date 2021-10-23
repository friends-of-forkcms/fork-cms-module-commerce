<?php

namespace Backend\Modules\Commerce\Domain\Order\EventListener;

use Backend\Core\Engine\Model;
use Backend\Modules\Commerce\Domain\Order\Event\OrderGenerateInvoiceNumber;
use Backend\Modules\Commerce\Domain\Order\Mailer\Message;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\OrderHistory\OrderHistory;
use Common\Language;
use Common\ModulesSettings;
use Frontend\Core\Engine\TwigTemplate;
use Frontend\Core\Language\Locale;
use Knp\Snappy\Pdf;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Mime_SimpleMessage;

class OrderListener
{
    protected ModulesSettings $modulesSettings;
    protected Swift_Mailer $mailer;
    protected Order $order;
    protected OrderHistory $orderHistory;

    /**
     * OrderListener constructor.
     */
    public function __construct(Swift_Mailer $mailer, ModulesSettings $modulesSettings)
    {
        $this->mailer = $mailer;
        $this->modulesSettings = $modulesSettings;
    }

    protected function sendCustomerEmail(): void
    {
        if (!$this->orderHistory->getOrderStatus()->isSendEmail()) {
            return;
        }

        $language = $this->orderHistory->getOrderStatus()->getLocale();
        $from = $this->modulesSettings->get('Core', 'mailer_from');

        $subject = $this->modulesSettings->get('Core', 'site_title_' . $language);
        $subject .= ' - ';
        $subject .= $this->orderHistory->getOrderStatus()->getMailSubject();

        $message = $this->getMessage($subject, $this->orderHistory->getOrderStatus()->getTemplate(), true)
            ->setTo($this->order->getAccount()->getEmail())
            ->setFrom([$from['email'] => $from['name']]);

        $this->mailer->send($message);
    }

    protected function sendCompanyEmail(): void
    {
        if (!$this->orderHistory->getOrderStatus()->isSendCompanyEmail()) {
            return;
        }

        $language = $this->orderHistory->getOrderStatus()->getLocale();
        $from = $this->modulesSettings->get('Core', 'mailer_from');
        $to = $this->modulesSettings->get('Core', 'mailer_to');

        $subject = '[' . $this->modulesSettings->get('Core', 'site_title_' . $language) . ']';
        $subject .= ' - ';
        $subject .= $this->orderHistory->getOrderStatus()->getCompanyMailSubject();

        $message = $this->getMessage($subject, $this->orderHistory->getOrderStatus()->getTemplate())
            ->setTo($to['email'])
            ->setFrom([$from['email'] => $from['name']]);

        $this->mailer->send($message);
    }

    protected function getMessage(string $subject, string $template, bool $allowInvoice = false): Swift_Mime_SimpleMessage
    {
        $subject = $this->getSubject($subject);

        $message = Message::newInstance($subject)
            ->parseHtml(
                $this->getTemplatePath($template),
                [
                    'siteTitle' => $this->modulesSettings->get('Core', 'site_title_' . Locale::frontendLanguage()),
                    'SITE_URL' => SITE_URL,
                    'order' => $this->order,
                    'orderHistory' => $this->orderHistory,
                ],
                true
            );

        if ($allowInvoice && $this->orderHistory->getOrderStatus()->isPdfInvoice()) {
            /** @var OrderGenerateInvoiceNumber $orderGenerateInvoiceNumber */
            $orderGenerateInvoiceNumber = Model::get('event_dispatcher')->dispatch(
                OrderGenerateInvoiceNumber::EVENT_NAME,
                new OrderGenerateInvoiceNumber($this->order)
            );

            $filename = Language::lbl('Invoice') . '-' . $orderGenerateInvoiceNumber->getOrder()->getInvoiceNumber() . '.pdf';

            $attachment = new Swift_Attachment(
                $this->generateInvoice($orderGenerateInvoiceNumber->getOrder()),
                $filename,
                'application/pdf'
            );

            $message->attach($attachment);
        }

        return $message;
    }

    /**
     * Replace placeholders with the required values.
     */
    private function getSubject(string $subject): string
    {
        $placeHolders = [
            '%orderId%',
            '%fullName%',
            '%firstName%',
            '%lastName%',
        ];

        $values = [
            $this->order->getId(),
            $this->order->getAccount()->getFullName(),
            $this->order->getAccount()->getFirstName(),
            $this->order->getAccount()->getLastName(),
        ];

        return str_replace($placeHolders, $values, $subject);
    }

    /**
     * Get the template path since the backend won't use the theme path but this is needed
     * when there is a custom template.
     */
    private function getTemplatePath(string $template): string
    {
        $currentTheme = $this->modulesSettings->get('Core', 'theme');
        $themePath = '/Themes/' . $currentTheme . '/Modules';

        if (file_exists(FRONTEND_PATH . $themePath . $template)) {
            return $themePath . $template;
        }

        return $template;
    }

    private function generateInvoice($order): string
    {
        /** @var TwigTemplate $template */
        $template = Model::get('templating');
        $template->assign('order', $order);

        /** @var Pdf $pdf */
        $pdf = Model::get('knp_snappy.pdf');
        $pdf->setOption('viewport-size', '1024x768');

        return $pdf->getOutputFromHtml($template->getContent('Commerce/Layout/Templates/Invoice.html.twig'));
    }
}
