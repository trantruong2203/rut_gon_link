<?php
$this->assign('title', get_option('site_name'));
$this->assign('description', get_option('description'));
$this->assign('og_title', $link->title);
$this->assign('og_description', $link->description);
$this->assign('og_image', $link->image);

// Force captcha to always show for interstitial
$show_recaptcha = true;
$session_time = isset($session_time) ? (int) $session_time : 600;
$landing_url = $this->Url->build('/landing/' . $link->alias, ['fullBase' => true]);

$instruction_header = get_option('interstitial_instruction_header', __('HƯỚNG DẪN LẤY MÃ - TÌM KIẾM TỪ KHOÁ'));
$instruction_note = get_option('interstitial_instruction_note', __('(Lưu ý: Vui lòng làm theo đúng hướng dẫn để không bị sai MÃ)'));
$keywordTask = isset($keywordTask) ? $keywordTask : null;
$search_keyword = $keywordTask ? $keywordTask->keyword : get_option('interstitial_search_keyword', 'tg88');
$campaign_host = isset($campaign_item->campaign->website_url) ? parse_url($campaign_item->campaign->website_url, PHP_URL_HOST) : '';
$website_name = get_option('interstitial_website_name', $campaign_host ?: 'website');
$wait_seconds = (int) get_option('landing_wait_seconds', 60);
?>
<div class="myTestAd" style="height: 5px; width: 5px; position: absolute;"></div>
<div class="container" style="padding: 20px 0;">
    <?php if (!empty($link->title) || !empty($link->description)) : ?>
    <div class="row"><div class="col-md-10 col-md-offset-1"><div style="margin-bottom: 25px;">
        <?php if (!empty($link->title)) : ?><h4 style="color: #337ab7; font-weight: bold;"><?= h($link->title) ?></h4><?php endif; ?>
        <?php if (!empty($link->description)) : ?><p style="color: #333;"><?= h($link->description) ?></p><?php endif; ?>
    </div></div></div>
    <?php endif; ?>

    <div class="row"><div class="col-md-8 col-md-offset-2"><div class="box box-primary"><div class="box-body">
        <h4 style="color: #337ab7; font-weight: bold; margin-bottom: 20px;"><?= __('PLEASE ENTER CODE TO CONTINUE TO DESTINATION PAGE') ?></h4>
        <?= $this->Form->create(null, ['url' => ['controller' => 'Links', 'action' => 'go', 'prefix' => false], 'id' => 'go-link']); ?>
        <?= $this->Form->hidden('alias', ['value' => $link->alias]); ?>
        <?= $this->Form->hidden('ci', ['value' => $campaign_item->campaign_id]); ?>
        <?= $this->Form->hidden('cui', ['value' => $campaign_item->campaign->user_id]); ?>
        <?= $this->Form->hidden('cii', ['value' => $campaign_item->id]); ?>
        <?= $this->Form->hidden('ref', ['value' => strtolower(env('HTTP_REFERER'))]); ?>
        <div class="form-group"><?= $this->Form->input('code', ['label' => false, 'type' => 'text', 'placeholder' => __('Enter code'), 'class' => 'form-control input-lg', 'id' => 'code-input', 'style' => 'text-transform: uppercase; letter-spacing: 2px; max-width: 300px;', 'maxlength' => 10, 'autocomplete' => 'off']); ?></div>
        <?php if ($show_recaptcha) : ?>
            <?php $this->Form->unlockField('g-recaptcha-response'); ?>
            <div class="form-group">
                <p class="text-muted" style="margin-bottom: 10px;"><?= __('Please complete the captcha to enable the continue button.') ?></p>
                <div
                    class="g-recaptcha"
                    data-sitekey="<?= h(get_option('reCAPTCHA_site_key')) ?>"
                    data-callback="onInterstitialCaptchaOk"
                    data-expired-callback="onInterstitialCaptchaExpired"
                ></div>
            </div>
            <script>
                window.onInterstitialCaptchaOk = function () {
                    if (window.jQuery) {
                        jQuery('#go-submit, #go-submit-2').prop('disabled', false).removeClass('disabled');
                    }
                };
                window.onInterstitialCaptchaExpired = function () {
                    if (window.jQuery) {
                        jQuery('#go-submit, #go-submit-2').prop('disabled', true).addClass('disabled');
                    }
                };
            </script>
        <?php endif; ?>
        <div class="form-group"><div id="session-timer" class="text-danger" style="font-size: 16px; font-weight: bold;"><?= __('Session time remaining') ?>: <span id="timer-display"><?= $session_time ?></span></div></div>
        <?= $this->Form->button(__('Click here to continue'), ['id' => 'go-submit', 'class' => 'btn btn-success btn-lg', 'type' => 'submit', 'disabled' => $show_recaptcha]); ?>
        <?= $this->Form->end(); ?>
    </div></div></div></div>

    <div class="row"><div class="col-md-8 col-md-offset-2"><div class="panel panel-default" style="border: 1px solid #ddd; border-radius: 8px;">
        <div class="panel-heading" style="background: #f5f5f5;"><h4 class="panel-title" style="font-weight: bold;"><?= h($instruction_header) ?></h4></div>
        <div class="panel-body">
            <p class="text-danger" style="font-weight: bold;"><?= h($instruction_note) ?></p>
            <div style="border: 2px dashed #337ab7; padding: 15px; margin: 15px 0; background: #f9f9f9;">
                <p style="color: #337ab7; margin: 0;"><strong><?= __('Use Chrome browser to avoid errors') ?></strong></p>
                <p style="color: #337ab7; margin: 5px 0 0 0;"><strong><?= __('DO NOT Click on Sponsored ads') ?></strong></p>
                <p style="color: #337ab7; margin: 5px 0 0 0;"><strong><?= __('DO NOT use Incognito browser') ?></strong></p>
            </div>
            <ol style="text-align: left; padding-left: 20px; line-height: 1.8;">
                <li><strong><?= __('Step 1: Open a new tab, go to google.com') ?></strong></li>
                <li><strong><?= __('Step 2: Type the search keyword') ?> "<?= h($search_keyword) ?>"</strong></li>
                <li><strong><?= __('Step 3: Click on the website') ?> <?= h($website_name) ?> <?= __('on page 1') ?></strong></li>
                <li><strong><?= __('Step 4: Scroll to the end of the article, click the LẤY MÃ button as shown below and wait') ?> <?= $wait_seconds ?> <?= __('seconds') ?></strong></li>
                <li><strong><?= __('Step 5: Then click any link on the website and return to that position to get the code') ?></strong></li>
                <li><strong><?= __('Step 6: Paste the code here to go to the destination page') ?></strong></li>
            </ol>
            <p style="margin-top: 20px;"><a href="<?= h($landing_url) ?>" target="_blank" class="btn btn-danger btn-lg"><i class="fa fa-external-link"></i> <?= __('LẤY MÃ') ?> - <?= __('Click to open the page') ?></a></p>
        </div>
    </div></div></div>
</div>

<?php if (get_option('enable_popup', 'yes') == 'yes') : ?>
<?= $this->Form->create(null, ['url' => ['controller' => 'Links', 'action' => 'popad', 'prefix' => false], 'target' => "_blank", 'id' => 'go-popup', 'class' => 'hidden']); ?>
<?= $this->Form->hidden('pop_ad', ['value' => $pop_ad]); ?>
<?= $this->Form->end(); ?>
<?php endif; ?>

<?php $this->start('scriptBottom'); ?>
<script>
<?php if (get_option('enable_popup', 'yes') == 'yes') : ?>
$(window).on('load', function () { $(document).one("click", function (e) { if (!$(e.target).closest('#go-link').length && !$(e.target).closest('#go-submit').length) $('#go-popup').submit(); }); $('#go-popup').one("submit", function (e) { var w=screen.width-150,h=screen.height-150; window.open('about:blank','Popup_Window','width='+w+',height='+h+',left='+((screen.width-w)/2)+',top='+((screen.height-h)/2)); this.target='Popup_Window'; }); });
<?php endif; ?>
$(document).ready(function () { window.setTimeout(function(){var t=$('.myTestAd');document.cookie="adblockUser=0; expires=<?= \Cake\I18n\Time::now()->modify('+1 day')->toCookieString() ?>";(t.filter(':visible').length===0||t.filter(':hidden').length>0||t.height()===0)&&(document.cookie="adblockUser=1; expires=<?= \Cake\I18n\Time::now()->modify('+1 day')->toCookieString() ?>");},1500); $('#code-input').on('input',function(){this.value=this.value.toUpperCase();}); var st=<?= $session_time ?>,ti=setInterval(function(){st--;$('#timer-display').text(st);if(st<=0){clearInterval(ti);$('#session-timer').html('<?= __('Session expired. Please refresh the page.') ?>');$('#go-submit').prop('disabled',true);}},1000); });
$("#go-link").on("submit",function(e){e.preventDefault();var f=$(this),b=f.find('#go-submit'),c=f.find('#code-input');if(!$.trim(c.val())){alert('<?= __('Please enter the code.') ?>');return;}
<?php if ($show_recaptcha) : ?>if(!$.trim($('textarea[name="g-recaptcha-response"]').val())){alert('<?= __('Please complete the reCAPTCHA verification.') ?>');return;}
<?php endif; ?>b.prop('disabled',true).text('<?= __('Checking...') ?>');$.ajax({dataType:'json',type:'POST',url:f.attr('action'),data:f.serialize(),success:function(r){if(r.url)window.location.href=r.url;else{alert(r.message||'<?= __('Invalid code. Please try again.') ?>');b.prop('disabled',false).text('<?= __('Click here to continue.') ?>');<?php if ($show_recaptcha) : ?>if(typeof grecaptcha!=='undefined'){grecaptcha.reset();b.prop('disabled',true);}<?php endif; ?>}},error:function(){alert("<?= __('An error occurred. Please try again.') ?>");b.prop('disabled',false).text('<?= __('Click here to continue') ?>');<?php if ($show_recaptcha) : ?>if(typeof grecaptcha!=='undefined'){grecaptcha.reset();b.prop('disabled',true);}<?php endif; ?>}});});
</script>
<?php $this->end(); ?>
