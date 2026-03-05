<?php
$this->assign('title', __('SEO Tasks'));
$this->assign('description', '');
$this->assign('content_title', __('SEO Tasks'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title"><?= __('Available SEO Tasks') ?></h3>
            </div>
            <div class="box-body">
                <?php if (empty($campaigns)): ?>
                    <div class="alert alert-info">
                        <i class="icon fa fa-info"></i>
                        <?= __('No SEO tasks available at the moment. Please check back later.') ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($campaigns as $campaign): ?>
                        <div class="col-md-6">
                            <div class="box box-widget widget-user-2">
                                <div class="widget-user-header bg-yellow">
                                    <h3 class="widget-user-username"><?= h($campaign->name) ?></h3>
                                    <h5 class="widget-user-desc">
                                        <i class="fa fa-link"></i> 
                                        <?= $this->Text->truncate(h($campaign->website_url), 40) ?>
                                    </h5>
                                </div>
                                <div class="box-footer no-padding">
                                    <ul class="nav nav-stacked">
                                        <li>
                                            <a href="#">
                                                <i class="fa fa-search"></i> <?= __('Keyword') ?>
                                                <span class="pull-right"><?= h($campaign->keyword_or_url) ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <i class="fa fa-clock-o"></i> <?= __('Wait Time') ?>
                                                <span class="pull-right"><?= $campaign->seo_wait_seconds ?>s</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <i class="fa fa-eye"></i> <?= __('Views') ?>
                                                <span class="pull-right">
                                                    <?= $campaign->seo_current_views ?> / <?= $campaign->seo_target_views ?>
                                                </span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <i class="fa fa-dollar"></i> <?= __('Your Earn') ?>
                                                <span class="pull-right text-green">
                                                    $<?= number_format((float)get_option('keyword_seo_publisher_earn', 0.05), 2) ?>
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="box-footer">
                                        <?= $this->Html->link(__('Do Task'), ['action' => 'start', $campaign->id], ['class' => 'btn btn-primary btn-block']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header">
                <h3 class="box-title"><?= __('How It Works') ?></h3>
            </div>
            <div class="box-body">
                <ol>
                    <li><?= __('Select a SEO task from the list above.') ?></li>
                    <li><?= __('Search the keyword on Google and find the target website.') ?></li>
                    <li><?= __('Visit the website and stay for %d seconds to find the code.', '<b>' . get_option('keyword_seo_default_wait', 60) . '</b>') ?></li>
                    <li><?= __('Enter the 6-digit code in the form below to earn money.') ?></li>
                </ol>
                <div class="alert alert-success">
                    <i class="icon fa fa-money"></i>
                    <?= __('Earn $%s for each valid code submission!', number_format((float)get_option('keyword_seo_publisher_earn', 0.05), 2)) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title"><?= __('Submit Your Code') ?></h3>
            </div>
            <div class="box-body">
                <?= $this->Form->create(null, ['url' => ['action' => 'submit']]) ?>
                
                <?= $this->Form->control('campaign_id', [
                    'label' => __('Select Task'),
                    'options' => array_column($campaigns, 'name', 'id'),
                    'empty' => __('Select a task'),
                    'class' => 'form-control',
                    'required' => true
                ]) ?>

                <?= $this->Form->control('code', [
                    'label' => __('Enter 6-digit Code'),
                    'class' => 'form-control',
                    'placeholder' => '000000',
                    'maxlength' => 6,
                    'required' => true
                ]) ?>

                <?= $this->Form->button(__('Submit Code'), ['class' => 'btn btn-success btn-block btn-lg']) ?>

                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
