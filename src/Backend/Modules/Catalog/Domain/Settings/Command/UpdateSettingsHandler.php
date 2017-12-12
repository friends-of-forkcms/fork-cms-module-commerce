<?php

namespace Backend\Modules\Catalog\Domain\Settings\Command;

use Common\ModulesSettings;

final class UpdateSettingsHandler
{
    /**
     * @var ModulesSettings
     */
    private $modulesSettings;

    public function __construct(ModulesSettings $modulesSettings)
    {
        $this->modulesSettings = $modulesSettings;
    }

    public function handle(UpdateSettings $updateSettings): void
    {
        $this->set('overview_num_items', (int) $updateSettings->overview_num_items);
        $this->set('filters_show_more_num_items', (int) $updateSettings->filters_show_more_num_items);
    }

    /**
     * A wrapper to set the modules settings
     *
     * @param $key
     * @param $value
     *
     * @return void
     */
    private function set($key, $value): void
    {
        $this->modulesSettings->set('Catalog', $key, $value);
    }
}
