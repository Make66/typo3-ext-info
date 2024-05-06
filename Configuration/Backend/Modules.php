<?php

use Taketool\Sysinfo\Controller\CurlController;
use Taketool\Sysinfo\Controller\Mod1Controller;
use Taketool\Sysinfo\Controller\Sha1Controller;

/**
 * Definitions for modules provided by EXT:sysinfo
 * https://docs.typo3.org/m/typo3/reference-coreapi/12.4/en-us/ExtensionArchitecture/HowTo/BackendModule/ModuleConfiguration.html#backend-modules-configuration
 *
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
        'iconIdentifier' => 'mod1',
        'controllerActions' => [
            Mod1Controller::class => [
                'syslog','syslogDelete','securityCheck','allTemplates',
                'checkDomains','deleteFile','plugins','rootTemplates','viewFile'
            ],
            Sha1Controller::class => ['shaOne','shaOneJs','shaOnePhp'],
            CurlController::class => ['index'],
        ],
    ],
];
