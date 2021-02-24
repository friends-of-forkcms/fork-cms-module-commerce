<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Common\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="commerce_shipment_methods")
 * @ORM\Entity(repositoryClass="ShipmentMethodRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ShipmentMethod
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", name="name", length=191)
     */
    private string $name;

    /**
     * @ORM\Column(type="locale", name="language")
     */
    private Locale $locale;

    public function __construct(
        string $name,
        Locale $locale
    ) {
        $this->name = $name;
        $this->locale = $locale;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }
}
