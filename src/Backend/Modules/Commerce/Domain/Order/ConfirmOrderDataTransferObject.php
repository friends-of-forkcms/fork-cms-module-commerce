<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Symfony\Component\Validator\Constraints as Assert;

class ConfirmOrderDataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $accept_terms_and_conditions;
}
