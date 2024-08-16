<?php
defined('TYPO3') || die();

(static function () {

    // with T3v12.3 - Feature: #100232 - Load additional stylesheets in TYPO3 backend
    $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['sysinfo'] = 'EXT:sysinfo/Resources/Public/Css/Backend.css';

})();