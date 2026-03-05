<?php

use Migrations\AbstractMigration;

class AddProxyCheckProviderOptions extends AbstractMigration
{
    public function up()
    {
        $rows = [
            ['name' => 'proxy_check_provider', 'value' => 'proxycheck'],
            ['name' => 'ipinfo_token', 'value' => ''],
            ['name' => 'anti_bypass_redirect_token', 'value' => 'yes'],
            ['name' => 'anti_bypass_redirect_delay_min', 'value' => '2'],
            ['name' => 'anti_bypass_redirect_delay_max', 'value' => '5'],
        ];
        $this->table('options')->insert($rows)->saveData();
    }

    public function down()
    {
        $this->execute("DELETE FROM options WHERE name IN (
            'proxy_check_provider',
            'ipinfo_token',
            'anti_bypass_redirect_token',
            'anti_bypass_redirect_delay_min',
            'anti_bypass_redirect_delay_max'
        )");
    }
}
