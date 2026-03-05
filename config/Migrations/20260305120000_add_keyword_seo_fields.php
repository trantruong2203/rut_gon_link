<?php

use Migrations\AbstractMigration;

class AddKeywordSeoFields extends AbstractMigration
{
    public function up()
    {
        $this->table('campaigns')
            ->addColumn('keyword_seo_code', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
                'comment' => 'Mã 6 số của chiến dịch SEO'
            ])
            ->addColumn('keyword_seo_status', 'string', [
                'default' => 'pending',
                'limit' => 20,
                'null' => true,
                'comment' => 'pending, running, completed, stopped'
            ])
            ->addColumn('seo_target_views', 'integer', [
                'default' => 1000,
                'limit' => 11,
                'null' => true,
                'comment' => 'Target view (vd: 1000)'
            ])
            ->addColumn('seo_current_views', 'integer', [
                'default' => 0,
                'limit' => 11,
                'null' => true,
                'comment' => 'View hiện tại'
            ])
            ->addColumn('seo_wait_seconds', 'integer', [
                'default' => 60,
                'limit' => 11,
                'null' => true,
                'comment' => 'Thời gian chờ (60/90/120/200)'
            ])
            ->addColumn('seo_image_1', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
                'comment' => 'Ảnh hướng dẫn 1'
            ])
            ->addColumn('seo_image_2', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
                'comment' => 'Ảnh hướng dẫn 2'
            ])
            ->addColumn('seo_price_usd', 'float', [
                'default' => 80,
                'precision' => 50,
                'scale' => 6,
                'null' => true,
                'comment' => 'Giá bán USD'
            ])
            ->addIndex('keyword_seo_code', ['name' => 'idx_keyword_seo_code'])
            ->addIndex('keyword_seo_status', ['name' => 'idx_keyword_seo_status'])
            ->update();
    }

    public function down()
    {
        $this->table('campaigns')
            ->removeColumn('keyword_seo_code')
            ->removeColumn('keyword_seo_status')
            ->removeColumn('seo_target_views')
            ->removeColumn('seo_current_views')
            ->removeColumn('seo_wait_seconds')
            ->removeColumn('seo_image_1')
            ->removeColumn('seo_image_2')
            ->removeColumn('seo_price_usd')
            ->update();
    }
}
