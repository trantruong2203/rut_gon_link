<?php

use Migrations\AbstractMigration;

class AddProxycheckRateLimitOptions extends AbstractMigration
{
    public function up()
    {
        $rows = [
            ['name' => 'proxycheck_api_key', 'value' => ''],
            ['name' => 'enable_proxycheck', 'value' => 'no'],
            ['name' => 'proxycheck_block_url', 'value' => ''],
            ['name' => 'rate_limit_views_per_link_day', 'value' => '2'],
            ['name' => 'enable_anti_bypass', 'value' => 'yes'],
        ];
        $this->table('options')->insert($rows)->saveData();
    }

    public function down()
    {
        $this->execute("DELETE FROM options WHERE name IN (
            'proxycheck_api_key',
            'enable_proxycheck',
            'proxycheck_block_url',
            'rate_limit_views_per_link_day',
            'enable_anti_bypass'
        )");
    }
}
