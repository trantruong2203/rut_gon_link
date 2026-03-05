<?php
$this->assign('title', get_option('site_name'));
$this->assign('description', get_option('description'));
$this->assign('content_title', get_option('site_name'));
$this->assign('og_title', $link->title);
$this->assign('og_description', $link->description);
$this->assign('og_image', $link->image);

$show_recaptcha = (get_option('interstitial_recaptcha', 'yes') == 'yes') && isset_recaptcha();
$session_time = isset($session_time) ? (int) $session_time : 600;
$landing_url = $this->Url->build('/landing/' . $link->alias, ['fullBase' => true]);

$instruction_header = get_option('interstitial_instruction_header', 'HƯỚNG DẪN LẤY MÃ - TÌM KIẾM TỪ KHOÁ');
$instruction_note = get_option('interstitial_instruction_note', '(Lưu ý: Vui lòng làm theo đúng hướng dẫn để không bị sai MÃ)');
$keywordTask = isset($keywordTask) ? $keywordTask : null;
$campaign = $campaign_item->campaign ?? null;
$search_keyword = ($campaign && !empty($campaign->keyword_or_url))
    ? $campaign->keyword_or_url
    : ($keywordTask ? $keywordTask->keyword : get_option('interstitial_search_keyword', 'tg88'));
$campaign_host = isset($campaign_item->campaign->website_url) ? parse_url($campaign_item->campaign->website_url, PHP_URL_HOST) : '';
$target_host = $keywordTask && !empty($keywordTask->target_url) ? parse_url($keywordTask->target_url, PHP_URL_HOST) : $campaign_host;
$website_name = get_option('interstitial_website_name', $target_host ?: $campaign_host ?: 'website');
$wait_seconds = ($campaign && !empty($campaign->countdown_seconds))
    ? (int) $campaign->countdown_seconds
    : (int) get_option('landing_wait_seconds', 60);
$campaign_image_1 = ($campaign && !empty($campaign->image_1)) ? $this->Url->build('/' . $campaign->image_1, ['fullBase' => true]) : null;
$campaign_image_2 = ($campaign && !empty($campaign->image_2)) ? $this->Url->build('/' . $campaign->image_2, ['fullBase' => true]) : null;
$video_url = trim((string) get_option('interstitial_video_url', ''));
$report_url = trim((string) get_option('interstitial_report_error_url', ''));
?>

<div class="myTestAd" style="height: 5px; width: 5px; position: absolute;"></div>

<div class="container" style="padding: 20px 0;">
    <?php if (!empty($link->title) || !empty($link->description)) : ?>
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div style="margin-bottom: 25px;">
                <?php if (!empty($link->title)) : ?>
                <h4 style="color: #337ab7; font-weight: bold;"><?= h($link->title) ?></h4>
                <?php endif; ?>
                <?php if (!empty($link->description)) : ?>
                <p style="color: #333;"><?= h($link->description) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form nhập mã - phần đầu -->
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-primary">
                <div class="box-body">
                    <h4 style="color: #337ab7; font-weight: bold; margin-bottom: 20px;">
                        VUI LÒNG NHẬP MÃ ĐỂ TIẾP TỤC ĐẾN TRANG ĐÍCH
                    </h4>

                    <?= $this->Form->create(null, ['url' => ['controller' => 'Links', 'action' => 'go', 'prefix' => false], 'id' => 'go-link']); ?>
                    <?= $this->Form->hidden('alias', ['value' => $link->alias]); ?>
                    <?= $this->Form->hidden('ci', ['value' => $campaign_item->campaign_id]); ?>
                    <?= $this->Form->hidden('cui', ['value' => $campaign_item->campaign->user_id]); ?>
                    <?= $this->Form->hidden('cii', ['value' => $campaign_item->id]); ?>
                    <?= $this->Form->hidden('ref', ['value' => strtolower(env('HTTP_REFERER'))]); ?>

                    <div class="form-group">
                        <?= $this->Form->input('code', ['label' => false, 'type' => 'text', 'placeholder' => 'NHẬP MÃ', 'class' => 'form-control input-lg', 'id' => 'code-input', 'style' => 'text-transform: uppercase; letter-spacing: 2px; max-width: 300px;', 'maxlength' => 10, 'autocomplete' => 'off']); ?>
                    </div>

                    <?php if ($show_recaptcha) : ?>
                    <?php $this->Form->unlockField('g-recaptcha-response'); ?>
                    <div class="form-group">
                        <p class="text-muted" style="margin-bottom: 10px;"><?= __('Please complete the captcha to enable the continue button.') ?></p>
                        <div id="captchaInterstitial" style="display: inline-block;"></div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group" id="session-timer-wrap" style="font-size: 14px; color: #666;">
                        Thời gian phiên còn lại: <span id="timer-display"><?= $session_time ?></span>
                    </div>

                    <?= $this->Form->button('Nhấn vào đây để tiếp tục', ['id' => 'go-submit', 'class' => 'btn btn-success btn-lg', 'type' => 'submit', 'disabled' => $show_recaptcha]); ?>
                    <?= $this->Form->end(); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Hướng dẫn 6 bước -->
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default" style="border: 1px solid #ddd; border-radius: 8px;">
                <div class="panel-heading" style="background: #f5f5f5; border-radius: 8px 8px 0 0;">
                    <h4 class="panel-title" style="font-weight: bold;"><?= h($instruction_header) ?></h4>
                </div>
                <div class="panel-body" style="background: #fff;">
                    <p style="font-weight: bold; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; color: #856404;"><?= h($instruction_note) ?></p>

                    <div style="border: 2px dashed #337ab7; padding: 15px; margin: 15px 0; background: #f9f9f9;">
                        <p style="color: #337ab7; margin: 0;"><strong>Dùng trình duyệt Chrome để tránh lỗi</strong></p>
                        <p style="color: #337ab7; margin: 5px 0 0 0;"><strong>KHÔNG nhấn vào quảng cáo tài trợ</strong></p>
                        <p style="color: #337ab7; margin: 5px 0 0 0;"><strong>KHÔNG dùng chế độ ẩn danh</strong></p>
                    </div>

                    <ol style="text-align: left; padding-left: 20px; line-height: 2;">
                        <li><strong>Bước 1: Mở tab mới, truy cập google.com</strong></li>
                        <li><strong>Bước 2: Gõ từ khóa tìm kiếm "<?= h($search_keyword) ?>"</strong></li>
                        <li><strong>Bước 3: Nhấn vào website <?= h($website_name) ?> ở trang 1</strong></li>
                        <li><strong>Bước 4: Cuộn đến cuối bài viết, nhấn nút LẤY MÃ như hình bên dưới và chờ <?= $wait_seconds ?> giây</strong></li>

                        <?php if ($campaign_image_1) : ?>
                        <div style="margin: 15px 0;">
                            <img src="<?= h($campaign_image_1) ?>" alt="Hình minh họa LẤY MÃ" class="img-responsive" style="max-width: 100%; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <?php endif; ?>

                        <li><strong>Bước 5: Sau đó nhấn bất kỳ link nào trên website và quay lại vị trí đó để lấy mã</strong></li>

                        <?php if ($campaign_image_2) : ?>
                        <div style="margin: 15px 0;">
                            <img src="<?= h($campaign_image_2) ?>" alt="Hình minh họa click link" class="img-responsive" style="max-width: 100%; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <?php endif; ?>

                        <li><strong>Bước 6: Dán mã vào đây để đến trang đích</strong></li>
                    </ol>

                    <p style="margin-top: 20px;">
                        <a href="<?= h($landing_url) ?>" target="_blank" class="btn btn-danger btn-lg">
                            <i class="fa fa-external-link"></i> LẤY MÃ - Nhấn để mở trang
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form nhập mã - phần cuối (Bước 6) -->
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default" style="border: 1px solid #ddd; border-radius: 8px;">
                <div class="panel-body" style="background: #f9f9f9;">
                    <h5 style="font-weight: bold; margin-bottom: 15px;">Bước 6: Dán mã vào đây để đến trang đích</h5>

                    <div class="form-group">
                        <label for="code-input-2">Nhập mã</label>
                        <input type="text" id="code-input-2" class="form-control input-lg" placeholder="NHẬP MÃ" style="text-transform: uppercase; letter-spacing: 2px; max-width: 300px;" maxlength="10" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <button type="button" id="go-submit-2" class="btn btn-success btn-lg" <?= $show_recaptcha ? 'disabled' : '' ?>>Nhấn vào đây để tiếp tục</button>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <span>Bạn không lấy được mã?</span>
                        <?php if (!empty($report_url)) : ?>
                        <a href="<?= h($report_url) ?>" target="_blank" class="btn btn-danger btn-sm" style="margin-left: 10px;">Báo lỗi</a>
                        <?php else : ?>
                        <a href="<?= $this->Url->build(['controller' => 'Forms', 'action' => 'contact']) ?>" target="_blank" class="btn btn-danger btn-sm" style="margin-left: 10px;">Báo lỗi</a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($video_url)) : ?>
                    <div class="form-group">
                        <a href="<?= h($video_url) ?>" target="_blank" class="btn btn-info">
                            <i class="fa fa-video-camera"></i> VIDEO HƯỚNG DẪN CÁCH LẤY MÃ
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (get_option('enable_popup', 'yes') == 'yes') : ?>
<?= $this->Form->create(null, ['url' => ['controller' => 'Links', 'action' => 'popad', 'prefix' => false], 'target' => "_blank", 'id' => 'go-popup', 'class' => 'hidden']); ?>
<?= $this->Form->hidden('pop_ad', ['value' => $pop_ad]); ?>
<?= $this->Form->end(); ?>
<?php endif; ?>

<?php $this->start('scriptBottom'); ?>
<script>
<?php if (get_option('enable_popup', 'yes') == 'yes') : ?>
$(window).on('load', function () {
    $(document).one("click", function (e) {
        if (!$(e.target).closest('#go-link').length && !$(e.target).closest('#go-submit').length && !$(e.target).closest('#go-submit-2').length) $('#go-popup').submit();
    });
    $('#go-popup').one("submit", function (e) {
        var w=screen.width-150,h=screen.height-150; window.open('about:blank','Popup_Window','width='+w+',height='+h+',left='+((screen.width-w)/2)+',top='+((screen.height-h)/2)); this.target='Popup_Window';
    });
});
<?php endif; ?>
$(document).ready(function () {
    window.setTimeout(function(){var t=$('.myTestAd');document.cookie="adblockUser=0; expires=<?= date('D, d M Y H:i:s', time()+86400) ?> GMT";(t.filter(':visible').length===0||t.filter(':hidden').length>0||t.height()===0)&&(document.cookie="adblockUser=1; expires=<?= date('D, d M Y H:i:s', time()+86400) ?> GMT");},1500);
    var $code1=$('#code-input'),$code2=$('#code-input-2');
    $code1.on('input',function(){this.value=this.value.toUpperCase();$code2.val(this.value);});
    $code2.on('input',function(){this.value=this.value.toUpperCase();$code1.val(this.value);});
    var st=<?= $session_time ?>,ti=setInterval(function(){st--;$('#timer-display').text(st);if(st<=0){clearInterval(ti);$('#session-timer-wrap').html('<?= addslashes(__('Session expired. Please refresh the page.')) ?>');$('#go-submit').prop('disabled',true);$('#go-submit-2').prop('disabled',true);}},1000);
    $('#go-submit-2').on('click',function(){$('#go-link').submit();});
});
$("#go-link").on("submit",function(e){e.preventDefault();var f=$(this),b=f.find('#go-submit'),b2=$('#go-submit-2'),c=f.find('#code-input');if(!$.trim(c.val())){alert('<?= addslashes(__('Please enter the code.')) ?>');return;}
<?php if ($show_recaptcha) : ?>if(!$.trim($('textarea[name="g-recaptcha-response"]').val())){alert('<?= addslashes(__('Please complete the reCAPTCHA verification.')) ?>');return;}
<?php endif; ?>b.prop('disabled',true);b2.prop('disabled',true);b.text('<?= addslashes(__('Checking...')) ?>');b2.text('<?= addslashes(__('Checking...')) ?>');$.ajax({dataType:'json',type:'POST',url:f.attr('action'),data:f.serialize(),success:function(r){if(r.url){var siteHost='<?= addslashes(parse_url($this->Url->build("/", ['fullBase' => true]), PHP_URL_HOST) ?: "") ?>';var ref=(document.referrer||'').toLowerCase();var ok=!ref||!siteHost||ref.indexOf(siteHost)!==-1;if(!ok){alert('<?= addslashes(__('Invalid referrer. Please use the link from our site.')) ?>');b.prop('disabled',false);b2.prop('disabled',false);b.text('<?= addslashes('Nhấn vào đây để tiếp tục') ?>');b2.text('<?= addslashes('Nhấn vào đây để tiếp tục') ?>');return;}var dMin=<?= (int)get_option('anti_bypass_redirect_delay_min', 2) ?>;var dMax=<?= (int)get_option('anti_bypass_redirect_delay_max', 5) ?>;var delay=Math.floor(Math.random()*(dMax-dMin+1)+dMin)*1000;setTimeout(function(){window.location.href=r.url;},delay);}else{alert(r.message||'<?= addslashes(__('Invalid code. Please try again.')) ?>');b.prop('disabled',false);b2.prop('disabled',false);b.text('<?= addslashes('Nhấn vào đây để tiếp tục') ?>');b2.text('<?= addslashes('Nhấn vào đây để tiếp tục') ?>');<?php if ($show_recaptcha) : ?>if(typeof grecaptcha!=='undefined'&&typeof captchaInterstitial!=='undefined'){grecaptcha.reset(captchaInterstitial);b.prop('disabled',true);b2.prop('disabled',true);}<?php endif; ?>}},error:function(){alert("<?= addslashes(__('An error occurred. Please try again.')) ?>");b.prop('disabled',false);b2.prop('disabled',false);b.text('<?= addslashes('Nhấn vào đây để tiếp tục') ?>');b2.text('<?= addslashes('Nhấn vào đây để tiếp tục') ?>');<?php if ($show_recaptcha) : ?>if(typeof grecaptcha!=='undefined'&&typeof captchaInterstitial!=='undefined'){grecaptcha.reset(captchaInterstitial);b.prop('disabled',true);b2.prop('disabled',true);}<?php endif; ?>}});});
</script>
<?php $this->end(); ?>
