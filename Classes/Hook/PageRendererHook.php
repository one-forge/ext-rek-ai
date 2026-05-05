<?php
declare(strict_types=1);

namespace OneForge\RekAi\Hook;

use OneForge\RekAi\Service\RekAiConfigurationService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\Entity\Site;

class PageRendererHook
{
    public function __construct(
        private readonly RekAiConfigurationService $configService,
    ) {}

    public function addRekAiScript(array &$params, PageRenderer $pageRenderer): void
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $site = $request?->getAttribute('site');

        if (!$site instanceof Site) {
            return;
        }

        if (!$this->configService->isScriptLoadingEnabled($site)) {
            return;
        }

        $scriptUrl = $this->configService->getConfiguredScriptUrl($site);
        if ($scriptUrl === '') {
            return;
        }

        $baseScriptAttributes = [];
        if ($this->configService->isNonCssVersion($site)) {
            $baseScriptAttributes['data-allowinlinecss'] = 'false';
        }

        $pageRenderer->addJsFile(
            $scriptUrl,
            'text/javascript',
            false,
            true,
            '',
            false,
            '|',
            false,
            '',
            true,
            '',
            false,
            $baseScriptAttributes
        );

        $mode = $this->configService->getAutocompleteMode($site);
        if ($mode === 0) {
            return;
        }

        $pageRenderer->addJsFile(
            'https://static.rekai.se/addon/v3/rekai_autocomplete.min.js',
            'text/javascript'
        );

        $initScript = $this->configService->buildAutocompleteInitScript($site);
        if ($initScript !== '') {
            $pageRenderer->addJsInlineCode('rekai-autocomplete-init', $initScript);
        }
    }
}
