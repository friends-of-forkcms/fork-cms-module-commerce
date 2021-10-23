<?php

namespace Backend\Modules\Commerce\PaymentMethods\Mollie\Checkout;

use Backend\Modules\Commerce\Domain\Order\Event\OrderCreated;
use Backend\Modules\Commerce\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\Commerce\Domain\OrderHistory\Command\CreateOrderHistory;
use Backend\Modules\Commerce\Domain\OrderStatus\Exception\OrderStatusNotFound;
use Backend\Modules\Commerce\PaymentMethods\Base\Checkout\ConfirmOrder as BaseConfirmOrder;
use Backend\Modules\Commerce\PaymentMethods\Exception\PaymentException;
use Doctrine\DBAL\DBALException;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Language\Language;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment;
use PDO;

class ConfirmOrder extends BaseConfirmOrder
{
    /**
     * @var MollieApiClient
     */
    private $mollie;

    private string $currency = 'EUR'; // @TODO change when shop uses multi currency

    /**
     * @throws ApiException
     * @throws PaymentException
     * @throws OrderStatusNotFound
     * @throws DBALException
     * @throws \Common\Exception\RedirectException
     */
    public function prePayment(): void
    {
        $this->mollie = new MollieApiClient();
        $this->mollie->setApiKey($this->getSetting('apiKey'));

        if (!$this->redirectUrl) {
            $this->redirectUrl = '/post-payment?order_id=' . $this->order->getId();
        }

        $payment = $this->getPayment();

        // Update the order history
        $createOrderHistory = new CreateOrderHistory();
        $createOrderHistory->order = $this->order;
        $createOrderHistory->orderStatus = $this->getOrderStatus($this->getSetting('orderInitId'));
        $this->commandBus->handle($createOrderHistory);

        // Trigger an event to notify or not
        $this->eventDispatcher->dispatch(
            OrderCreated::EVENT_NAME,
            new OrderCreated($this->order, $createOrderHistory->getOrderHistoryEntity())
        );

        // Lets collect some money
        $this->redirect($payment->getCheckoutUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function postPayment(): void
    {
        $query = $this->entityManager->getConnection()->prepare(
            'SELECT order_id, method, transaction_id
            FROM commerce_orders_mollie_payments
            WHERE order_id = :order_id'
        );
        $query->bindValue('order_id', $this->order->getId());
        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);

        $mollie = new MollieApiClient();
        $mollie->setApiKey($this->getSetting('apiKey'));

        $payment = $mollie->payments->get($result['transaction_id']);

        $this->paid = $payment->isPaid();
        $this->open = $payment->isOpen();
        $this->expired = $payment->isExpired();
        $this->canceled = $payment->isCanceled();
        $this->failed = $payment->isFailed();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws PaymentException
     */
    private function getPayment(): Payment
    {
        $query = $this->entityManager->getConnection()->prepare(
            'SELECT order_id, method, transaction_id FROM commerce_orders_mollie_payments WHERE order_id = :order_id'
        );
        $query->bindValue('order_id', $this->order->getId());
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $this->updatePayment($result['transaction_id']);
        }

        return $this->createPayment();
    }

    /**
     * Create a new payment.
     *
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws DBALException
     */
    private function createPayment(): Payment
    {
        $baseUrl = SITE_URL . Navigation::getUrlForBlock('Commerce', 'Cart');
        $payment = $this->mollie->payments->create(
            [
                'amount' => [
                    'currency' => $this->currency,
                    'value' => number_format($this->order->getTotal(), 2, '.', ''),
                ],
                'description' => 'Order ' . $this->order->getId(),
                'redirectUrl' => SITE_URL . $this->redirectUrl,
                'webhookUrl' => $baseUrl . '/webhook?payment_method=Mollie.' . $this->option,
                'method' => $this->option,
                'issuer' => $this->data->issuer,
                'metadata' => [
                    'order_id' => $this->order->getId(),
                ],
            ]
        );

        $this->entityManager->getConnection()
            ->insert(
                'commerce_orders_mollie_payments',
                [
                    'order_id' => $this->order->getId(),
                    'method' => $this->option,
                    'transaction_id' => $payment->id,
                ]
            );

        return $payment;
    }

    /**
     * @param $transactionId
     *
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws PaymentException
     */
    private function updatePayment($transactionId): Payment
    {
        $baseUrl = SITE_URL . Navigation::getUrlForBlock('Commerce', 'Cart');

        $payment = $this->mollie->payments->get($transactionId);

        if ($payment->isPaid()) {
            throw new PaymentException(Language::msg('OrderAlreadyExistsPleaseContactUs'));
        }

        $payment->amount = [
            'currency' => $this->currency,
            'value' => number_format($this->order->getTotal(), 2, '.', ''),
        ];
        $payment->description = 'Order ' . $this->order->getId();
        $payment->redirectUrl = SITE_URL . $this->redirectUrl;
        $payment->webhookUrl = $baseUrl . '/webhook?payment_method=Mollie.' . $this->option;
        $payment->method = $this->option;
        $payment->metadata = [
            'order_id' => $this->order->getId(),
        ];

        $payment->update();

        return $payment;
    }

    /**
     * {@inheritdoc}
     */
    public function runWebhook()
    {
        if (!$this->request->request->has('id')) {
            return 'Order not found';
        }

        $mollie = new MollieApiClient();
        $mollie->setApiKey($this->getSetting('apiKey'));

        $payment = $mollie->payments->get($this->request->request->get('id'));

        try {
            $order = $this->getOrder($payment->metadata->order_id);
        } catch (OrderNotFound $e) {
            return 'Order not found';
        }

        if ($payment->isPaid()) {
            $this->updateOrderStatus(
                $order,
                $this->getSetting('orderCompletedId')
            );
        }

        if ($payment->isExpired()) {
            $this->updateOrderStatus(
                $order,
                $this->getSetting('orderExpiredId')
            );
        }

        if ($payment->isCanceled()) {
            $this->updateOrderStatus(
                $order,
                $this->getSetting('orderCancelledId')
            );
        }

        if ($payment->hasRefunds()) {
            $this->updateOrderStatus(
                $order,
                $this->getSetting('orderRefundedId')
            );
        }

        return '';
    }
}
