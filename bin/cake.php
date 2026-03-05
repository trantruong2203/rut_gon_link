#!/usr/bin/php -q
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
 * @since         2.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

$minVersion = '8.1.0';
if (file_exists(dirname(__DIR__) . '/composer.json')) {
    $composer = json_decode(file_get_contents(dirname(__DIR__) . '/composer.json'), true);
    if (isset($composer['require']['php'])) {
        $minVersion = preg_replace('/([^0-9\.])/', '', $composer['require']['php']);
    }
}
if (version_compare(phpversion(), $minVersion, '<')) {
    fwrite(STDERR, sprintf("Minimum PHP version: %s. You are using: %s.\n", $minVersion, phpversion()));
    exit(1);
}

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application;
use Cake\Console\CommandRunner;

$app = new Application(dirname(__DIR__) . '/config');
$runner = new CommandRunner($app, 'cake');
exit($runner->run($argv));
