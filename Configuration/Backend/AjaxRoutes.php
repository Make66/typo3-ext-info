<?php

use Taketool\Sysinfo\Controller\CheckRemotePageController;

return [
    'checkRemotePage_index' => [
        'path' => '/sysinfo/checkRemotePage',
        'target' => CheckRemotePageController::class . '::indexAction',
    ],
];