<?php

namespace Backend\Modules\Catalog\Domain\OrderStatus;

use Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory;
use Common\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="catalog_order_statuses")
 * @ORM\Entity(repositoryClass="OrderStatusRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OrderStatus
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
     * @var Locale
     *
     * @ORM\Column(type="locale", name="language")
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mail_subject;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company_mail_subject;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $pdf_invoice;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $pdf_packing_slip;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $paid;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $shipped;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $download_invoice;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $send_email;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $send_company_email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $template;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company_template;

    /**
     * @var OrderHistory[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Catalog\Domain\OrderHistory\OrderHistory", mappedBy="order_status")
     * @ORM\JoinColumn(name="order_status_id")
     */
    private $order_histories;

    private function __construct(
        Locale $locale,
        string $title,
        ?string $mail_subject,
        ?string $company_mail_subject,
        ?string $color,
        bool $pdf_invoice,
        bool $pdf_packing_slip,
        bool $paid,
        bool $shipped,
        bool $download_invoice,
        bool $send_email,
        bool $send_company_email,
        ?string $template,
        ?string $company_template
    )
    {
        $this->locale = $locale;
        $this->title = $title;
        $this->mail_subject = $mail_subject;
        $this->company_mail_subject = $company_mail_subject;
        $this->color = $color;
        $this->pdf_invoice = $pdf_invoice;
        $this->pdf_packing_slip = $pdf_packing_slip;
        $this->paid = $paid;
        $this->shipped = $shipped;
        $this->download_invoice = $download_invoice;
        $this->send_email = $send_email;
        $this->send_company_email = $send_company_email;
        $this->template = $template;
        $this->company_template = $company_template;
    }

    public static function fromDataTransferObject(OrderStatusDataTransferObject $dataTransferObject)
    {
        if ($dataTransferObject->hasExistingOrderStatus()) {
            return self::update($dataTransferObject);
        }

        return self::create($dataTransferObject);
    }

    private static function create(OrderStatusDataTransferObject $dataTransferObject): self
    {
        return new self(
            $dataTransferObject->locale,
            $dataTransferObject->title,
            $dataTransferObject->mail_subject,
            $dataTransferObject->company_mail_subject,
            $dataTransferObject->color,
            $dataTransferObject->pdf_invoice,
            $dataTransferObject->pdf_packing_slip,
            $dataTransferObject->paid,
            $dataTransferObject->shipped,
            $dataTransferObject->download_invoice,
            $dataTransferObject->send_email,
            $dataTransferObject->send_company_email,
            $dataTransferObject->template,
            $dataTransferObject->company_template
        );
    }

    private static function update(OrderStatusDataTransferObject $dataTransferObject)
    {
        $orderStatus = $dataTransferObject->getOrderStatusEntity();

        $orderStatus->locale = $dataTransferObject->locale;
        $orderStatus->title = $dataTransferObject->title;
        $orderStatus->mail_subject = $dataTransferObject->mail_subject;
        $orderStatus->company_mail_subject = $dataTransferObject->company_mail_subject;
        $orderStatus->color = $dataTransferObject->color;
        $orderStatus->pdf_invoice = $dataTransferObject->pdf_invoice;
        $orderStatus->pdf_packing_slip = $dataTransferObject->pdf_packing_slip;
        $orderStatus->paid = $dataTransferObject->paid;
        $orderStatus->shipped = $dataTransferObject->shipped;
        $orderStatus->download_invoice = $dataTransferObject->download_invoice;
        $orderStatus->send_email = $dataTransferObject->send_email;
        $orderStatus->send_company_email = $dataTransferObject->send_company_email;
        $orderStatus->template = $dataTransferObject->template;
        $orderStatus->company_template = $dataTransferObject->company_template;

        return $orderStatus;
    }

    public function getDataTransferObject(): OrderStatusDataTransferObject
    {
        return new OrderStatusDataTransferObject($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getMailSubject(): ?string
    {
        return $this->mail_subject;
    }

    /**
     * @return string
     */
    public function getCompanyMailSubject(): ?string
    {
        return $this->company_mail_subject;
    }

    /**
     * @return string
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @return bool
     */
    public function isPdfInvoice(): bool
    {
        return $this->pdf_invoice;
    }

    /**
     * @return bool
     */
    public function isPdfPackingSlip(): bool
    {
        return $this->pdf_packing_slip;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->paid;
    }

    /**
     * @return bool
     */
    public function isShipped(): bool
    {
        return $this->shipped;
    }

    /**
     * @return bool
     */
    public function isDownloadInvoice(): bool
    {
        return $this->download_invoice;
    }

    /**
     * @return bool
     */
    public function isSendEmail(): bool
    {
        return $this->send_email;
    }

    /**
     * @return bool
     */
    public function isSendCompanyEmail(): bool
    {
        return $this->send_company_email;
    }

    /**
     * @return string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getCompanyTemplate(): ?string
    {
        return $this->company_template;
    }

    /**
     * @return OrderHistory[]
     */
    public function getOrderHistories()
    {
        return $this->order_histories;
    }
}
