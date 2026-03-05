<?php
$this->assign('title', __('View SEO Campaign'));
$this->assign('description', '');
$this->assign('content_title', __('View SEO Campaign'));

$seoStatuses = [
    'pending' => __('Pending'),
    'running' => __('Running'),
    'completed' => __('Completed'),
    'stopped' => __('Stopped')
];

$waitOptions = [
    60 => '60s',
    90 => '90s',
    120 => '120s',
    200 => '200s'
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title"><?= __('Campaign Details') ?></h3>
                <?php if ($campaign->keyword_seo_status == 'running'): ?>
                    <?= $this->Form->postLink(__('Stop Campaign'), ['action' => 'stop', $campaign->id], ['class' => 'btn btn-warning pull-right', 'confirm' => __('Are you sure?')]) ?>
                <?php elseif ($campaign->keyword_seo_status != 'completed'): ?>
                    <?= $this->Form->postLink(__('Start Campaign'), ['action' => 'start', $campaign->id], ['class' => 'btn btn-success pull-right', 'confirm' => __('Are you sure?')]) ?>
                <?php endif; ?>
                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $campaign->id], ['class' => 'btn btn-default pull-right', 'style' => 'margin-right: 10px;']) ?>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <tr>
                        <th><?= __('ID') ?></th>
                        <td><?= $campaign->id ?></td>
                    </tr>
                    <tr>
                        <th><?= __('Campaign Name') ?></th>
                        <td><?= h($campaign->name) ?></td>
                    </tr>
                    <tr>
                        <th><?= __('SEO Code') ?></th>
                        <td>
                            <span class="label label-success" style="font-size: 18px;">
                                <?= h($campaign->keyword_seo_code) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?= __('Target URL') ?></th>
                        <td><?= $this->Html->link(h($campaign->website_url), h($campaign->website_url), ['target' => '_blank']) ?></td>
                    </tr>
                    <tr>
                        <th><?= __('Keyword') ?></th>
                        <td><?= h($campaign->keyword_or_url) ?></td>
                    </tr>
                    <tr>
                        <th><?= __('Status') ?></th>
                        <td>
                            <?php
                            $statusClass = 'default';
                            switch ($campaign->keyword_seo_status) {
                                case 'running':
                                    $statusClass = 'success';
                                    break;
                                case 'completed':
                                    $statusClass = 'info';
                                    break;
                                case 'stopped':
                                    $statusClass = 'danger';
                                    break;
                            }
                            ?>
                            <span class="label label-<?= $statusClass ?>"><?= __(ucfirst($campaign->keyword_seo_status)) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><?= __('Waiting Time') ?></th>
                        <td><?= $waitOptions[$campaign->seo_wait_seconds] ?? $campaign->seo_wait_seconds ?>s</td>
                    </tr>
                    <tr>
                        <th><?= __('Views') ?></th>
                        <td>
                            <?= $campaign->seo_current_views ?> / <?= $campaign->seo_target_views ?>
                            <div class="progress progress-xs progress-striped active">
                                <div class="progress-bar progress-bar-primary" style="width: <?= $progress ?>%"></div>
                            </div>
                            <?= $progress ?>% completed
                        </td>
                    </tr>
                    <tr>
                        <th><?= __('Price (USD)') ?></th>
                        <td>$<?= number_format($campaign->seo_price_usd, 2) ?></td>
                    </tr>
                    <tr>
                        <th><?= __('Created') ?></th>
                        <td><?= $campaign->created->format('Y-m-d H:i:s') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header">
                <h3 class="box-title"><?= __('Hướng dẫn Image 1') ?></h3>
            </div>
            <div class="box-body">
                <?php if (!empty($campaign->seo_image_1)): ?>
                    <img src="<?= h($campaign->seo_image_1) ?>" class="img-responsive">
                <?php else: ?>
                    <p class="text-muted"><?= __('No image uploaded') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header">
                <h3 class="box-title"><?= __('Hướng dẫn Image 2') ?></h3>
            </div>
            <div class="box-body">
                <?php if (!empty($campaign->seo_image_2)): ?>
                    <img src="<?= h($campaign->seo_image_2) ?>" class="img-responsive">
                <?php else: ?>
                    <p class="text-muted"><?= __('No image uploaded') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header">
                <h3 class="box-title"><?= __('Danger Zone') ?></h3>
            </div>
            <div class="box-body">
                <?= $this->Form->postLink(__('Delete Campaign'), ['action' => 'delete', $campaign->id], ['class' => 'btn btn-danger', 'confirm' => __('Are you sure? This action cannot be undone.')]) ?>
                <?php if ($campaign->keyword_seo_status != 'completed'): ?>
                    <?= $this->Form->postLink(__('Reset Views'), ['action' => 'reset', $campaign->id], ['class' => 'btn btn-warning', 'style' => 'margin-left: 10px;', 'confirm' => __('Are you sure you want to reset view count?')]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
