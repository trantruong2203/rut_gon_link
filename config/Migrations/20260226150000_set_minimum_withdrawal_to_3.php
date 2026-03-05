<?php

use Migrations\AbstractMigration;

class SetMinimumWithdrawalTo3 extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE options SET value = '3' WHERE name = 'minimum_withdrawal_amount' AND value = '5'");
    }

    public function down()
    {
        $this->execute("UPDATE options SET value = '5' WHERE name = 'minimum_withdrawal_amount' AND value = '3'");
    }
}
