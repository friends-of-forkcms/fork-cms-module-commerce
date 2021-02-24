<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridArray;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;
use Symfony\Component\Finder\Finder;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridArray
{
    public function __construct()
    {
        parent::__construct($this->getShipmentMethods());

        // our JS needs to know an id, so we can highlight it
        $this->setRowAttributes(['id' => 'row-[id]']);

        // Hide columns
        $this->setColumnHidden('raw_name');
        $this->setColumnHidden('installed');

        // Add some columns
        $this->setColumnFunction(
            [new DataGridFunctions(), 'showBool'],
            ['[data_grid_installed]'],
            'data_grid_installed',
            true
        );

        // Overwrite header labels
        $this->setHeaderLabels(
            [
                'data_grid_installed' => ucfirst(Language::lbl('Installed')),
            ]
        );

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditShipmentMethod')) {
            $editUrl = Model::createUrlForAction('EditShipmentMethod', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('name', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(): string
    {
        return (new self())->getContent();
    }

    /**
     * Get all the available shipment methods.
     */
    private function getShipmentMethods(): array
    {
        $installedShipmentMethods = Model::get('commerce.repository.shipment_method')->findInstalledShipmentMethods(
            Locale::workingLocale()
        );

        $availableShipmentMethods = [];

        $shipmentMethods = $this->getShipmentMethodsOnFilesystem();

        // get more information for each module
        foreach ($shipmentMethods as $shipmentMethodName) {
            $shipmentMethod = [];
            $shipmentMethod['id'] = 'shipment_method_'.$shipmentMethodName;
            $shipmentMethod['raw_name'] = $shipmentMethodName;
            $shipmentMethod['name'] = $shipmentMethodName;
            $shipmentMethod['description'] = '';
            $shipmentMethod['version'] = '';
            $shipmentMethod['installed'] = false;

            if (in_array($shipmentMethodName, $installedShipmentMethods)) {
                $shipmentMethod['installed'] = true;
            }

            try {
                $infoXml = @new \SimpleXMLElement(
                    BACKEND_MODULES_PATH.'/Commerce/ShipmentMethods/'.$shipmentMethod['raw_name'].'/Info.xml',
                    LIBXML_NOCDATA,
                    true
                );

                $info = $this->processShipmentMethodXml($infoXml);

                $shipmentMethod['name'] = $info['name'];

                // set fields if they were found in the XML
                if (isset($info['description'])) {
                    $shipmentMethod['description'] = DataGridFunctions::truncate($info['description'], 80);
                }
                if (isset($info['version'])) {
                    $shipmentMethod['version'] = $info['version'];
                }
            } catch (\Exception $e) {
                // don't act upon error, we simply won't possess some info
            }

            $shipmentMethod['data_grid_installed'] = $shipmentMethod['installed'] ? 'Y' : 'N';

            $availableShipmentMethods[] = $shipmentMethod;
        }

        return $availableShipmentMethods;
    }

    /**
     * Get the shipment methods from file system.
     */
    public static function getShipmentMethodsOnFilesystem(): array
    {
        $shipmentMethods = [];

        $excludedDirectories = [
            'Base',
        ];

        $finder = new Finder();
        $directories = $finder->directories()->in(BACKEND_MODULES_PATH.'/Commerce/ShipmentMethods')->depth('==0');
        foreach ($directories as $directory) {
            // Exclude some directories
            if (in_array($directory->getBasename(), $excludedDirectories)) {
                continue;
            }

            $shipmentMethods[] = $directory->getBasename();
        }

        return $shipmentMethods;
    }

    /**
     * Process the shipment method XML.
     */
    private function processShipmentMethodXml(\SimpleXMLElement $xml): array
    {
        $information = [];

        // fetch theme node
        $module = $xml->xpath('/shipmentMethod');
        if (isset($module[0])) {
            $module = $module[0];
        }

        // fetch general module info
        $information['name'] = (string) $module->name;
        $information['version'] = (string) $module->version;
        $information['description'] = (string) $module->description;

        return $information;
    }
}
