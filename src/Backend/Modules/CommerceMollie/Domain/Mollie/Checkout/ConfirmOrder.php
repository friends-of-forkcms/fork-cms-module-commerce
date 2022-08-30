<?php

namespace Backend\Modules\CommerceMollie\Domain\Mollie\Checkout;

use Backend\Modules\Commerce\Domain\Order\Event\OrderCreated;
use Backend\Modules\Commerce\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\Commerce\Domain\OrderHistory\Command\CreateOrderHistory;
use Backend\Modules\Commerce\Domain\OrderStatus\Exception\OrderStatusNotFound;
use Backend\Modules\Commerce\Domain\PaymentMethod\Checkout\ConfirmOrder as BaseConfirmOrder;
use Backend\Modules\Commerce\Domain\PaymentMethod\Exception\PaymentException;
use Backend\Modules\CommerceMollie\Domain\Payment\Command\CreateMolliePayment;
use Backend\Modules\CommerceMollie\Domain\Payment\MolliePaymentRepository;
use Common\Core\Model;
use Doctrine\DBAL\DBALException;
use Frontend\Core\Engine\Navigation;
use Frontend\Core\Language\Language;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;

class ConfirmOrder extends BaseConfirmOrder
{
    /**
     * @var MollieApiClient
     */
    private $mollie;

    /**
     * @var string
     */
    private $currency = 'EUR'; // @TODO change when shop uses multi currency

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
        $result = $this->getPaymentRepository()->findOneByOrderId($this->order->getId());

        $mollie = new MollieApiClient();
        $mollie->setApiKey($this->getSetting('apiKey'));

        $payment = $mollie->payments->get($result->getTransactionId());

        $this->paid = $payment->isPaid();
        $this->open = $payment->isOpen();
        $this->expired = $payment->isExpired();
        $this->canceled = $payment->isCanceled();
        $this->failed = $payment->isFailed();
    }

    /**
     * @return Payment
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws PaymentException
     */
    private function getPayment(): Payment
    {
        $result = $this->getPaymentRepository()->findOneByOrderId($this->order->getId());

        if ($result) {
            return $this->updatePayment($result->getTransactionId());
        }

        return $this->createPayment();
    }

    /**
     * Create a new payment
     *
     * @return Payment
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws DBALException
     */
    private function createPayment(): Payment
    {
        $moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());

        $baseUrl = SITE_URL . Navigation::getUrlForBlock('Commerce', 'Cart');
        $payment = $this->mollie->payments->create(
            [
                'amount' => [
                    'currency' => $this->currency,
                    'value' => $moneyFormatter->format($this->order->getTotal()),
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

        $createPayment = new CreateMolliePayment();
        $createPayment->order_id = $this->order->getId();
        $createPayment->method = $this->option;
        $createPayment->transaction_id = $payment->id;
        $this->commandBus->handle($createPayment);

        return $payment;
    }

    /**
     * @param $transactionId
     * @return Payment
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws PaymentException
     */
    private function updatePayment($transactionId): Payment
    {
        $moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());
        $baseUrl = SITE_URL . Navigation::getUrlForBlock('Commerce', 'Cart');

        $payment = $this->mollie->payments->get($transactionId);

        if ($payment->isPaid()) {
            throw new PaymentException(Language::msg('OrderAlreadyExistsPleaseContactUs'));
        }

        $payment->amount = [
            'currency' => $this->currency,
            'value' => $moneyFormatter->format($this->order->getTotal()),
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

    private function getPaymentRepository(): MolliePaymentRepository
    {
        return Model::get('commerce_mollie.repository.payment');
    }
}
