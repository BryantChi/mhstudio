<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Fix: 共享主機透過 .htaccess 多層 rewrite 時，修正 SCRIPT_NAME 避免 URL 帶出內部路徑
if (isset($_SERVER['SCRIPT_NAME']) && str_contains($_SERVER['SCRIPT_NAME'], '/mhstudio/public')) {
    $_SERVER['SCRIPT_NAME'] = str_replace('/mhstudio/public', '', $_SERVER['SCRIPT_NAME']);
    $_SERVER['PHP_SELF'] = str_replace('/mhstudio/public', '', $_SERVER['PHP_SELF']);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
