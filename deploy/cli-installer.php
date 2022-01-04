<?php

// bootstrap Fork
require __DIR__ . '/../autoload.php';

use ForkCMS\App\AppKernel;
use ForkCMS\App\KernelLoader;
use ForkCMS\Bundle\InstallerBundle\Service\ForkInstaller;
use ForkCMS\Bundle\InstallerBundle\Entity\InstallationData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * A Fork CMS installer that can be run from the command line, using an associative array
 * holding the settings that would normally be filled in via the web installer form.
 */
class CliInstaller {
    private array $config;

    private const DEMO_ADMIN_USERNAME = 'demo@fork-cms.com';
    private const DEMO_ADMIN_PASSWORD = 'demo';
    private const AVAILABLE_LANGUAGES = ['en', 'nl'];

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function run(): void
    {
        if (!defined('PATH_WWW')) {
            define('PATH_WWW', $this->config['project_root']);
        }
        if (!defined('SPOON_CHARSET')) {
            define('SPOON_CHARSET', 'UTF-8');
        }
        if (!defined('BACKEND_CACHE_PATH')) {
            define('BACKEND_CACHE_PATH', PATH_WWW . 'src/Backend/Cache/');
        }
        if (!defined('FRONTEND_CACHE_PATH')) {
            define('FRONTEND_CACHE_PATH', PATH_WWW . 'src/Frontend/Cache/');
        }

        // installation data
        $forkData = new InstallationData();

        // database info
        $forkData->setDatabaseHostname($this->config['db_host']);
        $forkData->setDatabasePort($this->config['db_port']);
        $forkData->setDatabaseUsername($this->config['db_user']);
        $forkData->setDatabasePassword($this->config['db_pass']);
        $forkData->setDatabaseName($this->config['db']);

        // language settings
        $forkData->setLanguageType('multiple');
        $forkData->setSameInterfaceLanguage(true);
        $forkData->setLanguages($this->getLanguages());
        $forkData->setInterfaceLanguages($this->getLanguages());
        $forkData->setDefaultLanguage('en');
        $forkData->setDefaultInterfaceLanguage('en');

        // data settings
        $forkData->setModules($this->getModules());
        $forkData->setExampleData(false);
        $forkData->setDifferentDebugEmail(false);
        $forkData->setDebugEmail('');

        // login settings
        $forkData->setEmail(self::DEMO_ADMIN_USERNAME);
        $forkData->setPassword(self::DEMO_ADMIN_PASSWORD);

        // create the kernel so the database config is loaded
        $kernel = $this->getKernel();
        $session = new Session();
        $session->set('installation_data', $forkData);

        // reload the kernel because the container needs to be rebuilt
        $kernel = $this->getKernel();

        // install it
        $forkInstaller = new ForkInstaller($kernel->getContainer());
        $session = new Session();
        $forkInstaller->install($session->get('installation_data'));
    }

    protected function getKernel(): AppKernel
    {
        $kernel = new AppKernel('install', true);
        $kernel->boot();
        $loader = new KernelLoader($kernel);
        $loader->passContainerToModels();

        return $kernel;
    }

    /**
     * Returns the array of modules that should be installed
     */
    protected function getModules(): array
    {
        // init modules list
        return array_merge(
            ForkInstaller::getRequiredModules(),
            ForkInstaller::getHiddenModules()
        );
    }

    protected function getLanguages(): array
    {
        return self::AVAILABLE_LANGUAGES;
    }

    public static function usage(): string
    {
        return 'Usage: $ php install.php [project_root] [db_host]?| [db_port]?| [db_user]?| [db_pass]?| [db] [site_domain]';
    }
}

$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];

// check for valid usage
if ($argc !== 8) {
    exit(CliInstaller::usage() . "\n");
}

// create config
$config = [];
$config['project_root'] = $argv[1];
$config['db_host'] = $argv[2];
$config['db_port'] = $argv[3];
$config['db_user'] = $argv[4];
$config['db_pass'] = $argv[5];
$config['db'] = $argv[6];
$config['site_domain'] = $argv[7];

// run fake installer
$installer = new CliInstaller($config);
$installer->run();
