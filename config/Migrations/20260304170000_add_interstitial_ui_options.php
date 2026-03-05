<?php

use Migrations\AbstractMigration;

class AddInterstitialUiOptions extends AbstractMigration
{
    public function up()
    {
        $this->table('options')
            ->insert([
                ['name' => 'interstitial_video_url', 'value' => ''],
                ['name' => 'interstitial_report_error_url', 'value' => ''],
            ])
            ->saveData();
    }

    public function down()
    {
        $this->execute("DELETE FROM options WHERE name IN ('interstitial_video_url', 'interstitial_report_error_url')");
    }
}
