<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus;

use Backend\Modules\Commerce\Domain\OrderHistory\OrderHistory;
use Common\Locale;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="commerce_order_statuses")
 * @ORM\Entity(repositoryClass="OrderStatusRepository")
 */
class OrderStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="locale", name="language")
     */
    private Locale $locale;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $mailSubject;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $companyMailSubject;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $color;

    /**
     * @ORM\Column(type="boolean", options={"default": "0"})
     */
    private bool $pdfInvoice;

    /**
     * @ORM\Column(type="boolean", options={"default": "0"})
     */
    private bool $pdfPackingSlip;

    /**
     * @ORM\Column(type="boolean", options={"default": "0"})
     */
    private bool $paid;

    /**
     * @ORM\Column(type="boolean", options={"default": "0"})
     */
    private bool $shipped;

    /**
     * @ORM\Column(type="boolean", options={"default": "0"})
     */
    private bool $downloadInvoice;

    /**
     * @ORM\Column(type="boolean", options={"default": "0"})
     */
    private bool $sendEmail;

    /**
     * @ORM\Column(type="boolean", options={"default": "0"})
     */
    private bool $sendCompanyEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $template;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $companyTemplate;

    /**
     * @var Collection|OrderHistory[]
     *
     * @ORM\OneToMany(targetEntity="Backend\Modules\Commerce\Domain\OrderHistory\OrderHistory", mappedBy="orderStatus")
     * @ORM\JoinColumn(name="orderStatusId")
     */
    private Collection $orderHistories;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private DateTimeInterface $updatedAt;

    private function __construct(
        Locale $locale,
        string $title,
        ?string $mailSubject,
        ?string $companyMailSubject,
        ?string $color,
        bool $pdfInvoice,
        bool $pdfPackingSlip,
        bool $paid,
        bool $shipped,
        bool $downloadInvoice,
        bool $sendEmail,
        bool $sendCompanyEmail,
        ?string $template,
        ?string $companyTemplate
    ) {
        $this->locale = $locale;
        $this->title = $title;
        $this->mailSubject = $mailSubject;
        $this->companyMailSubject = $companyMailSubject;
        $this->color = $color;
        $this->pdfInvoice = $pdfInvoice;
        $this->pdfPackingSlip = $pdfPackingSlip;
        $this->paid = $paid;
        $this->shipped = $shipped;
        $this->downloadInvoice = $downloadInvoice;
        $this->sendEmail = $sendEmail;
        $this->sendCompanyEmail = $sendCompanyEmail;
        $this->template = $template;
        $this->companyTemplate = $companyTemplate;
    }

    public static function fromDataTransferObject(OrderStatusDataTransferObject $dataTransferObject): OrderStatus
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

    private static function update(OrderStatusDataTransferObject $dataTransferObject): OrderStatus
    {
        $orderStatus = $dataTransferObject->getOrderStatusEntity();

        $orderStatus->locale = $dataTransferObject->locale;
        $orderStatus->title = $dataTransferObject->title;
        $orderStatus->mailSubject = $dataTransferObject->mail_subject;
        $orderStatus->companyMailSubject = $dataTransferObject->company_mail_subject;
        $orderStatus->color = $dataTransferObject->color;
        $orderStatus->pdfInvoice = $dataTransferObject->pdf_invoice;
        $orderStatus->pdfPackingSlip = $dataTransferObject->pdf_packing_slip;
        $orderStatus->paid = $dataTransferObject->paid;
        $orderStatus->shipped = $dataTransferObject->shipped;
        $orderStatus->downloadInvoice = $dataTransferObject->download_invoice;
        $orderStatus->sendEmail = $dataTransferObject->send_email;
        $orderStatus->sendCompanyEmail = $dataTransferObject->send_company_email;
        $orderStatus->template = $dataTransferObject->template;
        $orderStatus->companyTemplate = $dataTransferObject->company_template;

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

    public function getMailSubject(): ?string
    {
        return $this->mailSubject;
    }

    public function getCompanyMailSubject(): ?string
    {
        return $this->companyMailSubject;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function isPdfInvoice(): bool
    {
        return $this->pdfInvoice;
    }

    public function isPdfPackingSlip(): bool
    {
        return $this->pdfPackingSlip;
    }

    public function isPaid(): bool
    {
        return $this->paid;
    }

    public function isShipped(): bool
    {
        return $this->shipped;
    }

    public function isDownloadInvoice(): bool
    {
        return $this->downloadInvoice;
    }

    public function isSendEmail(): bool
    {
        return $this->sendEmail;
    }

    public function isSendCompanyEmail(): bool
    {
        return $this->sendCompanyEmail;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getCompanyTemplate(): ?string
    {
        return $this->companyTemplate;
    }

    /**
     * @return Collection|OrderHistory[]
     */
    public function getOrderHistories(): Collection
    {
        return $this->orderHistories;
    }
}
