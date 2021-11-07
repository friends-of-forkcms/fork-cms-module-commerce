<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod;

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
        parent::__construct($this->getPaymentMethods($locale));

        $this->setColumnsSequence(['id', 'name', 'description', 'version', 'is_enabled']);
        $this->setColumnsHidden(['module']);

        // our JS needs to know an id, so we can highlight it
        $this->setRowAttributes(['id' => 'row-[id]']);

        // Add some columns
        $this->setColumnFunction(
            [new DataGridFunctions(), 'showBool'],
            ['[is_enabled]'],
            'is_enabled',
            true
        );

        // Overwrite header labels
        $this->setHeaderLabels(
            [
                'is_enabled' => ucfirst(Language::lbl('Enabled')),
            ]
        );

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditPaymentMethod')) {
            $editUrl = Model::createUrlForAction('EditPaymentMethod', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('name', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale): string
    {
        return (new self($locale))->getContent();
    }

    /**
     * First fetch the installed payment methods from the DB
     * Then read the module's info.xml to display extra information.
     */
    private function getPaymentMethods(Locale $locale): array
    {
        $paymentMethodsData = BackendModel::get('database')->getRecords(
            '
            SELECT pm.id, pm.name, pm.module, pm.is_enabled
            FROM commerce_payment_methods AS pm
            WHERE pm.language = :language',
            ['language' => $locale]
        );

        return array_map(function ($paymentMethod) {
            $moduleDirectory = BACKEND_MODULES_PATH . '/' . $paymentMethod['module'];
            if (!file_exists("$moduleDirectory/info.xml")) {
                return $paymentMethod;
            }

            try {
                $infoXml = new SimpleXMLElement("$moduleDirectory/info.xml", LIBXML_NOCDATA, true);
                $information = $infoXml->xpath('/module');
                if (isset($information[0])) {
                    ['description' => $description, 'version' => $version] = (array) $information[0];
                    $paymentMethod['description'] = trim($description);
                    $paymentMethod['version'] = trim($version);
                }
            } catch (Exception $e) {
            }

            return $paymentMethod;
        }, $paymentMethodsData ?? []);
    }
}
