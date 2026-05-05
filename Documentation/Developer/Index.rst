.. include:: /Includes.rst.txt

.. _developer:

===============
For Developers
===============

.. contents::
   :local:
   :depth: 2

.. _developer-architecture:

Architecture Overview
======================

The extension follows standard TYPO3 12/13 patterns: PSR-4 autoloading, Symfony DI with autowiring,
attribute-based backend controller registration, and Fluid templates.

.. code-block:: none

   Classes/
   ├── Controller/Backend/
   │   └── ConfigurationController.php   # Backend module controller
   ├── DataProcessing/
   │   └── RekaiSettingsProcessor.php    # Fluid data processor
   ├── Hook/
   │   └── PageRendererHook.php          # PageRenderer pre-process hook
   └── Service/
       └── RekAiConfigurationService.php # Read/write extension configuration

.. _developer-service:

RekAiConfigurationService
==========================

:php:`\OneForge\RekAi\Service\RekAiConfigurationService` is the central service for reading and
writing the extension configuration. Configuration is **per site** and stored in
:file:`config/sites/<identifier>/settings.yaml`. The service is available via dependency injection.

.. _developer-service-api:

Public API
----------

.. code-block:: php

   use OneForge\RekAi\Service\RekAiConfigurationService;
   use TYPO3\CMS\Core\Site\Entity\Site;

   // Via constructor injection (recommended)
   public function __construct(
       private readonly RekAiConfigurationService $rekAiConfigService,
   ) {}

All read methods require the concrete :php:`TYPO3\CMS\Core\Site\Entity\Site` object (not
:php:`SiteInterface` or :php:`NullSite`). Obtain it from the current frontend request via
``$request->getAttribute('site')`` or from :php:`SiteFinder` in backend contexts.

**Methods**

.. rst-class:: dl-parameters

:php:`getConfigurationForSite(Site $site): array`
   Returns the full configuration array for the given site, merged with defaults.
   Keys: ``loadScripts``, ``scriptUrl``, ``nonCssVersion``, ``autocompleteMode``,
   ``autocompleteSelector``, ``autocompleteOpenOnClick``, ``autocompleteUseCurrentLanguage``,
   ``autocompleteNumberOfResults``, ``autocompleteCustomScript``.

:php:`isScriptLoadingEnabled(Site $site): bool`
   Returns ``true`` when the ``loadScripts`` flag is enabled for the site.

:php:`isNonCssVersion(Site $site): bool`
   Returns ``true`` when the ``nonCssVersion`` flag is enabled for the site.

:php:`getConfiguredScriptUrl(Site $site): string`
   Returns the configured script URL as a trimmed string, or an empty string if not set.

:php:`isValidScriptUrl(string $scriptUrl): bool`
   Returns ``true`` when the given string is a non-empty, well-formed URL
   (uses :php:`filter_var($url, FILTER_VALIDATE_URL)`). Stateless — no site parameter needed.

:php:`getAutocompleteMode(Site $site): int`
   Returns the autocomplete mode (0, 1, or 2) for the site.

:php:`getAutocompleteSelector(Site $site): string`
   Returns the CSS selector string for autocomplete mode 1.

:php:`isAutocompleteOpenOnClick(Site $site): bool`
   Returns ``true`` when the open-on-click behaviour is enabled.

:php:`isAutocompleteUseCurrentLanguage(Site $site): bool`
   Returns ``true`` when language filtering is enabled.

:php:`getAutocompleteNumberOfResults(Site $site): int`
   Returns the configured maximum number of autocomplete suggestions.

:php:`getAutocompleteCustomScript(Site $site): string`
   Returns the raw custom autocomplete script for mode 2.

:php:`buildAutocompleteInitScript(Site $site): string`
   Generates and returns the full autocomplete initialisation ``<script>`` block for the site.
   Returns an empty string when autocomplete mode is 0.

:php:`saveConfiguration(Site $site, bool $loadScripts, string $scriptUrl, bool $nonCssVersion, int $mode, string $selector, string $customScript, bool $openOnClick, bool $useCurrentLanguage, int $numberOfResults): void`
   Persists all values to the site's :file:`settings.yaml` via
   :php:`SiteSettingsService::writeSettings()`, which also flushes the TYPO3 code cache so the
   new values take effect immediately.

.. _developer-hook:

PageRendererHook
=================

:php:`\OneForge\RekAi\Hook\PageRendererHook` is registered via :php:`ext_localconf.php` as a
``render-preProcess`` hook on :php:`t3lib_pagerenderer`:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']
       ['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][]
       = \OneForge\RekAi\Hook\PageRendererHook::class . '->addRekAiScript';

The hook's ``addRekAiScript()`` method:

1. Reads the current site from ``$GLOBALS['TYPO3_REQUEST']->getAttribute('site')``.
2. Returns early if the attribute is not a concrete :php:`Site` instance (e.g. CLI or backend context).
3. Reads the configuration for that site via :php:`RekAiConfigurationService`.
4. Returns early if ``loadScripts`` is disabled or ``scriptUrl`` is empty.
5. Otherwise calls :php:`PageRenderer::addHeaderData()` with:

   .. code-block:: html

      <script src="https://static.rekai.se/xyz.js" defer></script>

The ``src`` attribute is escaped with :php:`htmlspecialchars()`.

.. _developer-data-processor:

RekaiSettingsProcessor
=======================

:php:`\OneForge\RekAi\DataProcessing\RekaiSettingsProcessor` implements
:php:`TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface`. It is configured in the
TypoScript rendering chain for both ``tt_content.rekai_recommendations`` and
``tt_content.rekai_qna``:

.. code-block:: typoscript

   dataProcessing {
       10 = OneForge\RekAi\DataProcessing\RekaiSettingsProcessor
   }

The processor reads the full extension configuration for the current site and assigns it as the
Fluid variable ``{rekaiSettings}``. Both frontend templates use ``{rekaiSettings.loadScripts}``
to guard their widget output. The processor also resolves any selected page UIDs in the
``tx_rekai_subtree_pages`` / ``tx_rekai_qna_subtree_pages`` group fields to URL slugs, storing
the result in ``data.tx_rekai_subtree`` / ``data.tx_rekai_qna_subtree`` respectively.

.. _developer-backend-controller:

ConfigurationController
========================

:php:`\OneForge\RekAi\Controller\Backend\ConfigurationController` handles both :http:`GET` and
:http:`POST` requests for the backend module. It is registered with the
:php:`#[AsBackendController]` attribute and resolves through Symfony DI.

**Site resolution** — the active site is determined from the ``rekaiSite`` key in the POST body
(hidden form field) or GET query parameter, falling back to the first site returned by
:php:`SiteFinder::getAllSites()`. URLs for site switching are built via
:php:`BackendUriBuilder::buildUriFromRoute()` so they survive TYPO3's backend routing.

**GET** – renders the form pre-populated with the configuration of the resolved site. When more
than one TYPO3 site exists, a site selector dropdown is shown above the form.

**POST** – validates input (URL must be valid when ``loadScripts`` is checked), saves the
configuration for the resolved site via :php:`RekAiConfigurationService::saveConfiguration()`,
queues a flash message, and redirects (PRG pattern) back to the module URL for the same site.

The controller uses :php:`ModuleTemplateFactory` to create a :php:`ModuleTemplate` and returns
:php:`ModuleTemplate::renderResponse('Backend/Configuration/Index')`.

.. _developer-tca:

TCA & Database Schema
======================

The extension extends ``tt_content`` with the following columns:

**rekai_recommendations**

.. list-table::
   :header-rows: 1
   :widths: 30 15 55

   * - Column
     - Type
     - Description
   * - ``tx_rekai_show_header``
     - tinyint(1)
     - Toggle: show widget header (default 1)
   * - ``tx_rekai_headertext``
     - varchar(255)
     - Text displayed above the recommendations list
   * - ``tx_rekai_titlemaxlength``
     - int(11)
     - Maximum characters for each recommendation title (1–99, default 20)
   * - ``tx_rekai_nrofhits``
     - int(11)
     - Number of recommendation links to render (1–20, default 5)
   * - ``tx_rekai_renderstyle``
     - varchar(20)
     - Layout style: ``pills``, ``list``, or ``advanced``
   * - ``tx_rekai_listcols``
     - int(11)
     - Number of columns for the ``list`` render style (1–6, default 2)
   * - ``tx_rekai_rootpath_mode``
     - varchar(20)
     - Scope of candidate pages: ``''``, ``subpages``, or ``level``
   * - ``tx_rekai_rootpathlevel``
     - int(11)
     - Tree level for the ``level`` root path mode (1–10, default 1)
   * - ``tx_rekai_subtree_pages``
     - text
     - Page UIDs (serialised group field) resolved to URL slugs at render time; passed as ``data-subtree``
   * - ``tx_rekai_excludechildnodes``
     - tinyint(1)
     - Exclude children of the root path entry point (default 0)
   * - ``tx_rekai_extra_attributes``
     - text
     - Raw HTML data-attribute string appended verbatim to the widget ``<div>``

**rekai_qna**

.. list-table::
   :header-rows: 1
   :widths: 30 15 55

   * - Column
     - Type
     - Description
   * - ``tx_rekai_qna_branch_mode``
     - varchar(20)
     - Q&A scope: ``''`` (none), ``current``, ``subtree``, or ``currentpage``
   * - ``tx_rekai_qna_subtree_pages``
     - text
     - Page UIDs resolved to URL slugs; passed as ``data-subtree`` when branch mode is ``subtree``
   * - ``tx_rekai_qna_nrofhits``
     - int(11)
     - Maximum Q&A items to display (0 = no limit, default 0)
   * - ``tx_rekai_qna_tags``
     - varchar(255)
     - Comma-separated tag filter; passed as ``data-tags``
   * - ``tx_rekai_qna_hide_answer_link_same``
     - tinyint(1)
     - Hide the answer link when the answer is on the current page (default 0)
   * - ``tx_rekai_qna_hide_answer_link``
     - tinyint(1)
     - Always hide the link to the answer page (default 0)
   * - ``tx_rekai_qna_disable_highlight``
     - tinyint(1)
     - Disable Rek.ai keyword highlighting in answers (default 0)

.. _developer-di:

Dependency Injection
=====================

:file:`Configuration/Services.yaml` enables autowiring and autoconfiguration for all classes under
the ``OneForge\RekAi\`` namespace. The backend controller is explicitly declared ``public: true``
so TYPO3's backend routing can resolve it from the container:

.. code-block:: yaml

   services:
     _defaults:
       autowire: true
       autoconfigure: true
       public: false

     OneForge\RekAi\:
       resource: '../Classes/*'

     OneForge\RekAi\Controller\Backend\ConfigurationController:
       public: true

     OneForge\RekAi\Hook\PageRendererHook:
       public: true

.. _developer-extending:

Extending the Extension
========================

Custom data attributes
-----------------------

If the Rek.ai widget requires additional ``data-*`` attributes that are not covered by the
standard TCA fields, use the **Extra Attributes** field on the content element (see
:ref:`editor-tab-advanced`) without touching the extension code.

Custom templates
-----------------

Override the Fluid template by adding a higher-priority path in your site package's TypoScript:

.. code-block:: typoscript

   tt_content.rekai_recommendations.templateRootPaths {
       20 = EXT:my_site_package/Resources/Private/Templates/Content/
   }

Place your custom :file:`RekaiRecommendations.html` in that directory.

Custom data processor
----------------------

To pass additional variables to the Fluid template, chain your own data processor after the
built-in one:

.. code-block:: typoscript

   tt_content.rekai_recommendations.dataProcessing {
       20 = Vendor\MyExtension\DataProcessing\MyProcessor
   }
