<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Information\Typo3Version;

// TYPO3 13 keys the data structures of a column, TYPO3 14 migrates that with a deprecation of its own.
$dataStructure = static fn(string $dataStructure): string|array => (new Typo3Version())->getMajorVersion() >= 14
    ? $dataStructure
    : ['default' => $dataStructure];

return [
    'ctrl' => [
        'title' => 'tx_firstpartyflexform_record',
        'label' => 'title',
    ],
    'columns' => [
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
            ],
        ],
        'file_settings' => [
            'label' => 'Settings from a file',
            'config' => [
                'type' => 'flex',
                'ds' => $dataStructure('FILE:EXT:first_party_flexform/Configuration/FlexForms/Settings.xml'),
            ],
        ],
        'inline_settings' => [
            'label' => 'Inline settings',
            'config' => [
                'type' => 'flex',
                // The field is migrated to type "datetime" with a deprecation on every core.
                'ds' => $dataStructure('
                    <T3DataStructure>
                        <sheets>
                            <sDEF>
                                <ROOT>
                                    <sheetTitle>General</sheetTitle>
                                    <type>array</type>
                                    <el>
                                        <project_inline_date>
                                            <label>Project inline date</label>
                                            <config>
                                                <type>input</type>
                                                <renderType>inputDateTime</renderType>
                                            </config>
                                        </project_inline_date>
                                    </el>
                                </ROOT>
                            </sDEF>
                        </sheets>
                    </T3DataStructure>
                '),
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => 'title, file_settings, inline_settings',
        ],
    ],
];
