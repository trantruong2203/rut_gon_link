<?php
$baseDir = dirname(dirname(__FILE__));

return [
    'plugins' => [
        'ADmad/SocialAuth' => $baseDir . '/vendor/admad/cakephp-social-auth/',
        'ClassicTheme' => $baseDir . '/plugins/ClassicTheme/',
        'CloudTheme' => $baseDir . '/plugins/CloudTheme/',
        'Migrations' => $baseDir . '/vendor/cakephp/migrations/',
        'ModernTheme' => $baseDir . '/plugins/ModernTheme/',
    ],
];
