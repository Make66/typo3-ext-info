<?php

use Taketool\Sysinfo\Controller\CurlController;

return [
    'sysinfo_checkpage' => [
        'path' => '/sysinfo/checkpage',
        'target' => CurlController::class . '::indexAction',
    ],
];