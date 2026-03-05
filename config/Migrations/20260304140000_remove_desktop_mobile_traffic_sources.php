<?php

use Migrations\AbstractMigration;

class RemoveDesktopMobileTrafficSources extends AbstractMigration
{
    public function up()
    {
        $this->execute("UPDATE campaigns SET traffic_source = 1 WHERE traffic_source IN (2, 3)");
    }

    public function down()
    {
        // Cannot restore original values
    }
}
