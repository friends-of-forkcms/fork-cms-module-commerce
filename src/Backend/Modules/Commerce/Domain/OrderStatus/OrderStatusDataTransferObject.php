<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus;

use Backend\Core\Language\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class OrderStatusDataTransferObject
{
    protected ?OrderStatus $orderStatusEntity;
    public int $id;
    /** @Assert\NotBlank(message="err.FieldIsRequired") */
    public string $title;
    public ?string $mail_subject;
    public ?string $company_mail_subject;
    public ?string $color;
    public bool $pdf_invoice = false;
    public bool $pdf_packing_slip = false;
    public bool $paid = false;
    public bool $shipped = false;
    public bool $download_invoice = false;
    public bool $send_email = false;
    public bool $send_company_email = false;
    public ?string $template;
    public ?string $company_template;
    public Locale $locale;
    public int $type;

    public function __construct(OrderStatus $orderStatus = null)
    {
        $this->orderStatusEntity = $orderStatus;
        $this->locale = Locale::workingLocale();

        if (!$this->hasExistingOrderStatus()) {
            return;
        }

        $this->id = $this->orderStatusEntity->getId();
        $this->title = $this->orderStatusEntity->getTitle();
        $this->mail_subject = $this->orderStatusEntity->getMailSubject();
        $this->company_mail_subject = $this->orderStatusEntity->getCompanyMailSubject();
        $this->color = $this->orderStatusEntity->getColor();
        $this->locale = $this->orderStatusEntity->getLocale();
        $this->pdf_invoice = $this->orderStatusEntity->isPdfInvoice();
        $this->pdf_packing_slip = $this->orderStatusEntity->isPdfPackingSlip();
        $this->paid = $this->orderStatusEntity->isPaid();
        $this->shipped = $this->orderStatusEntity->isShipped();
        $this->download_invoice = $this->orderStatusEntity->isDownloadInvoice();
        $this->send_email = $this->orderStatusEntity->isSendEmail();
        $this->send_company_email = $this->orderStatusEntity->isSendCompanyEmail();
        $this->template = $this->orderStatusEntity->getTemplate();
        $this->company_template = $this->orderStatusEntity->getCompanyTemplate();
    }

    public function setOrderStatusEntity(OrderStatus $orderStatus): void
    {
        $this->orderStatusEntity = $orderStatus;
    }

    public function getOrderStatusEntity(): OrderStatus
    {
        return $this->orderStatusEntity;
    }

    public function hasExistingOrderStatus(): bool
    {
        return $this->orderStatusEntity instanceof OrderStatus;
    }
}
