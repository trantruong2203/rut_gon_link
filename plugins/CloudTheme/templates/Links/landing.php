<?php
$this->assign('title', get_option('site_name'));
$wait_seconds = isset($wait_seconds) ? (int) $wait_seconds : 60;
$landing_brand = get_option('landing_brand', get_option('site_name'));
$landing_url = $this->Url->build('/landing/' . $link->alias, ['fullBase' => true]);
$iframe_base = $campaign_item->campaign->website_url;
$iframe_src = $iframe_base . (strpos($iframe_base, '?') !== false ? '&' : '?') . 'landing_url=' . urlencode($landing_url);
$auto_start = $this->request->getQuery('action') === 'get_code';

// Show captcha on landing page - user must complete captcha before getting code
$show_recaptcha = true;
?>
<div class="container" style="padding: 20px 0; max-width: 900px;">
    <div class="row">
        <div class="col-xs-12">
            <div style="text-align: center; margin-bottom: 20px;">
                <h2 style="font-weight: bold;"><?= h($landing_brand) ?></h2>
            </div>

            <?php if ($show_recaptcha) : ?>
                <div class="well" style="margin-bottom: 20px;">
                    <div class="text-center">
                        <p style="margin-bottom: 15px;"><?= __('Please complete the captcha below to enable the Get Code button') ?></p>
                        <?php $this->Form->unlockField('g-recaptcha-response'); ?>
                        <div
                            class="g-recaptcha"
                            data-sitekey="<?= h(get_option('reCAPTCHA_site_key')) ?>"
                            data-callback="onLandingCaptchaOk"
                            data-expired-callback="onLandingCaptchaExpired"
                        ></div>
                    </div>
                </div>
                <script>
                    window.onLandingCaptchaOk = function () {
                        if (window.jQuery) {
                            jQuery('#btn-get-code').prop('disabled', false).removeClass('disabled');
                        }
                    };
                    window.onLandingCaptchaExpired = function () {
                        if (window.jQuery) {
                            jQuery('#btn-get-code').prop('disabled', true).addClass('disabled');
                        }
                    };
                </script>
            <?php endif; ?>

            <iframe src="<?= h($iframe_src) ?>" style="width: 100%; height: 400px; border: 1px solid #ddd; border-radius: 4px;"></iframe>

            <div class="well" style="margin-top: 20px;">
                <h4><?= __('THÔNG TIN') ?></h4>
                <p><a href="<?= h($campaign_item->campaign->website_url) ?>" target="_blank"><?= __('Privacy Policy') ?></a> | <a href="<?= h($campaign_item->campaign->website_url) ?>" target="_blank"><?= __('Terms and Conditions') ?></a></p>
                <h4><?= __('HƯỚNG DẪN') ?></h4>
                <p><a href="<?= h($campaign_item->campaign->website_url) ?>" target="_blank"><?= __('Register') ?></a> | <a href="<?= h($campaign_item->campaign->website_url) ?>" target="_blank"><?= __('Login') ?></a></p>
            </div>

            <div id="get-code-section" style="text-align: center; margin: 30px 0;"<?= $auto_start ? ' data-auto-start="1"' : '' ?>>
                <p style="margin-bottom: 15px; color: #666;"><?= __('Please click the button below and wait for the code') ?></p>
                <p class="text-muted small"><?= __('Or find the LẤY MÃ button on the website above and click it.') ?></p>
                <button type="button" id="btn-get-code" class="btn btn-danger btn-lg" style="padding: 15px 40px; font-size: 18px;" <?= $show_recaptcha ? 'disabled' : '' ?>>
                    <?= __('LẤY MÃ') ?>
                </button>
            </div>

            <div id="wait-section" style="display: none; text-align: center; margin: 30px 0;">
                <p class="text-muted"><?= __('Please wait') ?> <span id="wait-countdown"><?= $wait_seconds ?></span> <?= __('seconds') ?></p>
                <div class="progress" style="height: 25px;">
                    <div id="wait-progress" class="progress-bar progress-bar-success" role="progressbar" style="width: 0%;"></div>
                </div>
            </div>

            <div id="code-section" style="display: none; text-align: center; margin: 30px 0;">
                <p class="text-success" style="font-size: 18px; font-weight: bold;"><?= __('Your code') ?>:</p>
                <div class="alert alert-success" style="font-size: 28px; font-weight: bold; letter-spacing: 5px;">
                    <?= h($link_code) ?>
                </div>
                <p class="text-muted"><?= __('Copy this code and paste it on the previous page to continue.') ?></p>
            </div>
        </div>
    </div>
</div>

<?php $this->start('scriptBottom'); ?>
<script>
$(document).ready(function () {
    var waitSeconds = <?= $wait_seconds ?>;
    var $getCodeSection = $('#get-code-section');
    var $waitSection = $('#wait-section');
    var $codeSection = $('#code-section');
    var $btn = $('#btn-get-code');
    var $countdown = $('#wait-countdown');
    var $progress = $('#wait-progress');
    
    <?php if ($show_recaptcha) : ?>
    // Disable button by default if captcha is required
    $btn.prop('disabled', true).addClass('disabled');
    <?php endif; ?>

    function startCountdown() {
        $getCodeSection.hide();
        $waitSection.show();
        $btn.prop('disabled', true);

        var remaining = waitSeconds;
        var startTime = Date.now();
        var totalMs = waitSeconds * 1000;

        var timer = setInterval(function () {
            var elapsed = Date.now() - startTime;
            remaining = Math.max(0, Math.ceil((totalMs - elapsed) / 1000));
            $countdown.text(remaining);
            $progress.css('width', (remaining / waitSeconds * 100) + '%');

            if (remaining <= 0) {
                clearInterval(timer);
                $waitSection.hide();
                $codeSection.show();
            }
        }, 200);
    }

    if ($getCodeSection.data('auto-start')) {
        startCountdown();
    }

    $btn.on('click', startCountdown);
});
</script>
<?php $this->end(); ?>
