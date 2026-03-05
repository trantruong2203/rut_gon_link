<?php

use Migrations\AbstractMigration;

class AddLandingOptions extends AbstractMigration
{
    public function up()
    {
        $rows = [
            ['name' => 'landing_wait_seconds', 'value' => '60'],
            ['name' => 'landing_brand', 'value' => ''],
            ['name' => 'interstitial_website_name', 'value' => ''],
        ];
        $this->table('options')->insert($rows)->saveData();
    }

    public function down()
    {
        $this->execute("DELETE FROM options WHERE name IN ('landing_wait_seconds', 'landing_brand', 'interstitial_website_name')");
    }
}
