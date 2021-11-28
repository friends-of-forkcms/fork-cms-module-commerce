<?php

namespace Backend\Modules\Commerce\Domain\Order;

use Symfony\Component\Validator\Constraints as Assert;

class ConfirmOrderDataTransferObject
{
    /**
     * @Assert\IsTrue (message="err.FieldIsRequired")
     */
    public bool $accept_terms_and_conditions = false;
}
