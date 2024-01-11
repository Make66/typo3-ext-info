<?php

use Taketool\Sysinfo\Controller\Mod1Controller;

return [
    'sysinfo_checkdomains' => [
        'path' => '/tools/sysinfo/Mod1/checkDomains',
        'target' => Mod1Controller::class . '::checkDomains',
    ],
];
