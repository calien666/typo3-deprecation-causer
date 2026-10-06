<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'tx_firstpartytca_record',
        'label' => 'title',
        // Not evaluated anymore, migrated with a deprecation on every core.
        'cruser_id' => 'cruser_id',
    ],
    'columns' => [
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
            ],
        ],
        'archived' => [
            'label' => 'Archived',
            'config' => [
                // Migrated to type "datetime" with a deprecation on every core.
                'type' => 'input',
                'renderType' => 'inputDateTime',
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => 'title, archived',
        ],
    ],
];
