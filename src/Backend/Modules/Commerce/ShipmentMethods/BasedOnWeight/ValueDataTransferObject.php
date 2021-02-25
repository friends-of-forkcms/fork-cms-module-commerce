<?php

namespace Backend\Modules\Commerce\ShipmentMethods\BasedOnWeight;

use Backend\Modules\Commerce\ShipmentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class ValueDataTransferObject extends DataTransferObject
{
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
    public $price;

    /**
     * @var double
     */
    public $fromWeight;

    /**
     * @var double
     */
    public $tillWeight;


    public function __construct()
    {
    }
}
