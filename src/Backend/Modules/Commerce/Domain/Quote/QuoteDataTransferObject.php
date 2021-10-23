<?php

namespace Backend\Modules\Commerce\Domain\Quote;

use DateTime;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class QuoteDataTransferObject
{
    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $first_name;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $last_name;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $phone;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $street;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $house_number;

    public string $house_number_addition;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $city;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $zip_code;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Email(
     *     message="err.EmailIsRequired",
     *     checkMX=true
     * )
     */
    public string $email_address;

    public DateTimeInterface $date;

    public function __construct()
    {
        $this->date = new DateTime();
    }

    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
