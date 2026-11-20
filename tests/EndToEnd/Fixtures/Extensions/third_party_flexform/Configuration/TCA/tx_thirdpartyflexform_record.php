<?php

declare(strict_types=1);

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
            'config' => [
                'type' => 'flex',
                'ds' => 'FILE:EXT:third_party_flexform/Configuration/FlexForms/Settings.xml',
            ],
        ],
    ],
    'types' => [
        'vendor' => [
            'showitem' => 'record_type, title, settings',
        ],
    ],
];
