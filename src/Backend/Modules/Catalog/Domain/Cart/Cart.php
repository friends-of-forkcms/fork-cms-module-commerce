<?php

namespace Backend\Modules\Catalog\Domain\Cart;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_carts")
 * @ORM\Entity(repositoryClass="CartRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Cart
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var CartValue
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\Cart\CartValue", mappedBy="cart", cascade={"remove", "persist"})
     */
    private $values;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $session_id;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="created_on")
     */
    private $createdOn;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", name="edited_on")
     */
    private $editedOn;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return CartValue
     */
    public function getValues(): CartValue
    {
        return $this->values;
    }

    /**
     * @param CartValue $values
     */
    public function setValues(CartValue $values)
    {
        $this->values = $values;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->session_id;
    }

    /**
     * @param string $session_id
     */
    public function setSessionId(string $session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * @return DateTime
     */
    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    /**
     * @param DateTime $createdOn
     */
    public function setCreatedOn(DateTime $createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @return DateTime
     */
    public function getEditedOn(): DateTime
    {
        return $this->editedOn;
    }

    /**
     * @param DateTime $editedOn
     */
    public function setEditedOn(DateTime $editedOn)
    {
        $this->editedOn = $editedOn;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->editedOn = new DateTime();

        if (!$this->id) {
            $this->createdOn = $this->editedOn;
        }
    }
}
