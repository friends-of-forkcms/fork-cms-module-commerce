<?php

namespace Frontend\Modules\Catalog\CheckoutStep;

use Backend\Modules\Catalog\Domain\Order\Command\CreateOrder;
use Backend\Modules\Catalog\Domain\Order\Command\UpdateOrder;
use Backend\Modules\Catalog\Domain\Order\Order;
use Backend\Modules\Catalog\Domain\OrderProduct\Command\CreateOrderProduct;
use Backend\Modules\Catalog\Domain\OrderProductOption\OrderProductOption;
use Backend\Modules\Catalog\Domain\OrderVat\Command\CreateOrderVat;
use Backend\Modules\Catalog\Domain\PaymentMethod\CheckoutPaymentMethodDataTransferObject;
use Backend\Modules\Catalog\Domain\Vat\Vat;
use Backend\Modules\Catalog\PaymentMethods\Base\Checkout\ConfirmOrder;
use Common\Core\Cookie;
use Common\Exception\RedirectException;
use Common\Uri;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;

class OrderPlaced extends Step
{
    public static $stepIdentifier = 'orderPlaced';

    public function init()
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
        return parent::getUrl() .'/'.  Uri::getUrl(Language::lbl('Thanks'));
    }
}
