<?php
declare(strict_types=1);

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][]
    = \OneForge\RekAi\Hook\PageRendererHook::class . '->addRekAiScript';

ExtensionManagementUtility::addTypoScriptSetup(
    '@import "EXT:one_forge_rekai/Configuration/TypoScript/setup.typoscript"'
);