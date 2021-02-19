<?php

namespace Backend\Modules\Catalog\Domain\Account\EventListener;

use Backend\Modules\Catalog\Domain\Account\Account;
use Common\Mailer\Message;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Language\Language as FL;
use Frontend\Modules\Profiles\Engine\Model as FrontendProfilesModel;
use \Swift_Mailer;
use Backend\Modules\Catalog\Domain\Account\Event\Created as AccountCreatedEvent;
use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory;
use Common\ModulesSettings;

final class AccountCreated
{
    /**
     * @var ModulesSettings
     */
    protected $modulesSettings;

    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var OrderHistory $orderHistory
     */
    protected $orderHistory;

    /**
     * OrderListener constructor.
     * @param Swift_Mailer $mailer
     * @param ModulesSettings $modulesSettings
     */
    public function __construct(Swift_Mailer $mailer, ModulesSettings $modulesSettings)
    {
        $this->mailer = $mailer;
        $this->modulesSettings = $modulesSettings;
    }

    public function onCreated(AccountCreatedEvent $event): void
    {
        $account = $event->getAccount();
        $activationUrl = SITE_URL . FrontendNavigation::getUrlForBlock('Profiles', 'Activate');
        $activationKey = $this->getProfileActivationKey($account);

        $from = $this->modulesSettings->get('Core', 'mailer_from');
        $replyTo = $this->modulesSettings->get('Core', 'mailer_reply_to');

        $message = Message::newInstance(FL::getMessage('RegisterSubject'))
            ->setFrom([$from['email'] => $from['name']])
            ->setTo([$account->getEmail() => $account->getFullName()])
            ->setReplyTo([$replyTo['email'] => $replyTo['name']])
            ->parseHtml(
                'Profiles/Layout/Templates/Mails/Register.html.twig',
                ['activationUrl' => $activationUrl . '/' . $activationKey],
                true
            );

        $this->mailer->send($message);
    }

    private function getProfileActivationKey(Account $account): string
    {
        return FrontendProfilesModel::getSetting($account->getProfileId(), 'activation_key');
    }
}
