<?php
$this->assign('title', __('SEO Campaigns'));
$this->assign('description', '');
$this->assign('content_title', __('SEO Campaigns'));

$seoStatuses = [
    'pending' => __('Pending'),
    'running' => __('Running'),
    'completed' => __('Completed'),
    'stopped' => __('Stopped')
];

$waitOptions = [
    60 => __('60 seconds - $80'),
    90 => __('90 seconds - $120'),
    120 => __('120 seconds - $150'),
    200 => __('200 seconds - $200')
];

?>

<div class="box box-solid">
    <div class="box-header">
        <i class="fa fa-search"></i>
        <h3 class="box-title"><?= __('Filters') ?></h3>
    </div>
    <div class="box-body">
        <?php
        $base_url = ['controller' => 'KeywordSeo', 'action' => 'index'];
        
        echo $this->Form->create(null, [
            'url' => $base_url,
            'class' => 'form-inline'
        ]);
        ?>
        
        <?=
        $this->Form->input('Filter.id', [
            'label' => false,
            'class' => 'form-control',
            'type' => 'text',
            'placeholder' => __('ID')
        ]);
        ?>
        
        <?=
        $this->Form->input('Filter.name', [
            'label' => false,
            'class' => 'form-control',
            'type' => 'text',
            'placeholder' => __('Campaign Name')
        ]);
        ?>
        
        <?=
        $this->Form->input('Filter.keyword_seo_status', [
            'label' => false,
            'options' => $seoStatuses,
            'empty' => __('Status'),
            'class' => 'form-control'
        ]);
        ?>
        
        <?= $this->Form->button(__('Filter'), ['class' => 'btn btn-default']); ?>
        
        <?= $this->Html->link(__('Reset'), $base_url, ['class' => 'btn btn-default']); ?>
        
        <?= $this->Form->end() ?>
    </div>
</div>

<div class="box box-solid">
    <div class="box-header">
        <i class="fa fa-globe"></i>
        <h3 class="box-title"><?= __('SEO Campaigns') ?></h3>
        <?= $this->Html->link(__('Add New'), ['action' => 'add'], ['class' => 'btn btn-primary pull-right']) ?>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?= __('ID') ?></th>
                    <th><?= __('Name') ?></th>
                    <th><?= __('Code') ?></th>
                    <th><?= __('Target URL') ?></th>
                    <th><?= __('Keyword') ?></th>
                    <th><?= __('Views') ?></th>
                    <th><?= __('Status') ?></th>
                    <th><?= __('Created') ?></th>
                    <th><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campaigns as $campaign): ?>
                <tr>
                    <td><?= $campaign->id ?></td>
                    <td><?= h($campaign->name) ?></td>
                    <td>
                        <span class="label label-success" style="font-size: 14px;">
                            <?= h($campaign->keyword_seo_code) ?>
                        </span>
                    </td>
                    <td><?= $this->Text->truncate(h($campaign->website_url), 30) ?></td>
                    <td><?= h($campaign->keyword_or_url) ?></td>
                    <td>
                        <?= $campaign->seo_current_views ?> / <?= $campaign->seo_target_views ?>
                        <div class="progress progress-xs">
                            <?php $percent = $campaign->seo_target_views > 0 ? ($campaign->seo_current_views / $campaign->seo_target_views) * 100 : 0; ?>
                            <div class="progress-bar progress-bar-primary" style="width: <?= $percent ?>%"></div>
                        </div>
                    </td>
                    <td>
                        <?php
                        $statusClass = 'default';
                        $statusText = $campaign->keyword_seo_status;
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
                        <span class="label label-<?= $statusClass ?>"><?= __(ucfirst($statusText)) ?></span>
                    </td>
                    <td><?= $campaign->created->format('Y-m-d') ?></td>
                    <td>
                        <?= $this->Html->link('<i class="fa fa-eye"></i>', ['action' => 'view', $campaign->id], ['escape' => false, 'class' => 'btn btn-sm btn-default', 'title' => __('View')]) ?>
                        <?= $this->Html->link('<i class="fa fa-edit"></i>', ['action' => 'edit', $campaign->id], ['escape' => false, 'class' => 'btn btn-sm btn-default', 'title' => __('Edit')]) ?>
                        <?php if ($campaign->keyword_seo_status == 'running'): ?>
                            <?= $this->Form->postLink('<i class="fa fa-stop"></i>', ['action' => 'stop', $campaign->id], ['escape' => false, 'class' => 'btn btn-sm btn-warning', 'title' => __('Stop'), 'confirm' => __('Are you sure?')]) ?>
                        <?php elseif ($campaign->keyword_seo_status != 'completed'): ?>
                            <?= $this->Form->postLink('<i class="fa fa-play"></i>', ['action' => 'start', $campaign->id], ['escape' => false, 'class' => 'btn btn-sm btn-success', 'title' => __('Start'), 'confirm' => __('Are you sure?')]) ?>
                        <?php endif; ?>
                        <?= $this->Form->postLink('<i class="fa fa-trash"></i>', ['action' => 'delete', $campaign->id], ['escape' => false, 'class' => 'btn btn-sm btn-danger', 'title' => __('Delete'), 'confirm' => __('Are you sure?')]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($campaigns->hasPrevPage() || $campaigns->hasNextPage()): ?>
<div class="box box-solid">
    <div class="box-body">
        <div class="pagination pagination-sm">
            <?= $this->Paginator->numbers() ?>
        </div>
    </div>
</div>
<?php endif; ?>
