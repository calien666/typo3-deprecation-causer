<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Information\Typo3Version;

// TYPO3 13 selects the data structure by a pointer field, TYPO3 14 by the record type.
$settingsConfig = (new Typo3Version())->getMajorVersion() >= 14
    ? [
        'type' => 'flex',
        'ds' => 'FILE:EXT:third_party_flexform/Configuration/FlexForms/Settings.xml',
    ]
    : [
        'type' => 'flex',
        'ds_pointerField' => 'record_type',
        'ds' => [
            'default' => 'FILE:EXT:third_party_flexform/Configuration/FlexForms/Settings.xml',
        ],
    ];

return [
    'ctrl' => [
        'title' => 'tx_thirdpartyflexform_record',
        'label' => 'title',
        'type' => 'record_type',
    ],
    'columns' => [
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
            ],
        ],
        'record_type' => [
            'label' => 'Type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'Vendor',
                        'value' => 'vendor',
                    ],
                ],
            ],
        ],
        'settings' => [
            'label' => 'Settings',
            'config' => $settingsConfig,
        ],
    ],
    'types' => [
        'vendor' => [
            'showitem' => 'record_type, title, settings',
        ],
    ],
];
