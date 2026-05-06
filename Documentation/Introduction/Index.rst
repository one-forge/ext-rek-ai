.. include:: /Includes.rst.txt

.. _introduction:

============
Introduction
============

.. _what-it-does:

What does it do?
================

The **1_FORGE – Rek.ai** extension integrates the `Rek.ai <https://www.rek.ai/>`_ content recommendation
service into your TYPO3 website. Rek.ai is a Swedish SaaS platform that analyses visitor behaviour in
real time and serves personalised content recommendations — helping you increase page views, dwell time,
and reader engagement without any tracking cookies.

The extension provides four complementary integration points:

1. **Global script injection** — the Rek.ai JavaScript is automatically added to every frontend page
   via a :php:`PageRenderer` hook, so no TypoScript or manual template changes are required once it is
   configured. An optional *Non CSS Version* mode disables Rek.ai's built-in inline CSS.

2. **Autocomplete** — the Rek.ai autocomplete library can be loaded and initialised automatically.
   Three modes are available: disabled, default (selector-based, with generated initialisation script),
   and custom (editor-provided JavaScript block).

3. **Recommendations content element** — a dedicated backend content element
   (*CType* ``rekai_recommendations``) lets editors place a personalised recommendations widget anywhere
   in the page layout. Editors have full control over the number of results, the render style, header
   text, and the scope of pages that Rek.ai considers as candidates — including selecting specific pages
   directly.

4. **Questions and Answers content element** — a second content element (*CType* ``rekai_qna``)
   renders a Rek.ai Q&A widget. Editors can configure the number of results, the branch scope, tag
   filters, and link behaviour.

.. _screenshots:

Screenshots
===========

.. figure:: /Images/BackendModule.png
   :alt: The Rek.ai backend configuration module
   :class: with-shadow

   The backend configuration module under **Site > Rek.ai**.

.. figure:: /Images/ContentElement.png
   :alt: The Rek.ai Recommendations content element in the backend form
   :class: with-shadow

   The *Rek.ai Recommendations* content element — Display tab.

.. _requirements:

Requirements
============

.. rst-class:: dl-parameters

TYPO3
   Version 12.0 – 13.x

PHP
   8.1 or later

Rek.ai account
   A valid Rek.ai account and a script URL provided by Rek.ai
   (e.g. ``https://static.rekai.se/xyz.js``).

.. _credits:

Credits
=======

Developed and maintained by `1_FORGE <https://1forge.de/>`_.

.. _feedback:

Feedback & Bug Reports
======================

Please use the issue tracker of the extension's source repository to report bugs or request features.
