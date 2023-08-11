<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Template Sysinfo',
    'description' => 'Provides a backend module for showing which templates included where. This supports Typo3 migration.',
    'category' => 'module',
    'author' => 'Martin Keller',
    'author_email' => 'martin.keller@taketool.de',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '10.4.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];