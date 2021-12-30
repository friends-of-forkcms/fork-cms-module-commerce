<?php

namespace Backend\Modules\Commerce\Domain\ShipmentMethod;

use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridArray;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\Model;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;
use Exception;
use SimpleXMLElement;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridArray
{
    public function __construct(Locale $locale)
    {
        parent::__construct($this->getShipmentMethods($locale));

        $this->setColumnsSequence(['id', 'name', 'description', 'version', 'isEnabled']);
        $this->setColumnsHidden(['module']);

        // our JS needs to know an id, so we can highlight it
        $this->setRowAttributes(['id' => 'row-[id]']);

        // Add some columns
        $this->setColumnFunction(
            [new DataGridFunctions(), 'showBool'],
            ['[isEnabled]'],
            'isEnabled',
            true
        );

        // Overwrite header labels
        $this->setHeaderLabels(
            [
                'isEnabled' => ucfirst(Language::lbl('Enabled')),
            ]
        );

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditShipmentMethod')) {
            $editUrl = Model::createUrlForAction('EditShipmentMethod', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('name', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale): string
    {
        return (new self($locale))->getContent();
    }

    /**
     * First fetch the installed shipment methods from the DB
     * Then read the module's info.xml to display extra information.
     */
    private function getShipmentMethods(Locale $locale): array
    {
        $shipmentMethodsData = BackendModel::get('database')->getRecords(
            '
            SELECT pm.id, pm.name, pm.module, pm.isEnabled
            FROM commerce_shipment_methods AS pm
            WHERE pm.language = :language',
            ['language' => $locale]
        );

        return array_map(function ($shipmentMethod) {
            $moduleDirectory = BACKEND_MODULES_PATH . '/' . $shipmentMethod['module'];
            if (!file_exists("$moduleDirectory/info.xml")) {
                return $shipmentMethod;
            }

            try {
                $infoXml = new SimpleXMLElement("$moduleDirectory/info.xml", LIBXML_NOCDATA, true);
                $information = $infoXml->xpath('/module');
                if (isset($information[0])) {
                    ['description' => $description, 'version' => $version] = (array) $information[0];
                    $shipmentMethod['description'] = trim($description);
                    $shipmentMethod['version'] = trim($version);
                }
            } catch (Exception $e) {
            }

            return $shipmentMethod;
        }, $shipmentMethodsData ?? []);
    }
}
