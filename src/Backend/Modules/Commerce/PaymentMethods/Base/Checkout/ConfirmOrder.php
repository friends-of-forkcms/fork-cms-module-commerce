<?php

namespace Backend\Modules\Commerce\PaymentMethods\Base\Checkout;

use Backend\Modules\Commerce\Domain\Order\Event\OrderUpdated;
use Backend\Modules\Commerce\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\Commerce\Domain\Order\Order;
use Backend\Modules\Commerce\Domain\Order\OrderRepository;
use Backend\Modules\Commerce\Domain\OrderHistory\Command\CreateOrderHistory;
use Backend\Modules\Commerce\Domain\OrderStatus\Exception\OrderStatusNotFound;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Commerce\Domain\OrderStatus\OrderStatusRepository;
use Backend\Modules\Commerce\Domain\PaymentMethod\CheckoutPaymentMethodDataTransferObject;
use Common\Core\Model;
use Common\Exception\RedirectException;
use Common\ModulesSettings;
use Doctrine\ORM\EntityManager;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Language\Language;
use Frontend\Core\Language\Locale;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class ConfirmOrder
{
    protected string $name;

    protected string $option;

    protected Order $order;

    protected CheckoutPaymentMethodDataTransferObject $data;

    protected Request $request;

    protected ?Locale $language;

    protected ModulesSettings $settings;

    protected MessageBusSupportingMiddleware $commandBus;

    protected EventDispatcher $eventDispatcher;

    protected EntityManager $entityManager;

    protected OrderStatusRepository $orderStatusRepository;

    protected OrderRepository $orderRepository;

    protected string $redirectUrl;

    protected bool $paid = false;

    protected bool $open = false;

    protected bool $expired = false;

    protected bool $canceled = false;

    protected bool $failed = false;

    /**
     * Confirm order constructor.
     */
    public function __construct(string $name, string $option)
    {
        $this->name = $name;
        $this->option = $option;
        $this->language = Locale::frontendLanguage();
        $this->settings = Model::get('fork.settings');
        $this->commandBus = Model::get('command_bus');
        $this->eventDispatcher = Model::get('event_dispatcher');
        $this->entityManager = Model::get('doctrine.orm.entity_manager');
        $this->orderRepository = Model::get('commerce.repository.order');
        $this->orderStatusRepository = Model::get('commerce.repository.order_status');
    }

    /**
     * Set the order.
     */
    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    /**
     * Set the payment data.
     */
    public function setData(CheckoutPaymentMethodDataTransferObject $data): void
    {
        $this->data = $data;
    }

    /**
     * Set the request.
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * Get a setting.
     *
     * @param mixed $defaultValue
     * @param bool  $includeLanguage
     *
     * @return mixed
     */
    protected function getSetting(string $key, $defaultValue = null, $includeLanguage = true)
    {
        $baseKey = $this->name;

        if ($includeLanguage) {
            $baseKey .= '_'.$this->language->getLocale();
        }

        return $this->settings->get('Commerce', $baseKey.'_'.$key, $defaultValue);
    }

    public function isPaid(): bool
    {
        return $this->paid;
    }

    public function isOpen(): bool
    {
        return $this->open;
    }

    public function isExpired(): bool
    {
        return $this->expired;
    }

    public function isCanceled(): bool
    {
        return $this->canceled;
    }

    public function isFailed(): bool
    {
        return $this->failed;
    }

    public function setRedirectUrl(string $url): void
    {
        $this->redirectUrl = $url;
    }

    /**
     * Redirect.
     *
     * @throws RedirectException
     */
    protected function redirect(string $url, int $code = RedirectResponse::HTTP_FOUND): void
    {
        throw new RedirectException('Redirect', new RedirectResponse($url, $code));
    }

    /**
     * Go to the 404 page.
     *
     * @throws RedirectException
     */
    protected function goToErrorPage(): void
    {
        $this->redirect(Navigation::getUrl(404));
    }

    /**
     * Go to the store order page.
     *
     * @throws RedirectException
     */
    protected function goToPostPaymentPage(): void
    {
        $this->goToPage('post-payment', ['order_id' => $this->order->getId()]);
    }

    /**
     * Go to the success page.
     *
     * @throws RedirectException
     */
    protected function goToSuccessPage(): void
    {
        $this->goToPage(Language::lbl('PaymentSuccessUrl'));
    }

    /**
     * Go to the cancelled page.
     *
     * @throws RedirectException
     */
    protected function goToCancelledPage(): void
    {
        $this->goToPage(Language::lbl('PaymentCancelledUrl'));
    }

    /**
     * Go to an given cart page.
     *
     * @throws RedirectException
     */
    private function goToPage(string $page, array $parameters = []): void
    {
        $url = Navigation::getUrlForBlock('Commerce', 'Cart').'/'.$page;

        // Add extra parameters
        if ($parameters) {
            $query = [];

            foreach ($parameters as $key => $value) {
                $query[] = $key.'='.$value;
            }

            $url .= '?'.implode('&', $query);
        }

        throw new RedirectException('Redirect', new RedirectResponse($url, RedirectResponse::HTTP_FOUND));
    }

    /**
     * Get the order status based on an id.
     *
     * @param mixed $orderStatusId
     *
     * @throws OrderStatusNotFound
     *
     * @return OrderStatus
     */
    protected function getOrderStatus($orderStatusId): ?OrderStatus
    {
        return $this->orderStatusRepository->findOneByIdAndLocale(
            $orderStatusId,
            $this->language
        );
    }

    /**
     * Get the order based on an id.
     *
     * @param mixed $orderId
     *
     * @throws OrderNotFound
     *
     * @return Order
     */
    protected function getOrder($orderId): ?Order
    {
        return $this->orderRepository->findOneById(
            $orderId
        );
    }

    /**
     * Pre payment action.
     *
     * @throws RedirectException
     */
    abstract public function prePayment(): void;

    /**
     * Pre payment action.
     *
     * @throws RedirectException
     */
    abstract public function postPayment(): void;

    /**
     * Handle a optional webhook call from the payment provider.
     *
     * @return mixed
     */
    public function runWebhook()
    {
        return;
    }

    /**
     * Create a update order status.
     *
     * @throws OrderStatusNotFound
     */
    protected function updateOrderStatus(Order $order, int $statusId): void
    {
        $createOrderHistory = new CreateOrderHistory();
        $createOrderHistory->order = $order;
        $createOrderHistory->orderStatus = $this->getOrderStatus($statusId);
        $this->commandBus->handle($createOrderHistory);

        // Trigger an event to notify or not
        $this->eventDispatcher->dispatch(
            OrderUpdated::EVENT_NAME,
            new OrderUpdated($order, $createOrderHistory->getOrderHistoryEntity())
        );
    }
}
