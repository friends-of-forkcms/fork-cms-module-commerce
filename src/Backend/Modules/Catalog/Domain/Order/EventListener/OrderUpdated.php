<?php

namespace Backend\Modules\Catalog\Domain\Order\EventListener;

use Backend\Modules\Catalog\Domain\Order\Event\OrderUpdated as OrderUpdatedEvent;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory;
use Common\Mailer\Message;
use Common\ModulesSettings;
use Frontend\Core\Language\Language;
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

        $customerMessage = $this->getMessage(
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
    private function getMessage(Order $order, OrderHistory $orderHistory) : Swift_Mime_SimpleMessage {
        $subject = $this->modulesSettings->get('Core', 'site_title_' . LANGUAGE) .
                   ' - '.
                   sprintf(Language::getLabel('UpdateOrderMailSubject'), $order->getId());

        $from = $this->modulesSettings->get('Core', 'mailer_from');

        $message = Message::newInstance($subject)
                          ->parseHtml(
                              '/Catalog/Layout/Templates/Mails/Order/UpdateCustomer.html.twig',
                              [
                                  'order' => $order,
                                  'orderHistory' => $orderHistory
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
        $subject = $this->modulesSettings->get('Core', 'site_title_' . LANGUAGE) .
                   ' - '.
                   sprintf(Language::getLabel('NewOrderMailSubject'), $order->getId());

        $from = $this->modulesSettings->get('Core', 'mailer_from');
        $to = $this->modulesSettings->get('Core', 'mailer_to');

        $message = Message::newInstance($subject)
                          ->parseHtml(
                              '/Catalog/Layout/Templates/Mails/Order/UpdateCompany.html.twig',
                              [
                                  'order' => $order,
                                  'orderHistory' => $orderHistory
                              ],
                              true
                          )
                          ->setTo($to['email'])
                          ->setFrom([$from['email'] => $from['name']]);

        return $message;
    }
}
