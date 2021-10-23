<?php

namespace Backend\Modules\Commerce\Domain\Settings\Command;

use Common\ModulesSettings;
use Symfony\Component\Filesystem\Filesystem;

final class UpdateSettingsHandler
{
    private ModulesSettings $modulesSettings;

    private string $kernelRootDir;

    public function __construct(ModulesSettings $modulesSettings, string $kernelRootDir)
    {
        $this->modulesSettings = $modulesSettings;
        $this->kernelRootDir = $kernelRootDir;
    }

    public function handle(UpdateSettings $updateSettings): void
    {
        $this->set('overview_num_items', (int) $updateSettings->overview_num_items);
        $this->set('filters_show_more_num_items', (int) $updateSettings->filters_show_more_num_items);
        $this->set('next_invoice_number', (int) $updateSettings->next_invoice_number);
        $this->set('automatic_invoice_statuses', $updateSettings->automatic_invoice_statuses);
        $this->set('products_in_widget', $updateSettings->products_in_widget);

        if (!empty($updateSettings->google_product_categories)) {
            $this->loadGoogleMerchantCategories($updateSettings->google_product_categories);
        }
    }

    private function loadGoogleMerchantCategories(?string $url): void
    {
        $contents = file_get_contents($url);
        if (!$contents) {
            return;
        }

        preg_match('/([a-z]{2})-([A-Z]{2})/', $url, $matches);
        if (!$matches) {
            return;
        }
        $googleTaxonomyDir = $this->kernelRootDir . '/../src/Backend/Modules/Commerce/GoogleTaxonomy/' . $matches[1];

        $filesystem = new Filesystem();
        $filesystem->dumpFile($googleTaxonomyDir . '/taxonomies.txt', $contents);
    }

    /**
     * A wrapper to set the modules settings.
     *
     * @param $key
     * @param $value
     */
    private function set($key, $value): void
    {
        $this->modulesSettings->set('Commerce', $key, $value);
    }
}
