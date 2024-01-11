<?php

use Taketool\Sysinfo\Controller\Mod1Controller;
use Taketool\Sysinfo\Controller\Sha1Controller;
use Taketool\Sysinfo\Controller\CurlController;

/**
 * Definitions for modules provided by EXT:sysinfo
 * register the module for v12 here
 */
return [
    'tools_sysinfo' => [
        'parent' => 'tools',
        'position' => ['top'],
        'access' => 'admin',
        'workspaces' => 'live',
        'path' => '/module/tools/sysinfo',
        'labels' => 'LLL:EXT:sysinfo/Resources/Private/Language/locallang_mod1.xlf',
        'extensionName' => 'Sysinfo',
        'iconIdentifier' => 'mod1',
        'inheritNavigationComponentFromMainModule' => false,
        'controllerActions' => [
            Mod1Controller::class => ['securityCheck','allTemplates','checkDomains','deleteFile','plugins','rootTemplates','viewFile'],
            Sha1Controller::class => ['shaOne','shaOneJs','shaOnePhp'],
            CurlController::class => ['index'],
        ],
    ],
];
