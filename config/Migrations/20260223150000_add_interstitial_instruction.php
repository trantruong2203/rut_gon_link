<?php

use Migrations\AbstractMigration;

class AddInterstitialInstruction extends AbstractMigration
{
    public function up()
    {
        $instruction = 'Your code is displayed below. Enter it in the box to continue to your destination.';
        $this->table('options')
            ->insert([
                'name' => 'interstitial_instruction',
                'value' => $instruction
            ])
            ->saveData();
    }

    public function down()
    {
        $this->execute("DELETE FROM options WHERE name = 'interstitial_instruction'");
    }
}
