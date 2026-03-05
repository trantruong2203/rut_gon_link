<?php

use Migrations\AbstractMigration;

class AddKycColumnsToUsers extends AbstractMigration
{
    public function up()
    {
        $this->table('users')
            ->addColumn('traffic_source', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('kyc_status', 'string', [
                'default' => 'pending',
                'limit' => 50,
                'null' => false,
            ])
            ->update();
    }

    public function down()
    {
        $this->table('users')
            ->removeColumn('traffic_source')
            ->removeColumn('kyc_status')
            ->update();
    }
}
