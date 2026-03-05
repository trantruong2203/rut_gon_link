<?php
$this->assign('title', __('Get Code') . ' - ' . get_option('site_name'));
?>
<div class="container" style="padding: 40px 20px; text-align: center;">
    <div class="box box-primary" style="max-width: 500px; margin: 0 auto;">
        <div class="box-header with-border">
            <h3 class="box-title"><?= __('Your Code') ?></h3>
        </div>
        <div class="box-body">
            <p><?= __('Copy the code below and enter it on the previous page to continue.') ?></p>
            <div class="alert alert-success" style="font-size: 28px; font-weight: bold; letter-spacing: 5px;">
                <?= h($link_code) ?>
            </div>
            <p class="text-muted"><?= __('This code will expire when your session ends.') ?></p>
        </div>
    </div>
</div>
