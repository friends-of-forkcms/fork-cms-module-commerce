<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod;

use Common\Locale;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_payment_methods")
 * @ORM\Entity(repositoryClass="PaymentMethodRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PaymentMethod
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    public int $id;

    /**
     * @ORM\Column(type="string", name="name", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", name="module", length=255)
     */
    private string $module;

    /**
     * @ORM\Column(type="locale", name="language")
     */
    private Locale $locale;

    /**
     * @ORM\Column(type="boolean", name="is_enabled", options={"default": false})
     */
    private bool $isEnabled;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
