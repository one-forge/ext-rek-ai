.. include:: /Includes.rst.txt

.. _installation:

============
Installation
============

.. _installation-composer:

Installation via Composer (recommended)
========================================

The extension is available on `Packagist <https://packagist.org/packages/one-forge/rek-ai>`_.
Run the following command in your TYPO3 project root:

.. code-block:: bash

   composer require one-forge/rek-ai

After installation, activate the extension and run the TYPO3 database upgrade wizard if prompted:

.. code-block:: bash

   vendor/bin/typo3 extension:activate one_forge_rekai
   vendor/bin/typo3 database:updateschema

.. _installation-ter:

Installation via Extension Manager (TER)
=========================================

1. Open :guilabel:`Admin Tools > Extensions` in the TYPO3 backend.
2. Switch to the :guilabel:`Get Extensions` tab.
3. Search for ``one_forge_rekai`` and click :guilabel:`Import and Install`.
4. Activate the extension using the toggle in the extension list.
5. Run :guilabel:`Admin Tools > Maintenance > Analyze Database Structure` to apply the new database
   columns.

.. _installation-typoscript:

Include the TypoScript template
================================

The extension ships a TypoScript setup file that registers the ``rekai_recommendations`` content
element renderer. It is auto-imported via :php:`ExtensionManagementUtility::addTypoScriptSetup()` in
:file:`ext_localconf.php`, so **no manual template inclusion is required**.

If you prefer to include it explicitly (e.g. in a site package), add the following to your
TypoScript setup:

.. code-block:: typoscript

   @import 'EXT:one_forge_rekai/Configuration/TypoScript/setup.typoscript'

.. _installation-next-steps:

Next steps
==========

After installation, proceed to :ref:`configuration` to enter your Rek.ai script URL and enable
script injection.
