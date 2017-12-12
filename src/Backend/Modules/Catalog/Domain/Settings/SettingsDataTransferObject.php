<?php

namespace Backend\Modules\Catalog\Domain\Settings;

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

    public function __construct(ModulesSettings $modulesSettings)
    {
        $this->modulesSettings = $modulesSettings;

        $this->overview_num_items = $this->get('overview_num_items', 10);
        $this->filters_show_more_num_items = $this->get('filters_show_more_num_items', 5);
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
        return $this->modulesSettings->get('Catalog', $key, $defaultValue);
    }
}
