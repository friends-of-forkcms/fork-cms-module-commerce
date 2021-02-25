<?php

namespace Backend\Modules\Commerce\Domain\Quote;

use Symfony\Component\Validator\Constraints as Assert;

class QuoteDataTransferObject
{
    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $first_name;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $last_name;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $phone;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $street;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $house_number;

    /**
     * @var string
     */
    public $house_number_addition;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $city;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $zip_code;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Email(
     *     message = "err.EmailIsRequired",
     *     checkMX = true
     * )
     */
    public $email_address;

    /**
     * @var \DateTime
     */
    public $date;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function getFullName()
    {
        return $this->first_name .' '. $this->last_name;
    }
}
