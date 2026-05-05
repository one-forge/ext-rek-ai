.. include:: /Includes.rst.txt

.. _configuration:

=============
Configuration
=============

.. contents::
   :local:
   :depth: 2

.. _configuration-backend-module:

Backend Configuration Module
=============================

The primary way to configure this extension is through the dedicated backend module located at
:guilabel:`Site > Rek.ai`. It is accessible to administrators only.

Configuration is **per TYPO3 site**. When the TYPO3 installation contains more than one site, a
site selector dropdown appears at the top of the module. Select the site you want to configure
before making changes — each site stores its own independent set of values in
:file:`config/sites/<identifier>/settings.yaml` under the ``one_forge_rekai`` key.

.. figure:: /Images/BackendModule.png
   :alt: The Rek.ai backend configuration module
   :class: with-shadow

   The Rek.ai configuration module.

The module is divided into two sections: **Script Integration** and **Autocomplete**.

.. _configuration-script-integration:

Script Integration
------------------

.. _configuration-load-scripts:

Load Scripts
^^^^^^^^^^^^

.. rst-class:: dl-parameters

Type
   Boolean (toggle)

Default
   Disabled

When enabled, the Rek.ai JavaScript is injected as a ``<script defer>`` tag into the ``<head>``
of every rendered frontend page. The tag is only added when both this toggle is on **and** a
valid Script URL is configured.

.. _configuration-non-css-version:

Non CSS Version
^^^^^^^^^^^^^^^

.. rst-class:: dl-parameters

Type
   Boolean (toggle)

Default
   Disabled

When enabled, the ``data-allowinlinecss="false"`` attribute is added to the Rek.ai ``<script>``
tag. This instructs the Rek.ai script to skip its built-in inline CSS, which is useful when the
site provides its own styling for Rek.ai widgets.

.. _configuration-script-url:

Script URL
^^^^^^^^^^

.. rst-class:: dl-parameters

Type
   URL string

Default
   *(empty)*

Example
   ``https://static.rekai.se/xyz.js``

The full URL to the Rek.ai JavaScript file. This URL is provided by Rek.ai when you create or
configure a site in the Rek.ai dashboard. The module validates that the value is a well-formed URL
before saving; "Load Scripts" cannot be enabled without a valid URL.

After saving, the module shows a preview of the tag that will be injected:

.. code-block:: html

   <script src="https://static.rekai.se/xyz.js" defer></script>

With *Non CSS Version* enabled:

.. code-block:: html

   <script src="https://static.rekai.se/xyz.js" data-allowinlinecss="false" defer></script>

.. _configuration-autocomplete:

Autocomplete
------------

The extension can load and initialise the Rek.ai autocomplete library automatically. All autocomplete
settings are managed in the same backend module.

.. _configuration-autocomplete-mode:

Autocomplete Mode
^^^^^^^^^^^^^^^^^

.. rst-class:: dl-parameters

Type
   Select (integer)

Options
   | ``0`` – Disabled *(default)*
   | ``1`` – Enabled with selector
   | ``2`` – Enabled with custom script

Controls whether the autocomplete feature is active and how it is initialised.

* **Disabled** — no autocomplete library is loaded.
* **Enabled with selector** — the autocomplete library (``rekai_autocomplete.min.js``) is loaded
  and the extension generates an initialisation script automatically based on the settings below.
  A preview of the generated script is shown in the module.
* **Enabled with custom script** — the autocomplete library is loaded and the content of
  **Custom autocomplete script** is injected verbatim as an inline ``<script>`` block.

.. _configuration-autocomplete-selector:

Autocomplete Selector
^^^^^^^^^^^^^^^^^^^^^

.. rst-class:: dl-parameters

Type
   String (CSS selector)

Default
   ``#searchform-input``

Applies to
   Mode 1 only

The CSS selector for the search input field that autocomplete should attach to.

.. _configuration-autocomplete-open-on-click:

Open on Click
^^^^^^^^^^^^^

.. rst-class:: dl-parameters

Type
   Boolean (toggle)

Default
   Enabled

Applies to
   Mode 1 only

When enabled, selecting an autocomplete suggestion navigates to that result's URL immediately.

.. _configuration-autocomplete-use-current-language:

Use Current Language
^^^^^^^^^^^^^^^^^^^^

.. rst-class:: dl-parameters

Type
   Boolean (toggle)

Default
   Enabled

Applies to
   Mode 1 only

When enabled, the autocomplete request is restricted to content in the current page language
(using the ISO language code resolved from the active TYPO3 site language).

.. _configuration-autocomplete-number-of-results:

Number of Results
^^^^^^^^^^^^^^^^^

.. rst-class:: dl-parameters

Type
   Integer

Default
   ``5``

Applies to
   Mode 1 only

The maximum number of autocomplete suggestions to display.

.. _configuration-autocomplete-custom-script:

Custom Autocomplete Script
^^^^^^^^^^^^^^^^^^^^^^^^^^

.. rst-class:: dl-parameters

Type
   Text (JavaScript)

Applies to
   Mode 2 only

A full custom JavaScript block that is injected as-is into every frontend page when autocomplete
mode 2 is active. Use this when you need full control over the autocomplete initialisation that
the generated script (mode 1) does not provide.

.. warning::

   The custom script is output without sanitisation. Only enter trusted JavaScript here.

.. _configuration-site-settings:

Site Settings Storage
======================

All values configured in the backend module are stored per site in
:file:`config/sites/<identifier>/settings.yaml` under the ``one_forge_rekai`` key. The file is
created automatically on first save if it does not already exist.

Example :file:`config/sites/my-site/settings.yaml`:

.. code-block:: yaml

   one_forge_rekai:
     loadScripts: true
     scriptUrl: 'https://static.rekai.se/xyz.js'
     nonCssVersion: false
     autocompleteMode: 1
     autocompleteSelector: '#searchform-input'
     autocompleteOpenOnClick: true
     autocompleteUseCurrentLanguage: true
     autocompleteNumberOfResults: 5
     autocompleteCustomScript: ''

Each site in a multi-site TYPO3 installation maintains its own independent configuration file.
Values are read and written by :php:`\OneForge\RekAi\Service\RekAiConfigurationService` via
:php:`TYPO3\CMS\Core\Site\SiteSettingsService` and :php:`TYPO3\CMS\Core\Site\SiteSettingsFactory`.

.. _configuration-typoscript:

TypoScript Reference
=====================

The extension auto-imports the following TypoScript setup, which registers both content element
rendering chains:

.. code-block:: typoscript

   tt_content.rekai_recommendations = FLUIDTEMPLATE
   tt_content.rekai_recommendations {
       templateName = RekaiRecommendations

       templateRootPaths {
           10 = EXT:one_forge_rekai/Resources/Private/Templates/Content/
       }

       dataProcessing {
           10 = OneForge\RekAi\DataProcessing\RekaiSettingsProcessor
       }
   }

   tt_content.rekai_qna = FLUIDTEMPLATE
   tt_content.rekai_qna {
       templateName = RekaiQna

       templateRootPaths {
           10 = EXT:one_forge_rekai/Resources/Private/Templates/Content/
       }

       dataProcessing {
           10 = OneForge\RekAi\DataProcessing\RekaiSettingsProcessor
       }
   }

:typoscript:`dataProcessing.10` (:php:`RekaiSettingsProcessor`) passes the extension configuration
array as the Fluid variable ``{rekaiSettings}`` to both templates. It also resolves any selected
page UIDs to their URL slugs so they can be passed as ``data-subtree`` attributes.

.. _configuration-rendering-output:

Rendered HTML output
---------------------

**Recommendations element** — attributes vary by field values:

.. code-block:: html

   <div class="rek-prediction"
        data-headertext="Discover more"
        data-nrofhits="5"
        data-renderstyle="pills">
   </div>

With a specific page subtree selected:

.. code-block:: html

   <div class="rek-prediction"
        data-nrofhits="5"
        data-renderstyle="list"
        data-listcols="2"
        data-subtree="/news/,/blog/">
   </div>

**Q&A element**:

.. code-block:: html

   <div class="rek-prediction"
        data-entitytype="rekai-qna"
        data-nrofhits="10"
        data-userootpath="true">
   </div>

The Rek.ai JavaScript (loaded via the ``<script>`` in ``<head>``) finds all elements with the
class ``rek-prediction`` and populates them with the appropriate content.
