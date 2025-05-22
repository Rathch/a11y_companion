<?php
return [
    'ctrl' => [
        'title' => 'Blacklist Word',
        'label' => 'word',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'hideTable' => false,
        'iconfile' => 'EXT:a11y_companion/Resources/Public/Icons/Extension.svg',
        'rootLevel' => -1,
    ],
    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'word' => [
            'label' => 'Blacklist Word',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'eval' => 'trim,required',
            ],
        ],
    ],
    'types' => [
        '1' => ['showitem' => 'word, hidden'],
    ],
];