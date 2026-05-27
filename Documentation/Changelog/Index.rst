.. include:: /Includes.rst.txt

.. _changelog:

=========
Changelog
=========

.. _changelog-1-3-1:

1.3.1
=====

*Release date: 27.05.2026*

**Changes**

* **Default script URL** — the *Script URL* field in the extension configuration now ships with a
  pre-filled demo value (``https://static.rekai.eu/files/demo.min.js``) so the extension works
  out of the box without requiring manual configuration for evaluation purposes.

.. _changelog-1-3-0:

1.3.0
=====

*Release date: 11.05.2026*

**Features**

* **TYPO3 14.x compatibility** — the extension now supports TYPO3 14.x in addition to 12.4 and
  13.4.

.. _changelog-1-2-1:

1.2.1
=====

*Release date: 04.05.2026*

**Bug fixes**

* Fixed a typo in the TCA column key for the *Render Style* field: it was registered as
  ``tx_rekai_recnderstyle`` instead of ``tx_rekai_renderstyle``, which caused the field to be
  invisible in the backend form under the *Display* tab of the *Rek.ai Recommendations* content
  element.

.. _changelog-1-2-0:

1.2.0
=====

*Release date: 29.04.2026*

**Features**

* **Per-site configuration** — extension settings are now stored independently for each TYPO3
  site in :file:`config/sites/<identifier>/settings.yaml` under the ``one_forge_rekai`` key.
  Previously all sites shared a single global configuration in
  :file:`config/system/settings.php`.
* **Site selector dropdown** in the backend module: when a TYPO3 installation contains more than
  one site, a selector appears above the form so administrators can configure each site
  individually without leaving the module.

**Breaking changes**

* All public methods of :php:`RekAiConfigurationService` now require a
  :php:`TYPO3\CMS\Core\Site\Entity\Site` argument. Custom code calling the service directly must
  be updated to pass the relevant site object.

.. _changelog-1-1-0:

1.1.0
=====

*Release date: 08.04.2026*

**Features**

* Backend module — **Non CSS Version** toggle: adds ``data-allowinlinecss="false"`` to the
  injected ``<script>`` tag, allowing sites to provide their own styling for Rek.ai widgets
  instead of using the built-in inline CSS.
* **Autocomplete** integration in the backend module: three modes are available — disabled,
  default selector-based (with generated initialisation script and live preview), and custom
  (editor-provided JavaScript block). Supports configurable selector, open-on-click behaviour,
  current-language filtering, and number of results.
* New **``rekai_qna``** content element (*Rek.ai Questions and Answers*): renders a
  ``<div class="rek-prediction" data-entitytype="rekai-qna">`` widget. Supports branch-mode
  scoping (none / current branch / specific pages / current page only), tag filtering, and
  configurable link and highlight behaviour.
* **``rekai_recommendations``** content element — new *Use specific pages* source mode: editors
  can select individual pages directly as the recommendation source. Selected page UIDs are
  resolved to URL slugs and passed as ``data-subtree`` to the Rek.ai script.

.. _changelog-1-0-0:

1.0.0
=====

*Release date: 23.03.2026*

Initial release.

**Features**

* Backend configuration module under :guilabel:`Site > Rek.ai` to manage the Rek.ai script URL
  and the global script injection toggle.
* :php:`PageRendererHook` that injects the Rek.ai ``<script defer>`` tag into every frontend
  page when enabled.
* ``rekai_recommendations`` content element with configurable display options (render style,
  number of hits, header text) and recommendation source scoping (current page, subpages,
  page tree level).
* :php:`RekAiConfigurationService` for reading and writing extension configuration.
* :php:`RekaiSettingsProcessor` data processor for passing extension settings to Fluid templates.
* Compatible with TYPO3 12.0–13.x and PHP 8.1+.
