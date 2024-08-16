<?php

use Taketool\Sysinfo\Controller\CurlController;
use Taketool\Sysinfo\Controller\Mod1Controller;
use Taketool\Sysinfo\Controller\Sha1Controller;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3_MODE') || die();

(static function () {
    $t3majorVersion = GeneralUtility::makeInstance(Typo3Version::class)
        ->getMajorVersion();

    $extensionName = $t3majorVersion < 10
        ? 'Taketool.Sysinfo'
        : 'Sysinfo';

    if ($t3majorVersion < 10)
    {
        ExtensionUtility::registerModule(
            $extensionName,
            'tools',
            'm1',
            'top',
            [
                'Mod1' => 'index,deprecation,deprecationHide,deprecationClearHide,deprecationDelete,fileCheck,securityCheck,allTemplates,'.
                    'allTemplatesNoCache,checkDomains,deleteFile,plugins,rootTemplates,viewFile',
                'Curl'=> 'index',
                'Sha1' => 'shaOne,shaOneJs,shaOnePhp',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:sysinfo/Resources/Public/Icons/user_mod_m1.svg',
                'labels' => 'LLL:EXT:sysinfo/Resources/Private/Language/locallang_m1.xlf',
            ]
        );
    }
    if ($t3majorVersion < 12)
    {
        ExtensionUtility::registerModule(
            $extensionName,
            'tools',
            'm1',
            'top',
            [
                Mod1Controller::class => 'index,deprecation,deprecationHide,deprecationClearHide,deprecationDelete,fileCheck,syslog,syslogDelete,'.
                    'securityCheck,allTemplates,allTemplatesNoCache,checkDomains,deleteFile,plugins,rootTemplates,viewFile',
                Sha1Controller::class => 'shaOne,shaOneJs,shaOnePhp',
                CurlController::class => 'index',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:sysinfo/Resources/Public/Icons/user_mod_m1.svg',
                'labels' => 'LLL:EXT:sysinfo/Resources/Private/Language/locallang_m1.xlf',
            ]
        );
    }
})();
