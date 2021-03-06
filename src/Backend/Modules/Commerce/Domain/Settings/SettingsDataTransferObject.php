<?php

namespace Backend\Modules\Commerce\Domain\Settings;

use Common\ModulesSettings;
use Symfony\Component\Validator\Constraints as Assert;

class SettingsDataTransferObject
{
    private ModulesSettings $modulesSettings;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Range(
     *     min="1",
     *     max="1000"
     * )
     */
    public string $overview_num_items;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Range(
     *     min="1",
     *     max="1000"
     * )
     */
    public string $filters_show_more_num_items;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public string $next_invoice_number;

    public array $automatic_invoice_statuses;

    /**
     * @Assert\NotBlank(message="err.FieldIsRequired")
     * @Assert\Range(
     *     min="1",
     *     max="50"
     * )
     */
    public int $products_in_widget;

    /**
     * @Assert\Url
     */
    public ?string $google_product_categories;

    public function __construct(ModulesSettings $modulesSettings)
    {
        $this->modulesSettings = $modulesSettings;

        $this->overview_num_items = $this->get('overview_num_items', 10);
        $this->filters_show_more_num_items = $this->get('filters_show_more_num_items', 5);
        $this->next_invoice_number = $this->get('next_invoice_number', 1);
        $this->automatic_invoice_statuses = $this->get('automatic_invoice_statuses', []);
        $this->products_in_widget = $this->get('products_in_widget', 16);
        $this->google_product_categories = null;
    }

    /**
     * A wrapper for the modules settings.
     */
    private function get(string $key, $defaultValue = null)
    {
        return $this->modulesSettings->get('Commerce', $key, $defaultValue);
    }
}
