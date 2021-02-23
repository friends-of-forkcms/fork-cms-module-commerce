<?php

namespace Backend\Modules\Commerce\PaymentMethods\Buckaroo\Checkout;

use Backend\Modules\Commerce\Domain\Order\Event\OrderCreated;
use Backend\Modules\Commerce\Domain\Order\Exception\OrderNotFound;
use Backend\Modules\Commerce\Domain\OrderHistory\Command\CreateOrderHistory;
use Backend\Modules\Commerce\Domain\OrderStatus\Exception\OrderStatusNotFound;
use Backend\Modules\Commerce\PaymentMethods\Base\Checkout\ConfirmOrder as BaseConfirmOrder;
use Backend\Modules\Commerce\PaymentMethods\Buckaroo\BuckarooDataTransferObject;
use Backend\Modules\Commerce\PaymentMethods\Exception\PaymentException;
use Common\Exception\RedirectException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Frontend\Core\Engine\Navigation;
use GuzzleHttp\Client;
use stdClass;

class ConfirmOrder extends BaseConfirmOrder
{
    const IPv4 = 0;
    const IPv6 = 1;

    /**
     * @var string
     */
    private $currency = 'EUR'; // @TODO change when shop uses multi currency

    /**
     * @throws PaymentException
     * @throws OrderStatusNotFound
     * @throws DBALException
     * @throws RedirectException
     */
    public function prePayment(): void
    {
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
        $this->redirect($payment->RequiredAction->RedirectURL);
    }

    /**
     * {@inheritdoc}
     */
    public function postPayment(): void
    {
        $query = $this->entityManager->getConnection()->prepare(
            'SELECT order_id, method, transaction_id
            FROM commerce_orders_buckaroo_payments
            WHERE order_id = :order_id'
        );
        $query->bindValue('order_id', $this->order->getId());
        $query->execute();

        $result = $query->fetch(FetchMode::ASSOCIATIVE);

        $transactionStatus = $this->getTransactionStatus($result['transaction_id']);

        $this->paid = $this->paymentIsPaid($transactionStatus);
        $this->open = $this->paymentIsOpen($transactionStatus);
        $this->expired = $this->paymentIsExpired($transactionStatus);
        $this->canceled = $this->paymentIsCancelled($transactionStatus);
        $this->failed = $this->paymentIsFailed($transactionStatus);
    }

    /**
     * @return StdClass
     * @throws DBALException
     */
    private function getPayment(): StdClass
    {
        $query = $this->entityManager->getConnection()->prepare(
            'SELECT order_id, method, transaction_id FROM commerce_orders_buckaroo_payments WHERE order_id = :order_id'
        );
        $query->execute([
            'order_id' => $this->order->getId(),
        ]);
        $result = $query->fetch(FetchMode::ASSOCIATIVE);

        if ($result) {
            return $this->updatePayment();
        }

        return $this->createPayment();
    }

    /**
     * Create a new payment
     *
     * @param string|null $transactionKey
     * @return StdClass
     * @throws DBALException
     */
    private function createPayment(): StdClass
    {
        $payment = $this->getPaymentResponse($this->getPaymentRequestData());

        $this->entityManager->getConnection()
            ->insert('commerce_orders_buckaroo_payments', [
                'order_id' => $this->order->getId(),
                'method' => $this->option,
                'transaction_id' => $payment->Key,
            ]);

        return $payment;
    }

    /**
     * @return stdClass
     * @throws DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function updatePayment(): stdClass
    {
        $this->entityManager->getConnection()->delete('commerce_orders_buckaroo_payments', [
            'order_id' => $this->order->getId(),
        ]);

        return $this->createPayment();
    }

    /**
     * {@inheritdoc}
     */
    public function runWebhook()
    {
        $requestContent = json_decode($this->request->getContent());

        if (!$this->request->query->has('ADD_OrderId')) {
            throw new PaymentException('Invalid payment data');
        }

        $transactionStatus = $this->getTransactionStatus($requestContent->Transaction->Key);

        try {
            $order = $this->getOrder($this->request->query->get('ADD_OrderId'));
        } catch (OrderNotFound $e) {
            throw new PaymentException('Order not found: '. $this->request->query->get('ADD_OrderId'));
        }

        if ($this->paymentIsPaid($transactionStatus)) {
            $this->updateOrderStatus(
                $order,
                $this->getSetting('orderCompletedId')
            );
        }

        if ($this->paymentIsExpired($transactionStatus)) {
            $this->updateOrderStatus(
                $order,
                $this->getSetting('orderExpiredId')
            );
        }

        if ($this->paymentIsCancelled($transactionStatus) || $this->paymentIsFailed($transactionStatus)) {
            $this->updateOrderStatus(
                $order,
                $this->getSetting('orderCancelledId')
            );
        }

        return '';
    }

    private function paymentIsPaid(stdClass $transactionStatus): bool
    {
        return $this->checkTransactionStatus(190, $transactionStatus);
    }

    private function paymentIsFailed(stdClass $transactionStatus): bool
    {
        return $this->checkTransactionStatus(490, $transactionStatus) ||
            $this->checkTransactionStatus(491, $transactionStatus) ||
            $this->checkTransactionStatus(492, $transactionStatus);
    }

    private function paymentIsExpired(stdClass $transactionStatus): bool
    {
        return $this->checkTransactionStatus(690, $transactionStatus);
    }

    private function paymentIsOpen(stdClass $transactionStatus): bool
    {
        return $this->checkTransactionStatus(790, $transactionStatus) ||
            $this->checkTransactionStatus(791, $transactionStatus) ||
            $this->checkTransactionStatus(792, $transactionStatus);
    }

    private function paymentIsCancelled(stdClass $transactionStatus): bool
    {
        return $this->checkTransactionStatus(890, $transactionStatus) ||
            $this->checkTransactionStatus(891, $transactionStatus);
    }

    private function checkTransactionStatus(int $code, stdClass $transactionStatus): bool
    {
        return $code == $transactionStatus->Status->Code->Code;
    }

    /**
     * @param array $requestData
     * @return stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getPaymentResponse(array $requestData): stdClass
    {
        return $this->getApiRequest('Transaction', $requestData);
    }

    /**
     * @param string $transactionKey
     * @return stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getTransactionStatus(string $transactionKey)
    {
        return $this->getApiRequest('Transaction/Status/' . $transactionKey, null, 'get');
    }

    /**
     * @return array
     */
    private function getPaymentRequestData(): array
    {
        $baseUrl = SITE_URL . Navigation::getUrlForBlock('Commerce', 'Cart');

        return [
            'Currency' => $this->currency,
            'AmountDebit' => $this->order->getTotal(),
            'Description' => 'Order ' . $this->order->getId(),
            'Invoice' => $this->order->getId(),
            'Order' => $this->order->getId(),
            'ClientIP' => [
                'Type' => self::IPv4,
                'Address' => $_SERVER['REMOTE_ADDR'],
            ],
            'ContinueOnIncomplete' => true, // Payment gateway
            'ReturnURL' => SITE_URL . $this->redirectUrl,
            'ReturnURLCancel' => SITE_URL . $this->redirectUrl,
            'ReturnURLError' => SITE_URL . $this->redirectUrl,
            'ReturnURLReject' => SITE_URL . $this->redirectUrl,
            'PushURL' => $baseUrl . '/webhook?payment_method=Buckaroo.' . $this->option.'&ADD_OrderId=' . $this->order->getId(),
            'PushURLFailure' => $baseUrl . '/webhook?payment_method=Buckaroo.' . $this->option,
            'Services' => [
                'ServiceList' => [
                    [
                        'Name' => $this->option,
                    ],
                ],
            ],
            'AdditionalParameters' => [
                'AdditionalParameter' => [
                    [
                        'Name' => 'OrderId',
                        'Value' => $this->order->getId(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $action
     * @param array $requestData
     * @param string $method
     * @return stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getApiRequest(string $action, array $requestData = null, $method = 'post'): stdClass
    {
        $client = new Client([
            'base_uri' => $this->getEndpointUrl() . '/json/',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $hmac = $this->getHmac($action, $requestData);

        $response = $client->request($method, $action, [
            'json' => $requestData,
            'headers' => [
                'Authorization' => $hmac,
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    private function getHmac(string $requestUri, array $requestData = null): string
    {
        $websiteKey = $this->getSetting('websiteKey');
        $secretKey = $this->getSetting('secretKey');
        $uri = strtolower(urlencode($this->getEndpointUrl(false) . '/json/' . $requestUri));
        $nonce = 'nonce_' . rand(0000000, 9999999);
        $time = time();
        $encodedData = null;
        $requestMethod = 'GET';

        // When request data is set, it should be a post
        if ($requestData) {
            $encodedData = base64_encode(md5(json_encode($requestData), true));
            $requestMethod = 'POST';
        }

        $hmac = $websiteKey . $requestMethod . $uri . $time . $nonce . $encodedData;
        $s = hash_hmac('sha256', $hmac, $secretKey, true);
        $hmac = base64_encode($s);

        return 'hmac ' . $websiteKey . ':' . $hmac . ':' . $nonce . ':' . $time;
    }

    private function getEndpointUrl($withProtocol = true, $protocol = 'https'): string
    {
        $url = BuckarooDataTransferObject::PRODUCTION_ENDPOINT;

        if ($this->getSetting('apiEnvironment') == BuckarooDataTransferObject::ENVIRONMENT_TEST) {
            $url = BuckarooDataTransferObject::TEST_ENDPOINT;
        }

        if ($withProtocol) {
            $url = $protocol . '://' . $url;
        }

        return $url;
    }
}
