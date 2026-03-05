<?php

use Migrations\AbstractMigration;

class CreateKeywordTasks extends AbstractMigration
{
    public function up()
    {
        $this->table('keyword_tasks')
            ->addColumn('keyword', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('target_url', 'string', [
                'default' => null,
                'limit' => 512,
                'null' => false,
            ])
            ->addColumn('ad_code', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('campaign_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('status', 'integer', [
                'default' => 1,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('sort_order', 'integer', [
                'default' => 0,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->create();
    }

    public function down()
    {
        $this->table('keyword_tasks')->drop()->save();
    }
}
