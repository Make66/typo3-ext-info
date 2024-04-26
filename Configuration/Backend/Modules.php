<?php

use T3docs\Examples\Controller\ModuleController;
use T3docs\Examples\Controller\AdminModuleController;

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
        'labels' => 'LLL:EXT:sysinfo/Resources/Private/Language/Module/locallang_mod.xlf',
        'extensionName' => 'Sysinfo',
        'controllerActions' => [
            Mod1Controller::class => ['sysinfo,securityCheck,allTemplates,checkDomains,deleteFile,plugins,rootTemplates,viewFile'],
            Sha1Controller::class => ['shaOne,shaOneJs,shaOnePhp'],
            CurlController::class => ['index'],
        ],
    ],
];
