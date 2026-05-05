<?php
declare(strict_types=1);

namespace OneForge\RekAi\Tests\Unit\Service;

use OneForge\RekAi\Service\RekAiConfigurationService;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteSettingsFactory;
use TYPO3\CMS\Core\Site\SiteSettingsService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RekAiConfigurationServiceTest extends UnitTestCase
{
    // ── isValidScriptUrl ─────────────────────────────────────────────────────

    #[Test]
    public function isValidScriptUrlReturnsFalseForEmptyString(): void
    {
        $service = $this->makeService([]);
        self::assertFalse($service->isValidScriptUrl(''));
    }

    #[Test]
    public function isValidScriptUrlReturnsFalseForUrlWithoutScheme(): void
    {
        $service = $this->makeService([]);
        self::assertFalse($service->isValidScriptUrl('cdn.example.com/rekai.js'));
    }

    #[Test]
    public function isValidScriptUrlReturnsFalseForPlainText(): void
    {
        $service = $this->makeService([]);
        self::assertFalse($service->isValidScriptUrl('not a url at all'));
    }

    #[Test]
    public function isValidScriptUrlReturnsTrueForHttpsUrl(): void
    {
        $service = $this->makeService([]);
        self::assertTrue($service->isValidScriptUrl('https://cdn.example.com/rekai.js'));
    }

    #[Test]
    public function isValidScriptUrlReturnsTrueForHttpUrl(): void
    {
        $service = $this->makeService([]);
        self::assertTrue($service->isValidScriptUrl('http://cdn.example.com/rekai.js'));
    }

    #[Test]
    public function isValidScriptUrlReturnsTrueForUrlWithQueryString(): void
    {
        $service = $this->makeService([]);
        self::assertTrue($service->isValidScriptUrl('https://cdn.example.com/rekai.js?v=2'));
    }

    // ── buildAutocompleteInitScript ──────────────────────────────────────────

    #[Test]
    public function buildAutocompleteInitScriptReturnsEmptyStringWhenModeIsZero(): void
    {
        $service = $this->makeService(['autocompleteMode' => 0]);
        self::assertSame('', $service->buildAutocompleteInitScript($this->makeSite(['autocompleteMode' => 0])));
    }

    #[Test]
    public function buildAutocompleteInitScriptReturnsCustomScriptInMode2(): void
    {
        $customScript = '<script>console.log("custom init");</script>';
        $config = ['autocompleteMode' => 2, 'autocompleteCustomScript' => $customScript];
        $service = $this->makeService($config);
        self::assertSame($customScript, $service->buildAutocompleteInitScript($this->makeSite($config)));
    }

    #[Test]
    public function buildAutocompleteInitScriptFallsBackToDefaultWhenMode2HasEmptyCustomScript(): void
    {
        $config = [
            'autocompleteMode'           => 2,
            'autocompleteCustomScript'   => '   ',
            'autocompleteSelector'       => '.search-box',
            'autocompleteNumberOfResults' => 5,
            'autocompleteOpenOnClick'    => false,
            'autocompleteUseCurrentLanguage' => false,
        ];
        $service = $this->makeService($config);
        $result = $service->buildAutocompleteInitScript($this->makeSite($config));
        self::assertStringContainsString('rekai_autocomplete', $result);
        self::assertStringContainsString('.search-box', $result);
    }

    #[Test]
    public function buildAutocompleteInitScriptGeneratesDefaultScriptInMode1(): void
    {
        $config = [
            'autocompleteMode'           => 1,
            'autocompleteSelector'       => '#main-search',
            'autocompleteNumberOfResults' => 8,
            'autocompleteOpenOnClick'    => false,
            'autocompleteUseCurrentLanguage' => false,
            'autocompleteCustomScript'   => '',
        ];
        $service = $this->makeService($config);
        $result = $service->buildAutocompleteInitScript($this->makeSite($config));

        self::assertStringContainsString('<script>', $result);
        self::assertStringContainsString('__rekai.ready', $result);
        self::assertStringContainsString("rekai_autocomplete('#main-search'", $result);
        self::assertStringContainsString('nrOfHits: 8', $result);
        self::assertStringNotContainsString('allowedlangs', $result);
        self::assertStringNotContainsString('.on(', $result);
    }

    #[Test]
    public function buildAutocompleteInitScriptIncludesClickHandlerWhenOpenOnClickEnabled(): void
    {
        $config = [
            'autocompleteMode'           => 1,
            'autocompleteSelector'       => '.search',
            'autocompleteNumberOfResults' => 5,
            'autocompleteOpenOnClick'    => true,
            'autocompleteUseCurrentLanguage' => false,
            'autocompleteCustomScript'   => '',
        ];
        $service = $this->makeService($config);
        $result = $service->buildAutocompleteInitScript($this->makeSite($config));

        self::assertStringContainsString(".on('rekai_autocomplete:selected'", $result);
        self::assertStringContainsString('window.location = suggestion.url', $result);
    }

    #[Test]
    public function buildAutocompleteInitScriptOmitsAllowedLangsWhenNoRequestAvailable(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        $config = [
            'autocompleteMode'           => 1,
            'autocompleteSelector'       => '.search',
            'autocompleteNumberOfResults' => 5,
            'autocompleteOpenOnClick'    => false,
            'autocompleteUseCurrentLanguage' => true,
            'autocompleteCustomScript'   => '',
        ];
        $service = $this->makeService($config);
        $result = $service->buildAutocompleteInitScript($this->makeSite($config));

        self::assertStringNotContainsString('allowedlangs', $result);
    }

    #[Test]
    public function buildAutocompleteInitScriptUsesConfiguredNumberOfResults(): void
    {
        $config = [
            'autocompleteMode'           => 1,
            'autocompleteSelector'       => '.search',
            'autocompleteNumberOfResults' => 10,
            'autocompleteOpenOnClick'    => false,
            'autocompleteUseCurrentLanguage' => false,
            'autocompleteCustomScript'   => '',
        ];
        $service = $this->makeService($config);
        $result = $service->buildAutocompleteInitScript($this->makeSite($config));

        self::assertStringContainsString('nrOfHits: 10', $result);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function makeService(array $config): RekAiConfigurationService
    {
        $siteSettingsService = $this->createMock(SiteSettingsService::class);
        $siteSettingsFactory = $this->createMock(SiteSettingsFactory::class);
        return new RekAiConfigurationService($siteSettingsService, $siteSettingsFactory);
    }

    private function makeSite(array $config): Site
    {
        return new Site('test', 1, [
            'base' => 'https://example.com/',
            'settings' => ['one_forge_rekai' => $config],
        ]);
    }
}
