<?php
$this->assign('title', __('SEO Task'));
$this->assign('description', '');
$this->assign('content_title', __('SEO Task'));

$waitSeconds = $campaign->seo_wait_seconds ?? 60;
?>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title"><?= h($campaign->name) ?></h3>
            </div>
            <div class="box-body">
                <div class="alert alert-info">
                    <h4><i class="icon fa fa-info"></i> <?= __('Instructions') ?></h4>
                    <ol>
                        <li><?= __('Search keyword: ') ?><strong><?= h($campaign->keyword_or_url) ?></strong> <?= __('on Google') ?></li>
                        <li><?= __('Find and click on the target website: ') ?><strong><?= h($campaign->website_url) ?></strong></li>
                        <li><?= __('Stay on the website for at least %d seconds', $waitSeconds) ?></li>
                        <li><?= __('Find the 6-digit code on the website') ?></li>
                        <li><?= __('Return here and enter the code below') ?></li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="box box-default">
                            <div class="box-header">
                                <h3 class="box-title"><?= __('Step 1: Search') ?></h3>
                            </div>
                            <div class="box-body text-center">
                                <p><?= __('Search this keyword on Google:') ?></p>
                                <h4 class="text-red"><?= h($campaign->keyword_or_url) ?></h4>
                                <a href="https://www.google.com/search?q=<?= urlencode($campaign->keyword_or_url) ?>" 
                                   target="_blank" 
                                   class="btn btn-lg btn-primary">
                                    <i class="fa fa-google"></i> <?= __('Search on Google') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="box box-default">
                            <div class="box-header">
                                <h3 class="box-title"><?= __('Step 2: Visit Website') ?></h3>
                            </div>
                            <div class="box-body text-center">
                                <p><?= __('Target website:') ?></p>
                                <h4 class="text-green"><?= $this->Text->truncate(h($campaign->website_url), 30) ?></h4>
                                <a href="<?= h($campaign->website_url) ?>" 
                                   target="_blank" 
                                   class="btn btn-lg btn-success">
                                    <i class="fa fa-external-link"></i> <?= __('Visit Website') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($campaign->seo_image_1) || !empty($campaign->seo_image_2)): ?>
                <div class="row">
                    <?php if (!empty($campaign->seo_image_1)): ?>
                    <div class="col-md-6">
                        <div class="box box-warning">
                            <div class="box-header">
                                <h3 class="box-title"><?= __('How to find the code (1)') ?></h3>
                            </div>
                            <div class="box-body">
                                <img src="<?= h($campaign->seo_image_1) ?>" class="img-responsive">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($campaign->seo_image_2)): ?>
                    <div class="col-md-6">
                        <div class="box box-warning">
                            <div class="box-header">
                                <h3 class="box-title"><?= __('How to find the code (2)') ?></h3>
                            </div>
                            <div class="box-body">
                                <img src="<?= h($campaign->seo_image_2) ?>" class="img-responsive">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="box box-success">
                    <div class="box-header">
                        <h3 class="box-title"><?= __('Step 3: Enter the Code') ?></h3>
                    </div>
                    <div class="box-body">
                        <?= $this->Form->create(null, ['url' => ['action' => 'submit']]) ?>
                        
                        <?= $this->Form->hidden('campaign_id', ['value' => $campaign->id]) ?>
                        
                        <div class="form-group">
                            <label for="code"><?= __('Enter the 6-digit code you found:') ?></label>
                            <input type="text" name="code" id="code" class="form-control input-lg text-center" 
                                   placeholder="000000" maxlength="6" required autofocus
                                   pattern="[0-9]{6}" title="Enter 6 digits">
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="icon fa fa-clock-o"></i>
                            <?= __('Wait at least %d seconds on the website before entering the code!', $waitSeconds) ?>
                        </div>

                        <?= $this->Form->button(__('Submit Code'), ['class' => 'btn btn-success btn-lg btn-block']) ?>

                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
