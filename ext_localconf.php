<?php


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {

        ExtensionManagementUtility::addTypoScript(
            'sysinfo',
            'constants',
            '@import \'EXT:sysinfo/Configuration/TypoScript/setup.typoscript\'');
        ExtensionManagementUtility::addTypoScript(
            'sysinfo',
            'setup',
            '@import \'EXT:sysinfo/Configuration/TypoScript/setup.typoscript\'');
    }
);