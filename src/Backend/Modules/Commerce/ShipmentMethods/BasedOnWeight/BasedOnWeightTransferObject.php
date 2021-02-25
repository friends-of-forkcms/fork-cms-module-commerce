<?php

namespace Backend\Modules\Commerce\ShipmentMethods\BasedOnWeight;

use Backend\Modules\Commerce\Domain\Vat\Vat;
use Backend\Modules\Commerce\ShipmentMethods\Base\DataTransferObject;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class BasedOnWeightTransferObject extends DataTransferObject
{
    /**
     * @Assert\Valid
     */
    public $values;

    /**
     * @var Vat
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $vat;

    public function __construct()
    {
        $this->values = new ArrayCollection();
    }
}
