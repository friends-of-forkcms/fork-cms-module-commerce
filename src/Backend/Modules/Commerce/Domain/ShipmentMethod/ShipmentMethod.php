<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Common\Locale;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdOn;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="edited_on", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $editedOn;

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
