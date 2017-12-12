<?php

namespace Backend\Modules\Catalog\Domain\ShipmentMethod;

use Common\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_shipment_methods")
 * @ORM\Entity(repositoryClass="ShipmentMethodRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ShipmentMethod
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="name", length=191)
     */
    private $name;

    /**
     * @var Locale
     *
     * @ORM\Column(type="locale", name="language")
     */
    private $locale;

    public function __construct(
        string $name,
        Locale $locale
    ) {
        $this->name = $name;
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }
}
