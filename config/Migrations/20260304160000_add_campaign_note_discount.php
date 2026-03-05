<?php

use Migrations\AbstractMigration;

class AddCampaignNoteDiscount extends AbstractMigration
{
    public function up()
    {
        $this->table('campaigns')
            ->addColumn('discount_code', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('note', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->update();
    }

    public function down()
    {
        $this->table('campaigns')
            ->removeColumn('discount_code')
            ->removeColumn('note')
            ->update();
    }
}
