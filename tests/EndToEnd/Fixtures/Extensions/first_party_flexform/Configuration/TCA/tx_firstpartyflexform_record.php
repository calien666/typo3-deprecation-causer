<?php

declare(strict_types=1);

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
                'ds' => 'FILE:EXT:first_party_flexform/Configuration/FlexForms/Settings.xml',
            ],
        ],
        'inline_settings' => [
            'label' => 'Inline settings',
            'config' => [
                'type' => 'flex',
                // The field is migrated to type "datetime" with a deprecation on every core.
                'ds' => '
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
                ',
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => 'title, file_settings, inline_settings',
        ],
    ],
];
