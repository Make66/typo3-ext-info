<?php

use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3_MODE') || die();

(static function () {
    $isT3v9 = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 10000000;
    $extensionName = $isT3v9
        ? 'Taketool.Sysinfo'
        : 'Sysinfo';

    if ($isT3v9) // <v10
    {
        ExtensionUtility::registerModule(
            $extensionName,
            'tools',
            'm1',
            'top',
            [
                'Mod1' => 'securityCheck,allTemplates,checkDomains,deleteFile,plugins,rootTemplates,viewFile',
                'Curl'=> 'index',
                'Sha1' => 'shaOne,shaOneJs,shaOnePhp',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:sysinfo/Resources/Public/Icons/user_mod_m1.svg',
                'labels' => 'LLL:EXT:sysinfo/Resources/Private/Language/locallang_m1.xlf',
            ]
        );
    } else { // >= v10
        ExtensionUtility::registerModule(
            $extensionName,
            'tools',
            'm1',
            'top',
            [
                Taketool\Sysinfo\Controller\Mod1Controller::class => 'securityCheck,allTemplates,checkDomains,deleteFile,plugins,rootTemplates,viewFile',
                Taketool\Sysinfo\Controller\Sha1Controller::class => 'shaOne,shaOneJs,shaOnePhp',
                Taketool\Sysinfo\Controller\CurlController::class => 'index',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:sysinfo/Resources/Public/Icons/user_mod_m1.svg',
                'labels' => 'LLL:EXT:sysinfo/Resources/Private/Language/locallang_m1.xlf',
            ]
        );
    }
})();
