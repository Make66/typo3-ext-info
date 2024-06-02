<?php

return [
    'sysinfo_curl' => [
        'path' => '/sysinfo/curl/checkDomains',
        'target' => \Taketool\Sysinfo\Controller\CurlController::class . '::indexAction',
    ],
];
