<?php

use Migrations\AbstractMigration;

class AddCampaignVerificationFields extends AbstractMigration
{
    public function up()
    {
        $this->table('campaigns')
            ->addColumn('verification_token', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('verification_status', 'integer', [
                'default' => 0,
                'limit' => 1,
                'null' => false,
            ])
            ->addColumn('verification_checked_at', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('verification_note', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['verification_status'])
            ->addIndex(['verification_token'])
            ->update();

        // Keep legacy campaigns eligible after rollout.
        $this->execute("UPDATE campaigns SET verification_status = 2 WHERE verification_status = 0");
    }

    public function down()
    {
        $this->table('campaigns')
            ->removeIndex(['verification_status'])
            ->removeIndex(['verification_token'])
            ->removeColumn('verification_token')
            ->removeColumn('verification_status')
            ->removeColumn('verification_checked_at')
            ->removeColumn('verification_note')
            ->update();
    }
}
