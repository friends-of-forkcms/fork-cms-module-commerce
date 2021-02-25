<?php

namespace Backend\Modules\Commerce\Domain\Settings;

use Common\ModulesSettings;
use Symfony\Component\Validator\Constraints as Assert;

class SettingsDataTransferObject
{
    /**
     * @var ModulesSettings
     */
    private $modulesSettings;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Range(
     *      min = "1",
     *      max = "1000"
     * )
     */
    public $overview_num_items;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Range(
     *      min = "1",
     *      max = "1000"
     * )
     */
    public $filters_show_more_num_items;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $next_invoice_number;

    /**
     * @var string
     */
    public $automatic_invoice_statuses;

    /**
     * @var int
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Range(
     *      min = "1",
     *      max = "50"
     * )
     */
    public $products_in_widget;

    /**
     * @var string
     * @Assert\Url
     */
    public $google_product_categories;

    /**
     * @param ModulesSettings $modulesSettings
     */
    public function __construct(ModulesSettings $modulesSettings)
    {
        $this->modulesSettings = $modulesSettings;

        $this->overview_num_items = $this->get('overview_num_items', 10);
        $this->filters_show_more_num_items = $this->get('filters_show_more_num_items', 5);
        $this->next_invoice_number = $this->get('next_invoice_number', 1);
        $this->automatic_invoice_statuses = $this->get('automatic_invoice_statuses', []);
        $this->products_in_widget = $this->get('products_in_widget', 16);
    }

    /**
     * A wrapper for the modules settings
     *
     * @param $key
     * @param $defaultValue
     *
     * @return mixed
     */
    private function get($key, $defaultValue = null)
    {
        return $this->modulesSettings->get('Commerce', $key, $defaultValue);
    }
}
