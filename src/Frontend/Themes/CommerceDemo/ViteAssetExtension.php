<?php

namespace Frontend\Themes\CommerceDemo;

use Frontend\Core\Engine\Theme;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
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
    private const VITE_CLIENT = '@vite/client';
    private const LEGACY_POLYFILLS = 'vite/legacy-polyfills';

    private ?array $manifestData = null;

    private ClientInterface $httpClient;
    private string $environment;
    private ExceptionListener $exceptionListener;
    private string $basePublicPath;
    private string $manifest;
    private string $devServerPublic;
    private bool $includeReactRefreshShim;
    private bool $includeModulePreloadShim;

    // Possible to override with custom values in config.yml
    public function __construct(
        string $environment,
        ExceptionListener $exceptionListener,
        ?string $basePublicPath = null,
        ?string $manifest = null,
        ?string $devServerPublic = null,
        bool $includeReactRefreshShim = false,
        bool $includeModulePreloadShim = true
    ) {
        if (!(defined('APPLICATION') && APPLICATION === 'Frontend')) {
            return;
        }

        $theme = Theme::getTheme();
        $this->httpClient = new Client();
        $this->environment = $environment;
        $this->exceptionListener = $exceptionListener;

        // optional config args
        $this->basePublicPath = $basePublicPath ?? "/src/Frontend/Themes/$theme/dist/";
        $this->manifest = $manifest ?? PATH_WWW . "/src/Frontend/Themes/$theme/dist/manifest.json";
        $this->devServerPublic = $devServerPublic ?? "http://localhost:3000/src/Frontend/Themes/$theme/";
        $this->includeReactRefreshShim = $includeReactRefreshShim;
        $this->includeModulePreloadShim = $includeModulePreloadShim;
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
        foreach ($css as $cssFile) {
            $html .= <<<HTML
<link href="{$this->basePublicPath}{$cssFile}" rel="stylesheet">
HTML;
        }

        // Render preload links
        foreach ($imports as $importKey) {
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
        $html = '';

        // Include the react-refresh-shim
        if ($this->includeReactRefreshShim) {
            $html .= <<<HTML
<script type="module">
  import RefreshRuntime from '{$this->devServerPublic}@react-refresh'
  RefreshRuntime.injectIntoGlobalHook(window)
  window.\$RefreshReg$ = () => {}
  window.\$RefreshSig$ = () => (type) => type
  window.__vite_plugin_react_preamble_installed__ = true
</script>
HTML;
        }

        // Add the vite client script
        $html .= <<<HTML
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

        // Vite automatically generates <link rel="modulepreload"> directives for entry chunks and their
        // direct imports in the built HTML. Check whether or not the shim for modulepreload-polyfill should
        // be included to polyfill <link rel="modulepreload">
        if ($this->includeModulePreloadShim) {
            $html .= <<<HTML
<script>!function(){const e=document.createElement("link").relList;if(!(e&&e.supports&&e.supports("modulepreload"))){for(const e of document.querySelectorAll('link[rel="modulepreload"]'))r(e);new MutationObserver((e=>{for(const o of e)if("childList"===o.type)for(const e of o.addedNodes)if("LINK"===e.tagName&&"modulepreload"===e.rel)r(e);else if(e.querySelectorAll)for(const o of e.querySelectorAll("link[rel=modulepreload]"))r(o)})).observe(document,{childList:!0,subtree:!0})}function r(e){if(e.ep)return;e.ep=!0;const r=function(e){const r={};return e.integrity&&(r.integrity=e.integrity),e.referrerpolicy&&(r.referrerPolicy=e.referrerpolicy),"use-credentials"===e.crossorigin?r.credentials="include":"anonymous"===e.crossorigin?r.credentials="omit":r.credentials="same-origin",r}(e);fetch(e.href,r)}}();</script>
HTML;
        }

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

    /**
     * Inject the error entry point JavaScript for auto-reloading of Twig error pages
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        if (!isset($this->exceptionListener)) {
            return;
        }

        // Let the normal exceptionlistener handle the event first
        $this->exceptionListener->onKernelException($event);

        // Stop here if Vite dev server is not running
        if (!$this->isDevServerRunning()) {
            return;
        }

        // Grab the response of the exception page
        $content = $event->getResponse()->getContent();
        if (strpos($content, '<!DOCTYPE html>') !== 0) {
            // We only want to edit html responses
            return;
        }

        // Inject the vite client script in the exception response
        $html = <<<HTML
<script type="module" src="{$this->devServerPublic}@vite/client"></script>
HTML;
        $event
            ->getResponse()
            ->setContent(preg_replace('|<head>|', '<head>' . PHP_EOL . $html, $content, 1));
    }
}
