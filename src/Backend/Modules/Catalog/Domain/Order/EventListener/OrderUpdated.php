<?php

namespace Backend\Modules\Catalog\Domain\Order\EventListener;

use Backend\Modules\Catalog\Domain\Order\Event\OrderUpdated as OrderUpdatedEvent;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory;
use Common\Language;
use Common\Mailer\Message;
use Common\ModulesSettings;
use Swift_Mailer;
use Swift_Mime_SimpleMessage;

final class OrderUpdated
{
    /**
     * @var ModulesSettings
     */
    protected $modulesSettings;

    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    public function __construct(
        Swift_Mailer $mailer,
        ModulesSettings $modulesSettings
    ) {
        $this->mailer = $mailer;
        $this->modulesSettings = $modulesSettings;
    }

    public function onOrderUpdated(OrderUpdatedEvent $event): void
    {
        // Skip when we don't need to notify anyone
        if (!$event->getOrderHistory()->isNotify()) {
            return;
        }

        $customerMessage = $this->getCustomerMessage(
            $event->getOrder(),
            $event->getOrderHistory()
        );

        $companyMessage = $this->getCompanyMessage(
            $event->getOrder(),
            $event->getOrderHistory()
        );

        $this->mailer->send($customerMessage);
        $this->mailer->send($companyMessage);
    }

    /**
     * @param Order $order
     * @param OrderHistory $orderHistory
     *
     * @return Swift_Mime_SimpleMessage
     */
    private function getCustomerMessage(Order $order, OrderHistory $orderHistory) : Swift_Mime_SimpleMessage {
        $language = $orderHistory->getOrderStatus()->getLocale();
        $siteTitle = $this->modulesSettings->get('Core', 'site_title_' . $language);

        $subject = $siteTitle . ' - '.
                   sprintf(Language::lbl('UpdateOrderMailSubject'), $order->getId());

        $from = $this->modulesSettings->get('Core', 'mailer_from');

        $message = Message::newInstance($subject)
                          ->parseHtml(
                              $this->getTemplatePath('/Catalog/Layout/Templates/Mails/Order/UpdateCustomer.html.twig'),
                              [
                                  'order' => $order,
                                  'orderHistory' => $orderHistory,
                                  'siteTitle' => $siteTitle,
                              ],
                              true
                          )
                          ->setTo($order->getInvoiceAddress()->getEmailAddress())
                          ->setFrom([$from['email'] => $from['name']]);

        return $message;
    }

    /**
     * @param Order $order
     * @param OrderHistory $orderHistory
     *
     * @return Swift_Mime_SimpleMessage
     */
    private function getCompanyMessage(Order $order, OrderHistory $orderHistory) : Swift_Mime_SimpleMessage {
        $language = $orderHistory->getOrderStatus()->getLocale();
        $siteTitle = $this->modulesSettings->get('Core', 'site_title_' . $language);

        $subject =  $siteTitle. ' - '.
                   sprintf(Language::lbl('UpdateOrderMailSubject'), $order->getId());

        $from = $this->modulesSettings->get('Core', 'mailer_from');
        $to = $this->modulesSettings->get('Core', 'mailer_to');

        $message = Message::newInstance($subject)
                          ->parseHtml(
                              $this->getTemplatePath('/Catalog/Layout/Templates/Mails/Order/UpdateCompany.html.twig'),
                              [
                                  'order' => $order,
                                  'orderHistory' => $orderHistory,
                                  'siteTitle' => $siteTitle,
                              ],
                              true
                          )
                          ->setTo($to['email'])
                          ->setFrom([$from['email'] => $from['name']]);

        return $message;
    }

    /**
     * Get the template path since the backend won't use the theme path but this is needed
     * when there is a custom template
     *
     * @param string $template
     *
     * @return string
     */
    private function getTemplatePath(string $template): string
    {
        $currentTheme = $this->modulesSettings->get('Core', 'theme');
        $themePath = '/Themes/' . $currentTheme .'/Modules';

        if (file_exists(FRONTEND_PATH . $themePath . $template)) {
            return $themePath . $template;
        }

        return $template;
    }
}
