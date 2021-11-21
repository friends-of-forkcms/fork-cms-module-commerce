<?php

namespace Backend\Modules\Commerce\Domain\Account\EventListener;

use Backend\Modules\Commerce\Domain\Account\Account;
use Backend\Modules\Commerce\Domain\Account\Event\Created as AccountCreatedEvent;
use Common\Mailer\Message;
use Common\ModulesSettings;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Language\Language as FL;
use Frontend\Modules\Profiles\Engine\Model as FrontendProfilesModel;
use Swift_Mailer;

final class AccountCreated
{
    private ModulesSettings $modulesSettings;
    private Swift_Mailer $mailer;

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
