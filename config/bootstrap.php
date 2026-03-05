<?php

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.8
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Database\Type\StringType;
use Cake\Database\TypeFactory;
use Cake\Datasource\ConnectionManager;
use Cake\Error\ErrorTrap;
use Cake\Error\ExceptionTrap;
use Cake\Http\ServerRequest;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\Routing\Router;
use Cake\Utility\Security;

require __DIR__ . '/paths.php';

// Check if tmp directory and its subdirectories are writable
$temp = [
    CONFIG,
    LOGS,
    TMP,
    TMP . DS . 'cache',
    TMP . DS . 'cache' . DS . 'models',
    TMP . DS . 'cache' . DS . 'persistent',
    TMP . DS . 'cache' . DS . 'views',
    TMP . DS . 'sessions',
    TMP . DS . 'tests'
];

foreach ($temp as $dir) {
    if (!is_writable($dir)) {
        exit("<b>{$dir}</b> directory must be writable.");
    }
}

/*
 * Read configuration file and inject configuration into various
 * CakePHP classes.
 */
try {
    Configure::config('default', new PhpConfig());
    Configure::load('app', 'default', false);
    if (file_exists(CONFIG . 'configure.php')) {
        Configure::load('configure', 'default');
    }
    if (file_exists(CONFIG . 'app_vars.php')) {
        Configure::load('app_vars', 'default');
    }
    if (file_exists(CONFIG . 'email.php')) {
        Configure::load('email', 'default');
    }
    if (file_exists(CONFIG . 'app_local.php')) {
        Configure::load('app_local', 'default');
    }
} catch (\Exception $e) {
    exit($e->getMessage() . "\n");
}

/*
 * When debug = true the metadata cache should only last for a short time.
 */
if (Configure::read('debug')) {
    Configure::write('Cache._cake_model_.duration', '+2 minutes');
    Configure::write('Cache._cake_core_.duration', '+2 minutes');
    Configure::write('Cache._cake_routes_.duration', '+2 seconds');
}

date_default_timezone_set(Configure::read('App.defaultTimezone', 'UTC'));
mb_internal_encoding(Configure::read('App.encoding', 'UTF-8'));
ini_set('intl.default_locale', Configure::read('App.defaultLocale', 'en_US'));

/*
 * Register application error and exception handlers.
 */
(new ErrorTrap(Configure::read('Error')))->register();
(new ExceptionTrap(Configure::read('Error')))->register();

/*
 * Include the CLI bootstrap overrides.
 */
if (PHP_SAPI === 'cli') {
    require CONFIG . 'bootstrap_cli.php';
}

/*
 * Set the full base URL.
 */
$fullBaseUrl = Configure::read('App.fullBaseUrl');
if (!$fullBaseUrl) {
    $trustProxy = false;
    $s = null;
    if (env('HTTPS') || ($trustProxy && env('HTTP_X_FORWARDED_PROTO') === 'https')) {
        $s = 's';
    }
    $httpHost = env('HTTP_HOST');
    if (isset($httpHost)) {
        $fullBaseUrl = 'http' . $s . '://' . $httpHost;
    }
    unset($httpHost, $s);
}
if ($fullBaseUrl) {
    Router::fullBaseUrl($fullBaseUrl);
}
unset($fullBaseUrl);

Cache::setConfig(Configure::consume('Cache'));
ConnectionManager::setConfig(Configure::consume('Datasources'));

Mailer::setConfig(Configure::consume('Email'));
Log::setConfig(Configure::consume('Log'));
Security::setSalt(Configure::consume('Security.salt'));

// App functions (must be after ConnectionManager for get_option)
include CONFIG . 'functions.php';
include CONFIG . 'app_config.php';

/*
 * Setup detectors for mobile and tablet.
 */
ServerRequest::addDetector('mobile', function ($request) {
    $detector = new \Detection\MobileDetect();
    return $detector->isMobile();
});
ServerRequest::addDetector('tablet', function ($request) {
    $detector = new \Detection\MobileDetect();
    return $detector->isTablet();
});

/*
 * Map time type for ORM compatibility.
 */
TypeFactory::map('time', StringType::class);

/*
 * Initialize Router (cần cho cả web và CLI - ErrorHandlerMiddleware gọi Router::routes())
 */
Router::reload();
