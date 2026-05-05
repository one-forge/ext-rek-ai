<?php

return [
    'rek_ai_configuration' => [
        'extensionName' => 'OneforgeRekai',
        'parent' => 'web',
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