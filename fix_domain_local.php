<?php
/**
 * Fix domain settings - reset to localhost for local development.
 * Run: php fix_domain_local.php
 * 
 * Use when you accidentally set main_domain to production (e.g. adlinkfly.com)
 * and can no longer access localhost.
 */
$root = dirname(__FILE__);
require $root . '/vendor/autoload.php';
require $root . '/config/bootstrap.php';

$connection = \Cake\Datasource\ConnectionManager::get('default');

$updates = [
    'main_domain' => '',
    'default_short_domain' => '',
    'multi_domains' => '',
];

foreach ($updates as $name => $value) {
    $connection->execute(
        'UPDATE options SET value = :value WHERE name = :name',
        ['value' => $value, 'name' => $name]
    );
    echo "Updated {$name} = '{$value}'\n";
}

echo "\nDone! Domain settings reset. You can now access localhost.\n";
echo "Clear main_domain = no redirect. Short URLs will use HTTP_HOST (localhost).\n";
