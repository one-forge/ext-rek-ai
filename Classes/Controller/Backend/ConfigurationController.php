<?php
declare(strict_types=1);

namespace OneForge\RekAi\Controller\Backend;

use OneForge\RekAi\Service\RekAiConfigurationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsBackendController;
use TYPO3\CMS\Backend\Routing\UriBuilder as BackendUriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

#[AsBackendController]
class ConfigurationController
{
    private const ROUTE = 'rek_ai_configuration';

    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly FlashMessageService $flashMessageService,
        private readonly SiteFinder $siteFinder,
        private readonly RekAiConfigurationService $configService,
        private readonly BackendUriBuilder $backendUriBuilder,
        private readonly PageRenderer $pageRenderer,
    ) {}

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $allSites = $this->siteFinder->getAllSites();

        if ($allSites === []) {
            $moduleTemplate = $this->moduleTemplateFactory->create($request);
            $moduleTemplate->assign('noSites', true);
            return $moduleTemplate->renderResponse('Backend/Configuration/Index');
        }

$selectedSite = $this->resolveSelectedSite($request, $allSites);

        if ($request->getMethod() === 'POST') {
            return $this->handlePost($request, $selectedSite);
        }

        return $this->renderForm($request, $selectedSite, $allSites);
    }

    private function handlePost(
        ServerRequestInterface $request,
        Site $selectedSite,
    ): ResponseInterface {
        $parsedBody = (array)$request->getParsedBody();

        $loadScripts    = isset($parsedBody['loadScripts']);
        $scriptUrl      = trim((string)($parsedBody['scriptUrl'] ?? ''));
        $nonCssVersion  = isset($parsedBody['nonCssVersion']);

        $autocompleteMode            = (int)($parsedBody['autocompleteMode'] ?? 0);
        $autocompleteSelector        = trim((string)($parsedBody['autocompleteSelector'] ?? ''));
        $autocompleteCustomScript    = (string)($parsedBody['autocompleteCustomScript'] ?? '');
        $autocompleteOpenOnClick     = isset($parsedBody['autocompleteOpenOnClick']);
        $autocompleteUseCurrentLang  = isset($parsedBody['autocompleteUseCurrentLanguage']);
        $autocompleteNumberOfResults = (int)($parsedBody['autocompleteNumberOfResults'] ?? 5);

        if ($loadScripts && !$this->configService->isValidScriptUrl($scriptUrl)) {
            $this->addFlashMessage(
                'Please provide a valid script URL when "Load Scripts" is enabled.',
                'Validation error',
                ContextualFeedbackSeverity::ERROR,
            );
        } else {
            $this->configService->saveConfiguration(
                $selectedSite,
                $loadScripts,
                $scriptUrl,
                $nonCssVersion,
                $autocompleteMode,
                $autocompleteSelector,
                $autocompleteCustomScript,
                $autocompleteOpenOnClick,
                $autocompleteUseCurrentLang,
                $autocompleteNumberOfResults,
            );

            $this->addFlashMessage('Configuration saved successfully.', 'Saved', ContextualFeedbackSeverity::OK);
        }

        // PRG: redirect back to GET so a page reload doesn't re-submit
        $uri = $this->backendUriBuilder->buildUriFromRoute(self::ROUTE, ['rekaiSite' => $selectedSite->getIdentifier()]);
        return new RedirectResponse((string)$uri, 303);
    }

    private function renderForm(
        ServerRequestInterface $request,
        Site $selectedSite,
        array $allSites,
    ): ResponseInterface {
        $this->pageRenderer->addJsFile(
            'EXT:one_forge_rekai/Resources/Public/JavaScript/backend-configuration.js',
            'text/javascript',
            false,
            false,
            '',
            false,
            '|',
            false,
            '',
            true,
        );

        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $config = $this->configService->getConfigurationForSite($selectedSite);

        $autocompleteMode        = (int)($config['autocompleteMode'] ?? 0);
        $autocompletePreviewScript = '';
        if ($autocompleteMode === 1) {
            $autocompletePreviewScript = $this->configService->buildAutocompleteInitScript($selectedSite);
        }

        $sitesForTemplate = array_map(
            fn(Site $site) => [
                'identifier' => $site->getIdentifier(),
                'base'       => (string)$site->getBase(),
                'url'        => (string)$this->backendUriBuilder->buildUriFromRoute(self::ROUTE, ['rekaiSite' => $site->getIdentifier()]),
            ],
            $allSites,
        );

        $formAction = (string)$this->backendUriBuilder->buildUriFromRoute(self::ROUTE, ['rekaiSite' => $selectedSite->getIdentifier()]);

        $moduleTemplate->assignMultiple([
            'loadScripts'    => (bool)($config['loadScripts'] ?? false),
            'scriptUrl'      => (string)($config['scriptUrl'] ?? ''),
            'nonCssVersion'  => (bool)($config['nonCssVersion'] ?? false),

            'autocompleteMode'               => $autocompleteMode,
            'autocompleteSelector'           => (string)($config['autocompleteSelector'] ?? ''),
            'autocompleteCustomScript'       => (string)($config['autocompleteCustomScript'] ?? ''),
            'autocompleteOpenOnClick'        => (bool)($config['autocompleteOpenOnClick'] ?? false),
            'autocompleteUseCurrentLanguage' => (bool)($config['autocompleteUseCurrentLanguage'] ?? false),
            'autocompleteNumberOfResults'    => (int)($config['autocompleteNumberOfResults'] ?? 5),
            'autocompletePreviewScript'      => $autocompletePreviewScript,

            'sites'                       => $sitesForTemplate,
            'selectedSiteIdentifier'      => $selectedSite->getIdentifier(),
            'formAction'                  => $formAction,
            'flashMessageQueueIdentifier' => $this->flashMessageService->getMessageQueueByIdentifier()->getIdentifier(),
        ]);

        return $moduleTemplate->renderResponse('Backend/Configuration/Index');
    }

    /**
     * @param Site[] $allSites
     */
    private function resolveSelectedSite(ServerRequestInterface $request, array $allSites): Site
    {
        $parsedBody = (array)$request->getParsedBody();
        $siteIdentifier = $parsedBody['rekaiSite']
            ?? $request->getQueryParams()['rekaiSite']
            ?? null;

        if ($siteIdentifier !== null) {
            foreach ($allSites as $site) {
                if ($site->getIdentifier() === $siteIdentifier) {
                    return $site;
                }
            }
        }

        return reset($allSites);
    }

    private function addFlashMessage(string $message, string $title, ContextualFeedbackSeverity $severity): void
    {
        $flashMessage = new FlashMessage($message, $title, $severity, true);
        $this->flashMessageService->getMessageQueueByIdentifier()->enqueue($flashMessage);
    }
}
