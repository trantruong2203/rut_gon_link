<?php

use Migrations\AbstractMigration;

class AddInterstitialLink4mOptions extends AbstractMigration
{
    public function up()
    {
        $rows = [
            ['name' => 'interstitial_recaptcha', 'value' => 'yes'],
            ['name' => 'interstitial_session_time', 'value' => '600'],
            ['name' => 'interstitial_instruction_header', 'value' => 'HƯỚNG DẪN LẤY MÃ - TÌM KIẾM TỪ KHOÁ'],
            ['name' => 'interstitial_instruction_note', 'value' => 'Lưu ý: Vui lòng làm theo đúng hướng dẫn để không bị sai MÃ'],
            ['name' => 'interstitial_search_keyword', 'value' => 'Newgoal'],
            ['name' => 'interstitial_instruction_steps', 'value' => "Bước 1: Mở tab mới, truy cập google.com\nBước 2: Gõ tìm từ khóa Newgoal"],
        ];
        $this->table('options')->insert($rows)->saveData();
    }

    public function down()
    {
        $this->execute("DELETE FROM options WHERE name IN (
            'interstitial_recaptcha', 'interstitial_session_time', 'interstitial_instruction_header',
            'interstitial_instruction_note', 'interstitial_search_keyword', 'interstitial_instruction_steps'
        )");
    }
}
