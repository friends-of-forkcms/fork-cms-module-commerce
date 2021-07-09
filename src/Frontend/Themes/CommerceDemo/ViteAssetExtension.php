<?php

namespace Frontend\Themes\CommerceDemo;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The ViteAssetExtension provides script and link tags based on the Vite build. It's inspired by a few existing scripts.
 * @see: https://grafikart.fr/tutoriels/vitejs-symfony-1895
 * @see: https://sebastiandedeyne.com/vite-with-laravel/
 * @see: https://github.com/lhapaipai/vite-bundle
 * @see: https://nystudio107.com/docs/vite/#legacy
 */
class ViteAssetExtension extends AbstractExtension
{
    private const VITE_CLIENT = '@vite/client.js';
    private const LEGACY_POLYFILLS = 'vite/legacy-polyfills';

    private ?array $manifestData = null;

    private ClientInterface $httpClient;
    private string $basePublicPath;
    private string $manifest;
    private string $devServerPublic;
    private string $environment;

    public function __construct(
        string $basePublicPath,
        string $manifest,
        string $devServerPublic,
        string $environment
    ) {
        $this->httpClient = new Client();
        $this->basePublicPath = $basePublicPath;
        $this->manifest = $manifest;
        $this->devServerPublic = $devServerPublic;
        $this->environment = $environment;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vite_entry_script_tags', [$this, 'renderViteScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('vite_entry_link_tags', [$this, 'renderViteLinkTags'], ['is_safe' => ['html']]),
        ];
    }

    public function isDevServerRunning(): bool
    {
        // Don't expect to have the dev server running when the symfony env is prod
        if ($this->environment === 'prod') {
            return false;
        }

        // Check to see if the dev server is actually running by pinging the vite endpoint
        try {
            $response = $this->httpClient->request('GET', $this->devServerPublic . self::VITE_CLIENT);
            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    public function renderViteLinkTags(string $entryName): string
    {
        // No CSS when dev server is running
        if ($this->isDevServerRunning()) {
            return '';
        }

        // Fetch manifest from file or cache
        $manifest = $this->getManifestData();
        $css = $manifest[$entryName]['css'] ?? [];
        $imports = $manifest[$entryName]['imports'] ?? [];

        // Render the stylesheet tag. It loads asynchronous because we should have critical css set up.
        $html = '';
        foreach($css as $cssFile) {
            $html .= <<<HTML
<link href="{$this->basePublicPath}{$cssFile}" rel="stylesheet" media="print" onload="this.media='all'">
HTML;
        }

        // Render preload links
        foreach($imports as $importKey) {
            $importFile = $manifest[$importKey]['file'];
            $html .= <<<HTML
<link rel="modulepreload" href="{$this->basePublicPath}{$importFile}"/>
HTML;
        }

        return $html;
    }

    public function renderViteScriptTags(string $entryName, array $options = []): string
    {
        if ($this->isDevServerRunning()) {
            return $this->renderScriptsDev($entryName);
        }
        return $this->renderScriptsProd($entryName);
    }

    public function renderScriptsDev(string $entryName): string
    {
        // Add the vite client script
        $html = <<<HTML
<script type="module" src="{$this->devServerPublic}@vite/client"></script>
HTML;

        // Add the entrypoint script
        $html .= <<<HTML
<script type="module" src="{$this->devServerPublic}{$entryName}" defer></script>
HTML;

        return $html;
    }

    public function renderScriptsProd(string $entryName): string
    {
        // Read manifest file
        $manifest = $this->getManifestData();
        $html = '';

        // Detect vite-plugin-legacy
        ['dirname' => $entryDirName, 'filename' => $entryFileName, 'extension' => $entryFileExtension] = pathinfo($entryName);
        $entryFileLegacy = "{$entryDirName}/{$entryFileName}-legacy.{$entryFileExtension}";
        $hasLegacyPluginEnabled = isset($manifest[$entryFileLegacy]['file']);

        if ($hasLegacyPluginEnabled) {
            // Add the Safari 10.1 nomodule fix script
            $html .= <<<HTML
<script>
    !function(){var e=document,t=e.createElement("script");if(!("noModule"in t)&&"onbeforeload"in t){var n=!1;e.addEventListener("beforeload",function(e){if(e.target===t)n=!0;else if(!e.target.hasAttribute("nomodule")||!n)return;e.preventDefault()},!0),t.type="module",t.src=".",e.head.appendChild(t),t.remove()}}();
</script>
HTML;

            // Add the Legacy nomodule polyfill for dynamic imports for older browsers
            $polyfillsLegacy = $manifest[self::LEGACY_POLYFILLS]['file'];
            $html .= <<<HTML
<script type="nomodule" src="{$this->basePublicPath}{$polyfillsLegacy}"></script>
HTML;
        }

        // Add the modern app.js module for modern browsers
        $entryFile = $manifest[$entryName]['file'];
        $html .= <<<HTML
<script type="module" src="{$this->basePublicPath}{$entryFile}" defer crossorigin></script>
HTML;

        // Legacy app.js script for legacy browsers
        if ($hasLegacyPluginEnabled) {
            $html .= <<<HTML
<script type="nomodule" src="{$this->basePublicPath}{$manifest[$entryFileLegacy]['file']}"></script>
HTML;
        }

        return $html;
    }

    private function getManifestData(): array
    {
        if ($this->manifestData === null) {
            try {
                $this->manifestData = json_decode(file_get_contents($this->manifest), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $this->manifestData = [];
            }
        }

        return $this->manifestData;
    }
}
