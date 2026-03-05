<?php

use Migrations\AbstractMigration;

class AddAdsterraOptions extends AbstractMigration
{
    public function up()
    {
        $rows = [
            ['name' => 'adsterra_social_bar', 'value' => ''],
            ['name' => 'adsterra_popunder', 'value' => ''],
            ['name' => 'adsterra_direct_link', 'value' => ''],
        ];
        $this->table('options')->insert($rows)->saveData();
    }

    public function down()
    {
        $this->execute("DELETE FROM options WHERE name IN (
            'adsterra_social_bar',
            'adsterra_popunder',
            'adsterra_direct_link'
        )");
    }
}
