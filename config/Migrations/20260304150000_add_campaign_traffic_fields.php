<?php

use Migrations\AbstractMigration;

class AddCampaignTrafficFields extends AbstractMigration
{
    public function up()
    {
        $this->table('campaigns')
            ->addColumn('countdown_seconds', 'integer', [
                'default' => 60,
                'limit' => 11,
                'null' => true,
                'comment' => 'Thời gian bộ đếm ngược hiển thị code'
            ])
            ->addColumn('daily_view_limit', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
                'comment' => 'Số lượng view tối đa trong 1 ngày'
            ])
            ->addColumn('total_view_limit', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
                'comment' => 'Tổng view mua (chuyển sang ngày hôm sau nếu dư)'
            ])
            ->addColumn('keyword_or_url', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
                'comment' => 'Từ khóa (gg search) hoặc URL (backlink)'
            ])
            ->addColumn('image_1', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
                'comment' => 'Image 1: hình ảnh hướng dẫn'
            ])
            ->addColumn('image_2', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
                'comment' => 'Image 2: ảnh phụ'
            ])
            ->addColumn('anchor_mode', 'string', [
                'default' => 'default',
                'limit' => 20,
                'null' => true,
                'comment' => 'default hoặc specify'
            ])
            ->addColumn('anchor_text', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('anchor_link', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('view_by_hour', 'boolean', [
                'default' => false,
                'null' => true,
                'comment' => 'Chia view theo giờ'
            ])
            ->addColumn('campaign_version', 'integer', [
                'default' => 1,
                'limit' => 1,
                'null' => true,
                'comment' => '1=2 bước, 2=1 bước'
            ])
            ->update();
    }

    public function down()
    {
        $this->table('campaigns')
            ->removeColumn('countdown_seconds')
            ->removeColumn('daily_view_limit')
            ->removeColumn('total_view_limit')
            ->removeColumn('keyword_or_url')
            ->removeColumn('image_1')
            ->removeColumn('image_2')
            ->removeColumn('anchor_mode')
            ->removeColumn('anchor_text')
            ->removeColumn('anchor_link')
            ->removeColumn('view_by_hour')
            ->removeColumn('campaign_version')
            ->update();
    }
}
