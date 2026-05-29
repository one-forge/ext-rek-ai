<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Register content element 'Recommendations'
ExtensionManagementUtility::addPlugin(
    [
        'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.title',
        'description' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.description',
        'value' => 'rekai_recommendations',
        'icon'  => 'rek-ai-icon',
        'group' => 'special',
    ],
    'CType',
    'one_forge_rekai'
);

// Add custom columns
$GLOBALS['TCA']['tt_content']['columns'] = array_merge(
    $GLOBALS['TCA']['tt_content']['columns'],
    [
        'tx_rekai_show_header' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.show_header',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 1,
            ],
        ],
        'tx_rekai_headertext' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.headertext',
            'displayCond' => 'FIELD:tx_rekai_show_header:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'default' => 'Discover more',
            ],
        ],
        'tx_rekai_titlemaxlength' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.titlemaxlength',
            'config' => [
                'type' => 'number',
                'default' => 20,
                'range' => ['lower' => 1, 'upper' => 99],
            ],
        ],
        'tx_rekai_nrofhits' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.nrofhits',
            'config' => [
                'type' => 'number',
                'default' => 5,
                'range' => ['lower' => 1, 'upper' => 20],
            ],
        ],
        'tx_rekai_renderstyle' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.renderstyle',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'Pills', 'value' => 'pills'],
                    ['label' => 'List', 'value' => 'list'],
                    ['label' => 'Advanced', 'value' => 'advanced'],
                ],
                'default' => 'pills',
            ],
        ],
        'tx_rekai_listcols' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.listcols',
            'displayCond' => 'FIELD:tx_rekai_renderstyle:=:list',
            'config' => [
                'type' => 'number',
                'default' => 2,
                'range' => ['lower' => 1, 'upper' => 6],
            ],
        ],
        'tx_rekai_rootpath_mode' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.rootpath_mode',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.rootpath_mode.none',  'value' => ''],
                    ['label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.rootpath_mode.subpages', 'value' => 'subpages'],
                    ['label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.rootpath_mode.level',    'value' => 'level'],
                ],
                'default' => '',
            ],
        ],
        'tx_rekai_rootpathlevel' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.rootpathlevel',
            'displayCond' => 'FIELD:tx_rekai_rootpath_mode:=:level',
            'config' => [
                'type' => 'number',
                'default' => 1,
                'range' => ['lower' => 1, 'upper' => 10],
            ],
        ],
        'tx_rekai_subtree_pages' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:tt_content.tx_rekai_subtree_pages',
            'displayCond' => 'FIELD:tx_rekai_rootpath_mode:=:level',
            'config' => [
                'type' => 'group',
                'allowed' => 'pages',
                'foreign_table' => 'pages',
                'size' => 5,
                'minitems' => 0,
                'maxitems' => 99,
                'fieldWizard' => [
                    'recordsOverview' => [
                        'disabled' => false,
                    ],
                ],
            ],
        ],
        'tx_rekai_excludechildnodes' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.excludechildnodes',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'tx_rekai_extra_attributes' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.extra_attributes',
            'config' => [
                'type' => 'input',
                'size' => 60,
                'placeholder' => 'data-foo="bar" data-baz="qux"',
            ],
        ],
    ]
);

// Define showitem layout for the CE
$GLOBALS['TCA']['tt_content']['types']['rekai_recommendations'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
        --div--;LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.tab.display,
            tx_rekai_show_header,
            tx_rekai_headertext,
            tx_rekai_titlemaxlength,
            tx_rekai_nrofhits,
            tx_rekai_renderstyle,
            tx_rekai_listcols,
        --div--;LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.tab.source,
            tx_rekai_rootpath_mode,
            tx_rekai_rootpathlevel,
            tx_rekai_subtree_pages,
            tx_rekai_excludechildnodes,
        --div--;LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.tab.advanced,
            tx_rekai_extra_attributes,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
];

// Register content element 'Questions and Answers'
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.title',
        'description' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.description',
        'value' => 'rekai_qna',
        'icon'  => 'rek-ai-icon',
        'group' => 'special',
    ],
    'CType',
    'one_forge_rekai'
);

// TCA columns for QnA
$GLOBALS['TCA']['tt_content']['columns'] = array_merge(
    $GLOBALS['TCA']['tt_content']['columns'],
    [
        'tx_rekai_qna_show_header' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.show_header',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 1,
            ],
        ],
        'tx_rekai_qna_headertext' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.headertext',
            'displayCond' => 'FIELD:tx_rekai_show_header:REQ:true',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'default' => 'Discover more',
            ],
        ],
        'tx_rekai_qna_branch_mode' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.branch_mode',
            'config' => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => [
                    [
                        'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.branch_mode.none',
                        'value' => '',
                    ],
                    [
                        'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.branch_mode.current',
                        'value' => 'current',
                    ],
                    [
                        'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.branch_mode.subtree',
                        'value' => 'subtree',
                    ],
                    [
                        'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.branch_mode.currentpage',
                        'value' => 'currentpage',
                    ],
                ],
                'default' => '',
            ],
        ],
        'tx_rekai_qna_subtree_pages' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.subtree_pages',
            'config' => [
                'type'                => 'group',
                'allowed'             => 'pages',
                'size'                => 5,
                'maxitems'            => 20,
                'fieldControl'        => [
                    'editPopup'  => ['disabled' => false],
                    'addRecord'  => ['disabled' => true],
                    'listModule' => ['disabled' => false],
                ],
            ],
            'displayCond' => 'FIELD:tx_rekai_qna_branch_mode:=:subtree',
        ],
        'tx_rekai_qna_nrofhits' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.nrofhits',
            'config' => [
                'type'    => 'number',
                'default' => 0,
                'range'   => ['lower' => 0, 'upper' => 100],
            ],
        ],
        'tx_rekai_qna_tags' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.tags',
            'config' => [
                'type'    => 'input',
                'size'    => 50,
                'max'     => 255,
                'default' => '',
            ],
        ],
        'tx_rekai_qna_hide_answer_link_same' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.hide_answer_link_same',
            'config' => [
                'type'    => 'check',
                'default' => 0,
            ],
        ],
        'tx_rekai_qna_hide_answer_link' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.hide_answer_link',
            'config' => [
                'type'    => 'check',
                'default' => 0,
            ],
        ],
        'tx_rekai_qna_disable_highlight' => [
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.disable_highlight',
            'config' => [
                'type'    => 'check',
                'default' => 0,
            ],
        ],
        'tx_rekai_qna_extra_attributes' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.extra_attributes',
            'config' => [
                'type' => 'input',
                'size' => 60,
                'placeholder' => 'data-foo="bar" data-baz="qux"',
            ],
        ],
    ]
);

// Showitem / Palette for the QnA CType
$GLOBALS['TCA']['tt_content']['types']['rekai_qna'] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
        --div--;LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_qna.tab.options,
            tx_rekai_qna_show_header,
            tx_rekai_qna_headertext,
            tx_rekai_qna_nrofhits,
            tx_rekai_qna_branch_mode,
            tx_rekai_qna_subtree_pages,
            tx_rekai_qna_tags,
            tx_rekai_qna_hide_answer_link_same,
            tx_rekai_qna_hide_answer_link,
            tx_rekai_qna_disable_highlight,
        --div--;LLL:EXT:one_forge_rekai/Resources/Private/Language/locallang.xlf:ce.rekai_rec.tab.advanced,
            tx_rekai_qna_extra_attributes,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
    ',
];
