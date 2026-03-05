<?php
$this->assign('title', __('Edit Campaign'));
$this->assign('description', '');
$this->assign('content_title', __('Edit Campaign'));
?>

<div class="box box-primary">
    <div class="box-body">
        <?= $this->Form->create($campaign, ['id' => 'campaign-edit-form', 'type' => 'post', 'url' => ['controller' => 'Campaigns', 'action' => 'edit', $campaign->id, 'prefix' => 'Admin']]); ?>
        <?php
        // Unlock campaign_items để tránh FormProtection "Tampered field" (giá tính từ DB)
        foreach ($campaign->campaign_items ?? [] as $key => $ci) {
            $this->Form->unlockField("campaign_items.$key.id");
            $this->Form->unlockField("campaign_items.$key.country");
            $this->Form->unlockField("campaign_items.$key.purchase");
            $this->Form->unlockField("campaign_items.$key.advertiser_price");
            $this->Form->unlockField("campaign_items.$key.publisher_price");
        }
        ?>

        <label><?= $this->Form->checkbox('default_campaign') ?> <?= __('Default Campaign') ?></label>
        <span class="help-block"><?= __('Default = campaign dự phòng khi không có campaign khác. Tất cả campaign đều trả tiền. Nhiều campaign sẽ xoay vòng.') ?></span>
        
        <?=
        $this->Form->input('user_id', [
            'label' => __('User'),
            'options' => $users,
            'empty' => __( 'Choose' ),
            'class' => 'form-control'
        ]);

        ?>
        
        <?=
        $this->Form->input('name', [
            'label' => __('Campaign Name'),
            'class' => 'form-control'
        ]);

        ?>
        
        <?=
        $this->Form->input('status', [
            'label' => __('Status'),
            'options' => [
                1 => __('Active'),
                2 => __('Paused'),
                3 => __('Canceled'),
                4 => __('Finished'),
                5 => __('Under Review'),
                6 => __('Pending Payment'),
                7 => __('Invalid Payment'),
                8 => __('Refunded')
            ],
            'empty' => __('Choose'),
            'class' => 'form-control'
        ]);

        ?>

        <legend><?= __('Verification') ?></legend>
        <p class="help-block"><?= __('Only verified campaigns can be activated.') ?></p>

        <?=
        $this->Form->input('verification_status', [
            'label' => __('Verification Status'),
            'options' => \App\Service\CampaignVerificationService::getVerificationStatuses(),
            'class' => 'form-control'
        ]);
        ?>

        <?=
        $this->Form->input('verification_token', [
            'label' => __('Verification Token'),
            'class' => 'form-control',
            'readonly' => true
        ]);
        ?>

        <?=
        $this->Form->input('verification_note', [
            'label' => __('Verification Note'),
            'class' => 'form-control',
            'type' => 'textarea'
        ]);
        ?>
        
        <?php if( $campaign->ad_type == 1 || $campaign->ad_type == 2 || $campaign->ad_type == 3 ) : ?>
        <legend><?= __('Website Details') ?></legend>
        
        <?=
        $this->Form->input('website_title', [
            'label' => __('Website Title'),
            'class' => 'form-control'
        ]);

        ?>

        <?=
        $this->Form->input('website_url', [
            'label' => __('Website URL'),
            'class' => 'form-control',
            'type' => 'url'
        ]);

        ?>

        <?php endif; ?>

        <?php if ($campaign->ad_type == 1) : ?>
        <legend><?= __('Interstitial Settings') ?></legend>
        <?= $this->Form->input('traffic_source', [
            'label' => __('Traffic Source'),
            'options' => get_traffic_source_options(),
            'empty' => __('Choose'),
            'class' => 'form-control'
        ]); ?>
        <?= $this->Form->input('campaign_version', [
            'label' => __('Version'),
            'options' => get_campaign_version_options(),
            'class' => 'form-control'
        ]); ?>
        <?= $this->Form->input('countdown_seconds', [
            'label' => __('Countdown (seconds)'),
            'options' => get_countdown_options(),
            'empty' => __('Choose'),
            'class' => 'form-control'
        ]); ?>
        <?= $this->Form->input('daily_view_limit', ['label' => __('Daily view limit'), 'class' => 'form-control', 'type' => 'number']); ?>
        <?= $this->Form->input('total_view_limit', ['label' => __('Total view limit'), 'class' => 'form-control', 'type' => 'number']); ?>
        <label><?= $this->Form->checkbox('view_by_hour'); ?> <?= __('View theo giờ') ?></label>
        <?= $this->Form->input('keyword_or_url', ['label' => __('Từ khóa hoặc URL'), 'class' => 'form-control']); ?>
        <?= $this->Form->input('anchor_mode', [
            'label' => __('Anchors'),
            'options' => ['default' => __('Mặc định'), 'specify' => __('Chỉ định')],
            'class' => 'form-control'
        ]); ?>
        <?= $this->Form->input('anchor_text', ['label' => __('Anchor text'), 'class' => 'form-control']); ?>
        <?= $this->Form->input('anchor_link', ['label' => __('Anchor link'), 'class' => 'form-control', 'type' => 'url']); ?>
        <?= $this->Form->input('discount_code', ['label' => __('Mã giảm giá'), 'class' => 'form-control']); ?>
        <?= $this->Form->input('note', ['label' => __('Ghi chú'), 'class' => 'form-control', 'type' => 'textarea']); ?>
        <?php if (!empty($campaign->image_1)) : ?>
        <p><?= __('Image 1:') ?> <a href="<?= $this->Url->build('/' . $campaign->image_1) ?>" target="_blank"><?= h($campaign->image_1) ?></a></p>
        <?php endif; ?>
        <?php if (!empty($campaign->image_2)) : ?>
        <p><?= __('Image 2:') ?> <a href="<?= $this->Url->build('/' . $campaign->image_2) ?>" target="_blank"><?= h($campaign->image_2) ?></a></p>
        <?php endif; ?>
        <?php endif; ?>

        <?php if( $campaign->ad_type == 2 ) : ?>
        <legend><?= __('Banner Details') ?></legend>
        
        <?=
        $this->Form->input('banner_name', [
            'label' => __('Banner Name'),
            'class' => 'form-control'
        ]);

        ?>
        <span class="help-block"><?= __('(only for internal use)') ?></span>

        <?=
        $this->Form->input('banner_size', [
            'label' => __('Banner Size'),
            'options' => [
                '728x90' => __('Leaderboard - 728x90'),
                '468x60' => __('Full banner - 468x60'),
                '336x280' => __('Large rectangle - 336x280')
            ],
            'empty' => __('Choose'),
            'class' => 'form-control'
        ]);

        ?>
        
        <?=
        $this->Form->input('banner_code', [
            'label' => __('Banner Code'),
            'class' => 'form-control',
            'type' => 'textarea'
        ]);

        ?>
        <span class="help-block"><?= __('(can be either HTML or JavaScript and must comply with our rules)') ?></span>

        <?php endif; ?>
        
        <?php if ($campaign->ad_type != 1) : ?>
        <legend><?= __('Traffic Sources') ?></legend>
        <?= $this->Form->input('traffic_source', [
            'label' => __('Traffic Sources'),
            'options' => get_traffic_source_options(),
            'empty' => __('Choose'),
            'class' => 'form-control'
        ]); ?>
        <?php endif; ?>
        
        <?php if ($campaign->ad_type == 1) : ?>
        <legend><?= __('Giá dịch vụ (Interstitial)') ?></legend>
        <p class="help-block"><?= __('View từ đâu cũng tính. Giá theo Tổng view + Countdown. Chia 50-50 với member.') ?></p>
        <?php
        $totalViews = 0;
        foreach ($campaign->campaign_items as $ci) {
            $totalViews += ($ci->purchase ?? 0) * 1000;
        }
        $totalViews = $totalViews ?: ($campaign->total_view_limit ?? 0);
        $calc = calc_interstitial_total_price($totalViews, $campaign->countdown_seconds ?? 60, $campaign->campaign_version ?? 1);
        ?>
        <div class="well">
            <p><strong><?= __('Tổng view') ?>:</strong> <?= number_format($totalViews) ?></p>
            <p><strong><?= __('Countdown') ?>:</strong> <?= h($campaign->countdown_seconds ?? 60) ?>s</p>
            <p><strong><?= __('Giá / 1.000') ?>:</strong> <?= display_price_currency($calc['advertiser_price']) ?> (Member: <?= display_price_currency($calc['publisher_price']) ?>)</p>
        </div>
        <?php foreach ($campaign->campaign_items as $key => $campaign_item) : ?>
            <?= $this->Form->hidden("campaign_items.$key.id"); ?>
            <?= $this->Form->hidden("campaign_items.$key.country", ['value' => $campaign_item->country ?? 'all']); ?>
            <?= $this->Form->hidden("campaign_items.$key.purchase", ['value' => $campaign_item->purchase]); ?>
            <?= $this->Form->hidden("campaign_items.$key.advertiser_price", ['value' => $campaign_item->advertiser_price]); ?>
            <?= $this->Form->hidden("campaign_items.$key.publisher_price", ['value' => $campaign_item->publisher_price]); ?>
        <?php endforeach; ?>
        <?php else : ?>
        <legend><?= __('Advertising Rates') ?></legend>
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th><?= __('Country') ?></th>
                    <th><?= __('Advertiser Price / 1,000') ?></th>
                    <th><?= __('Publisher Price / 1,000') ?></th>
                    <th><?= __('Purchase') ?></th>
                </tr>
            </thead>
            <?php foreach ($campaign->campaign_items as $key => $campaign_item) : ?>
                <tr>
                    <td><?= $this->Form->hidden("campaign_items.$key.id"); ?><pre><?= h($campaign_item->country) ?></pre></td>
                    <td><pre><?= h($campaign_item->advertiser_price) ?></pre></td>
                    <td><?= $this->Form->input("campaign_items.$key.publisher_price", ['label' => false, 'class' => 'form-control', 'type' => 'text']); ?></td>
                    <td><pre><?= h($campaign_item->purchase) ?></pre></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
        
        <button type="submit" class="btn btn-success btn-lg"><?= h(__('Update Campaign')) ?></button>
        <?= $this->Form->end(); ?>
        
        <?= $this->Form->postLink(
            __('Check Verification Now'),
            ['action' => 'checkVerification', $campaign->id],
            ['confirm' => __('Run verification check now?'), 'class' => 'btn btn-info btn-sm', 'style' => 'margin-left: 10px;']
        ); ?>
    </div><!-- /.box-body -->
</div>




