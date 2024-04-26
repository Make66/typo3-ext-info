<?php
/* only needed for Typo3 v10 */


/**
 * Definitions for routes provided by EXT:backend
 * Contains all "regular" routes for entry points
 *
 * Please note that this setup is preliminary until all core use-cases are set up here.
 * Especially some more properties regarding modules will be added until TYPO3 CMS 7 LTS, and might change.
 *
 * Currently the "access" property is only used so no token creation + validation is made,
 * but will be extended further.
 * /typo3/index.php?route=/module/tools/toolsmaintenance
 */

use Taketool\Sysinfo\Controller\Mod1Controller;

return [
    'syslog' => [
        'path' => '/tools/sysinfo/syslog',
        'access' => 'admin',
        'target' => Mod1Controller::class . '::syslogAction'
    ],

];