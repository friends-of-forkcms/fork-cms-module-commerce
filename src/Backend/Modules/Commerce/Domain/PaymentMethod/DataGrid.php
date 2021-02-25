<?php

namespace Backend\Modules\Commerce\Domain\PaymentMethod;

use Backend\Core\Engine\DataGridArray;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridFunctions;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;
use Backend\Modules\Commerce\Domain\Category\Category;
use Symfony\Component\Finder\Finder;

/**
 * @TODO replace with a doctrine implementation of the data grid
 */
class DataGrid extends DataGridArray
{
    public function __construct()
    {
        parent::__construct($this->getPaymentMethods());

        // our JS needs to know an id, so we can highlight it
        $this->setRowAttributes(array('id' => 'row-[id]'));

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
                'data_grid_installed' => ucfirst(Language::lbl('Installed'))
            ]
        );

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('EditPaymentMethod')) {
            $editUrl = Model::createUrlForAction('EditPaymentMethod', null, null, ['id' => '[id]'], false);
            $this->setColumnURL('name', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(): string
    {
        return (new self())->getContent();
    }

    /**
     * Get all the available payment methods
     *
     * @return array
     */
    private function getPaymentMethods(): array
    {
        $installedPaymentMethods = Model::get('commerce.repository.payment_method')->findInstalledPaymentMethods(
            Locale::workingLocale()
        );

        $availablePaymentMethods = [];

        $paymentMethods = $this->getPaymentMethodsOnFilesystem();

        // get more information for each module
        foreach ($paymentMethods as $paymentMethodName) {
            $paymentMethod = [];
            $paymentMethod['id'] = 'payment_method_' . $paymentMethodName;
            $paymentMethod['raw_name'] = $paymentMethodName;
            $paymentMethod['name'] = $paymentMethodName;
            $paymentMethod['description'] = '';
            $paymentMethod['version'] = '';
            $paymentMethod['installed'] = false;

            if (in_array($paymentMethodName, $installedPaymentMethods)) {
                $paymentMethod['installed'] = true;
            }

            try {
                $infoXml = @new \SimpleXMLElement(
                    BACKEND_MODULES_PATH . '/Commerce/PaymentMethods/' . $paymentMethod['raw_name'] . '/Info.xml',
                    LIBXML_NOCDATA,
                    true
                );

                $info = $this->processPaymentMethodXml($infoXml);

                $paymentMethod['name'] = $info['name'];

                // set fields if they were found in the XML
                if (isset($info['description'])) {
                    $paymentMethod['description'] = DataGridFunctions::truncate($info['description'], 80);
                }
                if (isset($info['version'])) {
                    $paymentMethod['version'] = $info['version'];
                }
            } catch (\Exception $e) {
                // don't act upon error, we simply won't possess some info
            }

            $paymentMethod['data_grid_installed'] = $paymentMethod['installed'] ? 'Y' : 'N';

            $availablePaymentMethods[] = $paymentMethod;
        }

        return $availablePaymentMethods;
    }

    /**
     * Get the payment methods from file system
     *
     * @return array
     */
    public static function getPaymentMethodsOnFilesystem(): array
    {
        $paymentMethods = [];

        $finder = new Finder();
        $directories = $finder->directories()->in(BACKEND_MODULES_PATH . '/Commerce/PaymentMethods')->depth('==0');
        foreach ($directories as $directory) {
            // Exclude some directories
            if (!file_exists($directory . '/Info.xml')) {
                continue;
            }

            $paymentMethods[] = $directory->getBasename();
        }

        return $paymentMethods;
    }

    /**
     * Process the payment method XML
     *
     * @param \SimpleXMLElement $xml
     *
     * @return array
     */
    private function processPaymentMethodXml(\SimpleXMLElement $xml): array
    {
        $information = [];

        // fetch theme node
        $module = $xml->xpath('/paymentMethod');
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
