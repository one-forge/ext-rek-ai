<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$parentModule = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() >= 14
    ? 'content'
    : 'web';

return [
    'rek_ai_configuration' => [
        'extensionName' => 'OneforgeRekai',
        'parent' => $parentModule,
        'position' => ['after' => '*'],
        'access' => 'user',
        'path' => '/module/rek-ai/configuration',
        'labels' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang_mod.xlf',
        'iconIdentifier' => 'rek-ai-icon',
        'routes' => [
            '_default' => [
                'target' => \OneForge\RekAi\Controller\Backend\ConfigurationController::class . '::indexAction',
            ],
        ],
    ],
];