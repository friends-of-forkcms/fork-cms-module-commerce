<?php

namespace Backend\Modules\Catalog\PaymentMethods\Buckaroo;

use Backend\Modules\Catalog\Domain\OrderStatus\OrderStatus;
use Backend\Modules\Catalog\PaymentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class BuckarooDataTransferObject extends DataTransferObject
{
    const PAYMENT_METHODS = [
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

    const ENVIRONMENT_TEST = 1;
    const ENVIRONMENT_PRODUCTION = 2;

    const TEST_ENDPOINT = 'testcheckout.buckaroo.nl';
    const PRODUCTION_ENDPOINT = 'checkout.buckaroo.nl';

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $name;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $apiEnvironment;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $websiteKey;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $secretKey;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderInitId;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderCompletedId;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderCancelledId;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderRefundedId;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $orderExpiredId;

    public $paymentMethods = [];

    public function __set($key, $value) {
        $this->paymentMethods[$key] = $value;
    }

    public function __get($key)
    {
        if (!isset($this->paymentMethods[$key])) {
            return null;
        }

        return $this->paymentMethods[$key];
    }
}
