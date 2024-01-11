<?php
// this file is needed for T3 >=v11.4  In T3v10 this is done in ext_localconf.php

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'extension' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:sysinfo/Resources/Public/Icons/Extension.svg',
    ],
    'mod1' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:sysinfo/Resources/Public/Icons/user_mod_m1.svg',
    ],
];
