<?php

use Migrations\AbstractMigration;

class AddTrafficSourcesToUsers extends AbstractMigration
{
    public function up()
    {
        $this->table('users')
            ->addColumn('traffic_sources', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->update();
    }

    public function down()
    {
        $this->table('users')
            ->removeColumn('traffic_sources')
            ->update();
    }
}
