<?php

use Migrations\AbstractMigration;

class AddFinalAdOptions extends AbstractMigration
{
    public function up()
    {
        $rows = [
            ['name' => 'final_ad_enabled', 'value' => 'no'],
            ['name' => 'final_ad_url', 'value' => ''],
            ['name' => 'final_ad_delay_seconds', 'value' => '5'],
        ];
        $this->table('options')->insert($rows)->saveData();
    }

    public function down()
    {
        $this->execute("DELETE FROM options WHERE name IN (
            'final_ad_enabled',
            'final_ad_url',
            'final_ad_delay_seconds'
        )");
    }
}
