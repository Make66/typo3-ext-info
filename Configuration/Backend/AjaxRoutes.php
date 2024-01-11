<?php

return [
    'sysinfo_curl' => [
        'path' => '/sysinfo/checksite',
        'target' => Taketool\Sysinfo\Controller\CurlController::class . '::indexAction',
    ],
];
