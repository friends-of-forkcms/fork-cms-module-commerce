<?php

namespace Backend\Modules\Commerce\PaymentMethods\Buckaroo;

use Backend\Modules\Commerce\PaymentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class BuckarooDataTransferObject extends DataTransferObject
{
    public const PAYMENT_METHODS = [
        [
            'id' => 'ideal',
            'description' => 'iDeal',
        ],
        [
            'id' => 'afterpaydigiaccept',
            'description' => 'Afterpay',
        ],
        [
            'id' => 'mastercard',
            'description' => 'Mastercard',
        ],
        [
            'id' => 'visa',
            'description' => 'Visa',
        ],
        [
            'id' => 'amex',
            'description' => 'American Express',
        ],
        [
            'id' => 'vpay',
            'description' => 'Vpay',
        ],
        [
            'id' => 'bancontactmrcash',
            'description' => 'Bancontact',
        ],
    ];

    public const ENVIRONMENT_TEST = 1;
    public const ENVIRONMENT_PRODUCTION = 2;

    public const TEST_ENDPOINT = 'testcheckout.buckaroo.nl';
    public const PRODUCTION_ENDPOINT = 'checkout.buckaroo.nl';

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $apiEnvironment;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $websiteKey;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $secretKey;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderInitId;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderCompletedId;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderCancelledId;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderRefundedId;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $orderExpiredId;

    public array $paymentMethods = [];

    public function __construct()
    {
        $this->name = 'Buckaroo';
    }

    public function __set($key, $value)
    {
        $this->paymentMethods[$key] = $value;
    }

    public function __get($key)
    {
        return $this->paymentMethods[$key] ?? null;
    }
}
