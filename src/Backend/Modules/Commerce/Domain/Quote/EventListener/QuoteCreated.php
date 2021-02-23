<?php

namespace Backend\Modules\Commerce\Domain\Quote\EventListener;

use Backend\Modules\Commerce\Domain\Cart\Cart;
use Backend\Modules\Commerce\Domain\Quote\Event\QuoteCreated as QuoteCreatedEvent;
use Backend\Modules\Commerce\Domain\Quote\QuoteDataTransferObject;
use Common\Mailer\Message;
use Common\ModulesSettings;
use Frontend\Core\Language\Language;
use Swift_Mailer;
use Swift_Mime_SimpleMessage;

final class QuoteCreated
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

    public function onQuoteCreated(QuoteCreatedEvent $event): void
    {
        $customerMessage = $this->getCustomerMessage(
            $event->getQuote(),
            $event->getCart()
        );

        $companyMessage = $this->getCompanyMessage(
            $event->getQuote(),
            $event->getCart()
        );

        $this->mailer->send($customerMessage);
        $this->mailer->send($companyMessage);
    }

    /**
     * @param QuoteDataTransferObject $quote
     * @param Cart $cart
     *
     * @return Swift_Mime_SimpleMessage
     */
    private function getCustomerMessage(QuoteDataTransferObject $quote, Cart $cart) : Swift_Mime_SimpleMessage
    {
        $subject = $this->modulesSettings->get('Core', 'site_title_' . LANGUAGE) .
                   ' - '.
                   Language::getLabel('YourQuoteRequestMailSubject');

        $from = $this->modulesSettings->get('Core', 'mailer_from');

        $message = Message::newInstance($subject)
                          ->parseHtml(
                              '/Commerce/Layout/Templates/Mails/Quote/ConfirmationCustomer.html.twig',
                              [
                                  'quote' => $quote,
                                  'cart' => $cart,
                              ],
                              true
                          )
                          ->setTo($quote->email_address)
                          ->setFrom([$from['email'] => $from['name']]);

        return $message;
    }

    /**
     * @param QuoteDataTransferObject $quote
     * @param Cart $cart
     *
     * @return Swift_Mime_SimpleMessage
     */
    private function getCompanyMessage(QuoteDataTransferObject $quote, Cart $cart) : Swift_Mime_SimpleMessage
    {
        $subject = $this->modulesSettings->get('Core', 'site_title_' . LANGUAGE) .
                   ' - '.
                   Language::getLabel('NewQuoteRequestMailSubject');

        $from = $this->modulesSettings->get('Core', 'mailer_from');
        $to = $this->modulesSettings->get('Core', 'mailer_to');

        $message = Message::newInstance($subject)
                          ->parseHtml(
                              '/Commerce/Layout/Templates/Mails/Quote/ConfirmationCompany.html.twig',
                              [
                                  'quote' => $quote,
                                  'cart' => $cart,
                              ],
                              true
                          )
                          ->setTo($to['email'])
                          ->setReplyTo([$quote->email_address => $quote->getFullName()])
                          ->setFrom([$from['email'] => $from['name']]);

        return $message;
    }
}
