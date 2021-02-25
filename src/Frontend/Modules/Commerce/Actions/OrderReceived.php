<?php

namespace Frontend\Modules\Commerce\Actions;

use Common\Core\Cookie;
use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;

/**
 * This is the personal-data-action (default), it will display a personal data form.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class OrderReceived extends FrontendBaseBlock
{
    private string $commerceUrl;

    /**
     * First name of the person that submitted the order.
     */
    private string $firstName;

    public function execute(): void
    {
        parent::execute();

        $this->loadTemplate();
        $this->getData();

        $this->parse();
    }

    private function getData(): void
    {
        /** @var Cookie $cookie */
        $cookie = FrontendModel::getContainer()->get('fork.cookie');

        $this->firstName = $cookie->get('fname', '');
        $this->commerceUrl = FrontendNavigation::getURLForBlock('Commerce');
    }

    protected function parse(): void
    {
        $this->template->assign('commerceUrl', $this->commerceUrl);
        $this->template->assign('firstName', $this->firstName);
    }
}
