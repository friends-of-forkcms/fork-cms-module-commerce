<?php

namespace Frontend\Modules\Commerce\CheckoutStep;

use Common\Core\Cookie;
use Common\Uri;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Language\Language;

class OrderPlacedStep extends Step
{
    public static string $stepIdentifier = 'orderPlaced';

    public function init(): void
    {
        $this->setStepName(Language::lbl('Thanks'));
    }

    /**
     * @throws ChangeStepException
     */
    public function execute(): void
    {
        /**
         * @var Cookie $cookie
         */
        $cookie = FrontendModel::get('fork.cookie');
        $cookie->delete('cart_hash', false);

        $this->session->remove('confirm_order');
    }

    public function getUrl(): ?string
    {
        return parent::getUrl().'/'.Uri::getUrl(Language::lbl('Thanks'));
    }
}
