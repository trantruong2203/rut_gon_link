<?php
$this->assign('title', __('Get Code') . ' - ' . get_option('site_name'));

// Show captcha on code page for additional protection
$show_recaptcha = true;
$session_time = 600;
?>
<div class="myTestAd" style="height: 5px; width: 5px; position: absolute;"></div>
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
            
            <?php if ($show_recaptcha) : ?>
                <?php $this->Form->unlockField('g-recaptcha-response'); ?>
                <div class="form-group" style="margin-top: 20px;">
                    <p class="text-muted" style="margin-bottom: 10px;"><?= __('Please complete the captcha to verify you are human.') ?></p>
                    <div
                        class="g-recaptcha"
                        data-sitekey="<?= h(get_option('reCAPTCHA_site_key')) ?>"
                    ></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
