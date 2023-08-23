<?php

use Taketool\Sysinfo\Controller\CheckRemotePageController;

return [
    'checkRemotePage_checkPage' => [
        'path' => '/sysinfo/checkRemotePage',
        'target' => CheckRemotePageController::class . '::checkPageAction',
    ],
];