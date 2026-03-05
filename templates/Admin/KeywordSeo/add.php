<?php
$this->assign('title', __('Add SEO Campaign'));
$this->assign('description', '');
$this->assign('content_title', __('Add SEO Campaign'));

$waitOptions = [
    60 => __('60 seconds - $80'),
    90 => __('90 seconds - $120'),
    120 => __('120 seconds - $150'),
    200 => __('200 seconds - $200')
];
?>

<div class="box box-primary">
    <div class="box-body">
        <?= $this->Form->create($campaign, ['type' => 'file']); ?>

        <?= $this->Form->control('name', [
            'label' => __('Campaign Name') . ' *',
            'class' => 'form-control',
            'required' => true
        ]); ?>

        <?= $this->Form->control('website_url', [
            'label' => __('Target Website URL') . ' *',
            'class' => 'form-control',
            'placeholder' => 'https://example.com',
            'required' => true
        ]); ?>

        <?= $this->Form->control('keyword_or_url', [
            'label' => __('Google Keyword') . ' *',
            'class' => 'form-control',
            'placeholder' => 'e.g. "buy instagram followers" or "niche relevant keywords"',
            'required' => true
        ]); ?>
        <p class="help-block"><?= __('Từ khóa để người dùng tìm kiếm trên Google') ?></p>

        <?= $this->Form->control('seo_wait_seconds', [
            'label' => __('Waiting Time') . ' *',
            'options' => $waitOptions,
            'empty' => __('Select waiting time'),
            'class' => 'form-control',
            'required' => true,
            'value' => 60
        ]); ?>
        <p class="help-block"><?= __('Thời gian người dùng phải ở trên trang web mới tính view') ?></p>

        <?= $this->Form->control('seo_target_views', [
            'label' => __('Target Views') . ' *',
            'type' => 'number',
            'class' => 'form-control',
            'value' => 1000,
            'min' => 100,
            'required' => true
        ]); ?>
        <p class="help-block"><?= __('Số view cần đạt được (1000 view = 1 chỉ tiêu)') ?></p>

        <?= $this->Form->control('seo_price_usd', [
            'label' => __('Price (USD)') . ' *',
            'type' => 'number',
            'class' => 'form-control',
            'value' => 80,
            'min' => 1,
            'required' => true
        ]); ?>
        <p class="help-block"><?= __('Giá bán cho khách hàng (USD)') ?></p>

        <legend><?= __('Hướng dẫn tìm mã') ?></legend>

        <?= $this->Form->control('seo_image_1', [
            'label' => __('Hướng dẫn Image 1'),
            'type' => 'file',
            'class' => 'form-control'
        ]); ?>
        <p class="help-block"><?= __('Upload hình ảnh hướng dẫn cách tìm mã trên website (khuyến nghị: 800x600px)') ?></p>

        <?= $this->Form->control('seo_image_2', [
            'label' => __('Hướng dẫn Image 2'),
            'type' => 'file',
            'class' => 'form-control'
        ]); ?>
        <p class="help-block"><?= __('Upload thêm hình ảnh hướng dẫn (khuyến nghị: 800x600px)') ?></p>

        <?= $this->Form->button(__('Create Campaign'), ['class' => 'btn btn-primary']); ?>

        <?= $this->Form->end() ?>
    </div>
</div>
