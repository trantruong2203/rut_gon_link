<?php
$this->assign('title', __('Redirecting') . ' - ' . get_option('site_name'));
$delay_seconds = isset($delay_seconds) ? (int) $delay_seconds : 5;
?>
<div class="container" style="padding: 40px 20px; text-align: center;">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('Redirecting') ?></h3>
                </div>
                <div class="box-body">
                    <p class="text-muted" style="font-size: 16px;"><?= __('Please wait') ?> <span id="countdown"><?= $delay_seconds ?></span> <?= __('seconds') ?></p>
                    <div class="progress" style="height: 25px;">
                        <div id="progress-bar" class="progress-bar progress-bar-success" role="progressbar" style="width: 0%;"></div>
                    </div>
                    <p class="text-muted small" style="margin-top: 15px;"><?= __('A new tab has been opened. You will be redirected shortly.') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->start('scriptBottom'); ?>
<script>
(function() {
    var destUrl = <?= json_encode($dest_url) ?>;
    var finalAdUrl = <?= json_encode($final_ad_url) ?>;
    var delaySeconds = <?= $delay_seconds ?>;
    var remaining = delaySeconds;
    var startTime = Date.now();
    var totalMs = delaySeconds * 1000;

    if (finalAdUrl) {
        window.open(finalAdUrl, '_blank');
    }

    var timer = setInterval(function() {
        var elapsed = Date.now() - startTime;
        remaining = Math.max(0, Math.ceil((totalMs - elapsed) / 1000));
        document.getElementById('countdown').textContent = remaining;
        document.getElementById('progress-bar').style.width = ((delaySeconds - remaining) / delaySeconds * 100) + '%';

        if (remaining <= 0) {
            clearInterval(timer);
            window.location.href = destUrl;
        }
    }, 200);
})();
</script>
<?php $this->end(); ?>
