<?php
$this->assign('title', __('Add Keyword Task'));
$this->assign('description', '');
$this->assign('content_title', __('Add Keyword Task'));

?>

<div class="box box-primary">
    <div class="box-body">
        <?= $this->Form->create($keywordTask); ?>

        <?= $this->Form->input('keyword', [
            'label' => __('Keyword'),
            'class' => 'form-control',
            'placeholder' => __('e.g. tg88')
        ]); ?>

        <?= $this->Form->input('target_url', [
            'label' => __('Target Website URL'),
            'class' => 'form-control',
            'type' => 'url',
            'placeholder' => __('https://example.com')
        ]); ?>

        <?= $this->Form->input('ad_code', [
            'label' => __('Ad Code (optional)'),
            'class' => 'form-control',
            'type' => 'textarea',
            'rows' => 3
        ]); ?>

        <?= $this->Form->input('campaign_id', [
            'label' => __('Campaign'),
            'options' => $campaigns,
            'class' => 'form-control'
        ]); ?>

        <?= $this->Form->input('status', [
            'label' => __('Status'),
            'options' => [1 => __('Active'), 0 => __('Inactive')],
            'class' => 'form-control'
        ]); ?>

        <?= $this->Form->input('sort_order', [
            'label' => __('Sort Order'),
            'class' => 'form-control',
            'type' => 'number',
            'value' => 0
        ]); ?>

        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']); ?>
        <?= $this->Form->end(); ?>
    </div>
</div>
