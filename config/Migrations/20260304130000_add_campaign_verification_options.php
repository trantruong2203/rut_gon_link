<?php

use Migrations\AbstractMigration;

class AddCampaignVerificationOptions extends AbstractMigration
{
    public function up()
    {
        $rows = [
            ['name' => 'campaign_verify_retries', 'value' => '3'],
            ['name' => 'campaign_verify_connect_timeout', 'value' => '5'],
            ['name' => 'campaign_verify_request_timeout', 'value' => '12'],
            ['name' => 'campaign_verify_retry_delay_ms', 'value' => '700'],
            ['name' => 'campaign_verify_recheck_limit', 'value' => '200'],
            ['name' => 'campaign_verify_recheck_days', 'value' => '1'],
        ];

        $this->table('options')->insert($rows)->saveData();
    }

    public function down()
    {
        $this->execute("DELETE FROM options WHERE name IN (
            'campaign_verify_retries',
            'campaign_verify_connect_timeout',
            'campaign_verify_request_timeout',
            'campaign_verify_retry_delay_ms',
            'campaign_verify_recheck_limit',
            'campaign_verify_recheck_days'
        )");
    }
}
