<?php
declare(strict_types=1);

namespace OneForge\RekAi\Service;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteSettingsFactory;
use TYPO3\CMS\Core\Site\SiteSettingsService;

final class RekAiConfigurationService
{
    private const SETTINGS_KEY = 'one_forge_rekai';

    public function __construct(
        private readonly SiteSettingsService $siteSettingsService,
        private readonly SiteSettingsFactory $siteSettingsFactory,
    ) {}

    public function getConfigurationForSite(Site $site): array
    {
        $settings = $site->getSettings()->get(self::SETTINGS_KEY);
        return array_merge($this->getDefaults(), is_array($settings) ? $settings : []);
    }

    public function isScriptLoadingEnabled(Site $site): bool
    {
        return (bool)($this->getConfigurationForSite($site)['loadScripts'] ?? false);
    }

    public function isNonCssVersion(Site $site): bool
    {
        return (bool)($this->getConfigurationForSite($site)['nonCssVersion'] ?? false);
    }

    public function getConfiguredScriptUrl(Site $site): string
    {
        return trim((string)($this->getConfigurationForSite($site)['scriptUrl'] ?? ''));
    }

    public function isValidScriptUrl(string $scriptUrl): bool
    {
        if ($scriptUrl === '') {
            return false;
        }
        return filter_var($scriptUrl, FILTER_VALIDATE_URL) !== false;
    }

    public function getAutocompleteMode(Site $site): int
    {
        return (int)($this->getConfigurationForSite($site)['autocompleteMode'] ?? 0);
    }

    public function getAutocompleteSelector(Site $site): string
    {
        return trim((string)($this->getConfigurationForSite($site)['autocompleteSelector'] ?? ''));
    }

    public function getAutocompleteCustomScript(Site $site): string
    {
        return (string)($this->getConfigurationForSite($site)['autocompleteCustomScript'] ?? '');
    }

    public function isAutocompleteOpenOnClick(Site $site): bool
    {
        return (bool)($this->getConfigurationForSite($site)['autocompleteOpenOnClick'] ?? false);
    }

    public function isAutocompleteUseCurrentLanguage(Site $site): bool
    {
        return (bool)($this->getConfigurationForSite($site)['autocompleteUseCurrentLanguage'] ?? false);
    }

    public function getAutocompleteNumberOfResults(Site $site): int
    {
        return (int)($this->getConfigurationForSite($site)['autocompleteNumberOfResults'] ?? 5);
    }

    /**
     * Persists the configuration for the given site.
     *
     * Returns true on success. On failure the TYPO3 core already enqueues an
     * error flash message internally; this method returns false so the caller
     * can skip the success message.
     */
    public function saveConfiguration(
        Site   $site,
        bool   $loadScripts,
        string $scriptUrl,
        bool   $nonCssVersion,
        int    $mode,
        string $selector,
        string $customScript,
        bool   $openOnClick,
        bool   $useCurrentLanguage,
        int    $numberOfResults,
    ): bool {
        // Read the actual settings.yaml content (not config.yaml) to preserve other extensions' keys
        $currentSettings = $this->siteSettingsFactory->loadLocalSettings($site->getIdentifier()) ?? [];

        $currentSettings[self::SETTINGS_KEY] = [
            'loadScripts'                    => $loadScripts,
            'scriptUrl'                      => $scriptUrl,
            'nonCssVersion'                  => $nonCssVersion,
            'autocompleteMode'               => $mode,
            'autocompleteSelector'           => $selector,
            'autocompleteCustomScript'       => $customScript,
            'autocompleteOpenOnClick'        => $openOnClick,
            'autocompleteUseCurrentLanguage' => $useCurrentLanguage,
            'autocompleteNumberOfResults'    => $numberOfResults,
        ];

        try {
            $this->siteSettingsService->writeSettings($site, $currentSettings);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function buildAutocompleteInitScript(Site $site): string
    {
        $mode = $this->getAutocompleteMode($site);
        if ($mode === 0) {
            return '';
        }

        $customScript = trim($this->getAutocompleteCustomScript($site));
        if ($mode === 2 && $customScript !== '') {
            return $customScript;
        }

        $selector       = $this->getAutocompleteSelector($site);
        $nrOfHits       = $this->getAutocompleteNumberOfResults($site);
        $useCurrentLang = $this->isAutocompleteUseCurrentLanguage($site);
        $openOnClick    = $this->isAutocompleteOpenOnClick($site);

        $allowedLangs = '';
        if ($useCurrentLang) {
            $allowedLangs = $this->getCurrentLanguageIsoCode();
        }

        $clickHandler = $openOnClick
            ? ".on('rekai_autocomplete:selected', function (event, suggestion, dataset) { window.location = suggestion.url; })"
            : '';

        $paramsParts = ["nrOfHits: {$nrOfHits}"];
        if ($allowedLangs !== '') {
            $paramsParts[] = "allowedlangs: '{$allowedLangs}'";
        }
        $paramsJs = implode(', ', $paramsParts);

        return <<<JS
  __rekai.ready(function () {
    var rekAutocomplete = rekai_autocomplete('{$selector}', {
      params: { {$paramsJs} }
    }){$clickHandler};
  });
JS;
    }

    private function getDefaults(): array
    {
        return [
            'loadScripts'                    => false,
            'scriptUrl'                      => '',
            'nonCssVersion'                  => false,
            'autocompleteMode'               => 0,
            'autocompleteSelector'           => '#searchform-input',
            'autocompleteCustomScript'       => '',
            'autocompleteOpenOnClick'        => true,
            'autocompleteUseCurrentLanguage' => true,
            'autocompleteNumberOfResults'    => 5,
        ];
    }

    private function getCurrentLanguageIsoCode(): string
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $siteLanguage = $request?->getAttribute('language');

        if ($siteLanguage instanceof SiteLanguage) {
            $locale = $siteLanguage->getLocale();
            $languageCode = $locale->getLanguageCode();
            if ($languageCode !== '') {
                return $languageCode;
            }
        }

        return '';
    }
}
