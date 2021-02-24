<?php

namespace Backend\Modules\Commerce\ShipmentMethods\BasedOnWeight;

use Backend\Modules\Commerce\ShipmentMethods\Base\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;

class ValueDataTransferObject extends DataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $name;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $price;
    public float $fromWeight;
    public float $tillWeight;

    public function __construct()
    {
    }
}
