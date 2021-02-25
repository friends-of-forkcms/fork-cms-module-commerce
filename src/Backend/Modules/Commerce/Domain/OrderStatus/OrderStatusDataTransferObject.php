<?php

namespace Backend\Modules\Commerce\Domain\OrderStatus;

use Backend\Core\Language\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class OrderStatusDataTransferObject
{
    /**
     * @var OrderStatus
     */
    protected $orderStatusEntity;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $title;

    /**
     * @var string
     */
    public $mail_subject;

    /**
     * @var string
     */
    public $company_mail_subject;

    /**
     * @var string
     */
    public $color;

    /**
     * @var bool
     */
    public $pdf_invoice = false;

    /**
     * @var bool
     */
    public $pdf_packing_slip = false;

    /**
     * @var bool
     */
    public $paid = false;

    /**
     * @var bool
     */
    public $shipped = false;

    /**
     * @var bool
     */
    public $download_invoice = false;

    /**
     * @var bool
     */
    public $send_email = false;

    /**
     * @var bool
     */
    public $send_company_email = false;

    /**
     * @var string
     */
    public $template;

    /**
     * @var string
     */
    public $company_template;

    /**
     * @var Locale
     */
    public $locale;

    /**
     * @var int
     */
    public $type;

    public function __construct(OrderStatus $orderStatus = null)
    {
        $this->orderStatusEntity = $orderStatus;
        $this->locale = Locale::workingLocale();

        if (!$this->hasExistingOrderStatus()) {
            return;
        }

        $this->id = $orderStatus->getId();
        $this->title = $orderStatus->getTitle();
        $this->mail_subject = $orderStatus->getMailSubject();
        $this->company_mail_subject = $orderStatus->getCompanyMailSubject();
        $this->color = $orderStatus->getColor();
        $this->locale = $orderStatus->getLocale();
        $this->pdf_invoice = $orderStatus->isPdfInvoice();
        $this->pdf_packing_slip = $orderStatus->isPdfPackingSlip();
        $this->paid = $orderStatus->isPaid();
        $this->shipped = $orderStatus->isShipped();
        $this->download_invoice = $orderStatus->isDownloadInvoice();
        $this->send_email = $orderStatus->isSendEmail();
        $this->send_company_email = $orderStatus->isSendCompanyEmail();
        $this->template = $orderStatus->getTemplate();
        $this->company_template = $orderStatus->getCompanyTemplate();
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
