<?php

use Migrations\AbstractMigration;

/**
 * Add columns required by ADmad/SocialAuth plugin to existing social_profiles table.
 * The table was created by HybridAuth - this adds missing columns for SocialAuth compatibility.
 */
class AddSocialauthColumnsToSocialProfiles extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('social_profiles');

        if (!$table->hasColumn('access_token')) {
            $table->addColumn('access_token', 'blob', [
                'default' => null,
                'null' => true,
            ])->update();
        }

        if (!$table->hasColumn('username')) {
            $table->addColumn('username', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])->update();
        }

        if (!$table->hasColumn('full_name')) {
            $table->addColumn('full_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])->update();
        }

        if (!$table->hasColumn('picture_url')) {
            $table->addColumn('picture_url', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])->update();
        }

        if (!$table->hasColumn('birth_date')) {
            $table->addColumn('birth_date', 'date', [
                'default' => null,
                'null' => true,
            ])->update();
        }
    }

    public function down()
    {
        $table = $this->table('social_profiles');
        $table->removeColumn('access_token')
            ->removeColumn('username')
            ->removeColumn('full_name')
            ->removeColumn('picture_url')
            ->removeColumn('birth_date')
            ->update();
    }
}
