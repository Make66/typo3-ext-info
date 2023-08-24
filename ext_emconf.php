<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Template Sysinfo',
    'description' => 'Provides a backend module for showing which templates included where. This supports Typo3 migration.',
    'category' => 'module',
    'author' => 'Martin Keller',
    'author_email' => 'martin.keller@taketool.de',
    'state' => 'beta',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.4',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];