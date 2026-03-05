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
$contact_admin_url = get_option('contact_admin_url', 'https://web.telegram.org/k/#@my201901');
?>

<style>
body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; margin: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
.main-wrapper { background: #fff; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); margin: 30px auto; max-width: 800px; overflow: hidden; }
.header-section { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 30px; text-align: center; color: #fff; }
.header-section h2 { margin: 0; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
.header-section p { margin: 10px 0 0 0; opacity: 0.9; font-size: 14px; }
.content-section { padding: 40px 30px; }
.box-verify { background: #f8f9fa; border-radius: 15px; padding: 30px; text-align: center; border: 2px solid #e9ecef; }
.box-verify h3 { color: #1e3c72; margin: 0 0 20px 0; font-size: 22px; font-weight: 600; }
.input-code-wrap { position: relative; display: inline-block; margin-bottom: 20px; }
.input-code-wrap input { 
    border: 3px solid #1e3c72; border-radius: 12px; padding: 15px 20px; font-size: 24px; 
    text-align: center; letter-spacing: 8px; text-transform: uppercase; width: 280px; 
    transition: all 0.3s ease; outline: none;
}
.input-code-wrap input:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.2); }
.btn-submit-code {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff; border: none;
    padding: 15px 50px; font-size: 18px; font-weight: 600; border-radius: 50px;
    cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(17,153,142,0.4);
}
.btn-submit-code:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(17,153,142,0.5); }
.btn-submit-code:disabled { background: #ccc; cursor: not-allowed; box-shadow: none; transform: none; }
.box-huongdan { background: #fff; border-radius: 15px; padding: 25px; margin-top: 30px; border: 1px solid #e9ecef; }
.box-huongdan h4 { color: #1e3c72; margin: 0 0 15px 0; font-size: 18px; font-weight: 600; display: flex; align-items: center; }
.box-huongdan h4 i { margin-right: 8px; color: #11998e; }
.warning-box { 
    background: #fff3cd; border: 1px solid #ffc107; border-radius: 10px; padding: 15px; 
    margin: 15px 0; color: #856404; font-size: 14px; 
}
.warning-box strong { display: block; margin-bottom: 5px; }
.steps-list { text-align: left; padding-left: 20px; }
.steps-list li { margin-bottom: 15px; color: #333; line-height: 1.6; }
.steps-list li strong { color: #1e3c72; }
.btn-get-code {
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff;
    padding: 12px 30px; font-size: 16px; font-weight: 600; border-radius: 50px;
    text-decoration: none; margin-top: 15px; transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(245,87,108,0.4);
}
.btn-get-code:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245,87,108,0.5); text-decoration: none; color: #fff; }
.session-timer { font-size: 13px; color: #666; margin-top: 15px; }
.session-timer span { color: #1e3c72; font-weight: 600; }
.report-link { display: block; margin-top: 15px; color: #dc3545; font-size: 14px; }
.report-link:hover { color: #c82333; }
.video-btn { display: inline-block; margin-top: 15px; color: #17a2b8; font-size: 14px; }
.video-btn:hover { color: #138496; }
.footer-section { 
    background: #1e3c72; padding: 20px; text-align: center; color: #fff; 
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;
}
.footer-section .copyright { font-size: 14px; opacity: 0.9; }
.footer-section .contact-btn {
    background: rgba(255,255,255,0.2); color: #fff; padding: 8px 20px;
    border-radius: 25px; text-decoration: none; font-size: 14px; transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.3);
}
.footer-section .contact-btn:hover { background: rgba(255,255,255,0.3); color: #fff; text-decoration: none; }
.error-message { 
    background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; 
    border-radius: 10px; padding: 15px; margin-bottom: 20px; display: none; 
}
.error-message.show { display: block; }
.captcha-wrap { margin: 15px 0; }
@media (max-width: 600px) {
    .main-wrapper { margin: 15px; }
    .content-section { padding: 20px 15px; }
    .input-code-wrap input { width: 100%; font-size: 20px; }
    .footer-section { flex-direction: column; text-align: center; }
}
</style>

<div class="myTestAd" style="height: 5px; width: 5px; position: absolute;"></div>

<div class="main-wrapper">
    <!-- Header -->
    <div class="header-section">
        <h2><i class="fa fa-link"></i> XÁC NHẬN TRUY CẬP</h2>
        <p>Vui lòng hoàn tất các bước bên dưới để được chuyển hướng</p>
    </div>

    <!-- Content -->
    <div class="content-section">
        <?php if (!empty($link->title) || !empty($link->description)) : ?>
        <div style="text-align: center; margin-bottom: 25px;">
            <?php if (!empty($link->title)) : ?>
            <h4 style="color: #1e3c72; font-weight: 600;"><?= h($link->title) ?></h4>
            <?php endif; ?>
            <?php if (!empty($link->description)) : ?>
            <p style="color: #666;"><?= h($link->description) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Error message -->
        <div class="error-message" id="error-message">
            <i class="fa fa-exclamation-triangle"></i> <span id="error-text">Mã xác nhận không đúng. Vui lòng thử lại!</span>
        </div>

        <!-- Form xác nhận -->
        <div class="box-verify">
            <h3><i class="fa fa-shield"></i> NHẬP MÃ XÁC NHẬN</h3>
            
            <?= $this->Form->create(null, ['url' => ['controller' => 'Links', 'action' => 'go', 'prefix' => false], 'id' => 'go-link']); ?>
            <?= $this->Form->hidden('alias', ['value' => $link->alias]); ?>
            <?= $this->Form->hidden('ci', ['value' => $campaign_item->campaign_id]); ?>
            <?= $this->Form->hidden('cui', ['value' => $campaign_item->campaign->user_id]); ?>
            <?= $this->Form->hidden('cii', ['value' => $campaign_item->id]); ?>
            <?= $this->Form->hidden('ref', ['value' => strtolower(env('HTTP_REFERER'))]); ?>

            <div class="input-code-wrap">
                <?= $this->Form->input('code', ['label' => false, 'type' => 'text', 'placeholder' => 'NHẬP MÃ', 'class' => 'form-control', 'id' => 'code-input', 'maxlength' => 10, 'autocomplete' => 'off']); ?>
            </div>

            <?php if ($show_recaptcha) : ?>
            <?php $this->Form->unlockField('g-recaptcha-response'); ?>
            <div class="captcha-wrap">
                <div id="captchaInterstitial"></div>
            </div>
            <?php endif; ?>

            <div class="session-timer">
                Thời gian phiên còn lại: <span id="timer-display"><?= $session_time ?></span> giây
            </div>

            <?= $this->Form->button('<i class="fa fa-check"></i> XÁC NHẬN', ['id' => 'go-submit', 'class' => 'btn-submit-code', 'type' => 'submit', 'disabled' => $show_recaptcha]); ?>
            <?= $this->Form->end(); ?>
        </div>

        <!-- Hướng dẫn -->
        <div class="box-huongdan">
            <h4><i class="fa fa-info-circle"></i> <?= h($instruction_header) ?></h4>
            
            <div class="warning-box">
                <strong><i class="fa fa-exclamationclamation"></i> LƯU Ý QUAN TRỌNG:</strong>
                Vui lòng làm theo đúng hướng dẫn để không bị SAI MÃ. Sử dụng trình duyệt Chrome để tránh lỗi.
            </div>

            <ol class="steps-list">
                <li><strong>Bước 1:</strong> Mở tab mới, truy cập <strong>google.com</strong></li>
                <li><strong>Bước 2:</strong> Gõ từ khóa tìm kiếm: <strong style="color: #d63384; font-size: 16px;">"<?= h($search_keyword) ?>"</strong></li>
                <li><strong>Bước 3:</strong> Nhấn vào website <strong><?= h($website_name) ?></strong> ở trang kết quả đầu tiên</li>
                <li><strong>Bước 4:</strong> Cuộn xuống cuối trang, nhấn nút <strong>LẤY MÃ</strong> và chờ <strong><?= $wait_seconds ?> giây</strong></li>
                
                <?php if ($campaign_image_1) : ?>
                <div style="margin: 15px 0; text-align: center;">
                    <img src="<?= h($campaign_image_1) ?>" alt="Hướng dẫn lấy mã" class="img-responsive" style="max-width: 100%; border-radius: 10px; border: 2px solid #e9ecef;">
                </div>
                <?php endif; ?>

                <li><strong>Bước 5:</strong> Nhấn vào bất kỳ link nào trên website rồi quay lại để lấy mã</li>
                
                <?php if ($campaign_image_2) : ?>
                <div style="margin: 15px 0; text-align: center;">
                    <img src="<?= h($campaign_image_2) ?>" alt="Hướng dẫn click link" class="img-responsive" style="max-width: 100%; border-radius: 10px; border: 2px solid #e9ecef;">
                </div>
                <?php endif; ?>

                <li><strong>Bước 6:</strong> Nhập mã vào ô trên để tiếp tục</li>
            </ol>

            <div style="text-align: center; margin-top: 20px;">
                <a href="<?= h($landing_url) ?>" target="_blank" class="btn-get-code">
                    <i class="fa fa-external-link"></i> LẤY MÃ NGAY
                </a>
            </div>

            <?php if (!empty($video_url)) : ?>
            <div style="text-align: center;">
                <a href="<?= h($video_url) ?>" target="_blank" class="video-btn">
                    <i class="fa fa-video-camera"></i> Xem video hướng dẫn
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Báo lỗi -->
        <div style="text-align: center; margin-top: 20px;">
            <span style="color: #666; font-size: 14px;">Không lấy được mã? </span>
            <?php if (!empty($report_url)) : ?>
            <a href="<?= h($report_url) ?>" target="_blank" class="report-link">Báo lỗi tại đây</a>
            <?php else : ?>
            <a href="<?= $this->Url->build(['controller' => 'Forms', 'action' => 'contact']) ?>" target="_blank" class="report-link">Liên hệ hỗ trợ</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-section">
        <span class="copyright"><i class="fa fa-copyright"></i> MONEYLINK - All Rights Reserved</span>
        <a href="<?= h($contact_admin_url) ?>" target="_blank" class="contact-btn">
            <i class="fa fa-telegram"></i> LIÊN HỆ ADMIN
        </a>
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
    var $code1=$('#code-input');
    $code1.on('input',function(){this.value=this.value.toUpperCase();$('#error-message').removeClass('show');});
    var st=<?= $session_time ?>,ti=setInterval(function(){st--;$('#timer-display').text(st);if(st<=0){clearInterval(ti);$('#timer-display').closest('.session-timer').html('<span style="color:#dc3545;">Phiên đã hết hạn. Vui lòng tải lại trang.</span>');$('#go-submit').prop('disabled',true);}},1000);
});
$("#go-link").on("submit",function(e){e.preventDefault();var f=$(this),b=f.find('#go-submit'),c=f.find('#code-input');if(!$.trim(c.val())){$('#error-text').text('Vui lòng nhập mã xác nhận!');$('#error-message').addClass('show');return;}
<?php if ($show_recaptcha) : ?>if(!$.trim($('textarea[name="g-recaptcha-response"]').val())){$('#error-text').text('Vui lòng hoàn thành reCAPTCHA!');$('#error-message').addClass('show');return;}
<?php endif; ?>b.prop('disabled',true);b.html('<i class="fa fa-spinner fa-spin"></i> Đang kiểm tra...');$.ajax({dataType:'json',type:'POST',url:f.attr('action'),data:f.serialize(),success:function(r){if(r.url){var siteHost='<?= addslashes(parse_url($this->Url->build("/", ['fullBase' => true]), PHP_URL_HOST) ?: "") ?>';var ref=(document.referrer||'').toLowerCase();var ok=!ref||!siteHost||ref.indexOf(siteHost)!==-1;if(!ok){$('#error-text').text('Liên kết không hợp lệ. Vui lòng sử dụng link từ trang của chúng tôi!');$('#error-message').addClass('show');b.prop('disabled',false);b.html('<i class="fa fa-check"></i> XÁC NHẬN');return;}var dMin=<?= (int)get_option('anti_bypass_redirect_delay_min', 2) ?>;var dMax=<?= (int)get_option('anti_bypass_redirect_delay_max', 5) ?>;var delay=Math.floor(Math.random()*(dMax-dMin+1)+dMin)*1000;setTimeout(function(){window.location.href=r.url;},delay);}else{$('#error-text').text(r.message||'Mã xác nhận không đúng. Vui lòng thử lại!');$('#error-message').addClass('show');b.prop('disabled',false);b.html('<i class="fa fa-check"></i> XÁC NHẬN');<?php if ($show_recaptcha) : ?>if(typeof grecaptcha!=='undefined'&&typeof captchaInterstitial!=='undefined'){grecaptcha.reset(captchaInterstitial);}<?php endif; ?>}},error:function(){$('#error-text').text('Đã xảy ra lỗi. Vui lòng thử lại!');$('#error-message').addClass('show');b.prop('disabled',false);b.html('<i class="fa fa-check"></i> XÁC NHẬN');<?php if ($show_recaptcha) : ?>if(typeof grecaptcha!=='undefined'&&typeof captchaInterstitial!=='undefined'){grecaptcha.reset(captchaInterstitial);}<?php endif; ?>}});});
</script>
<?php $this->end(); ?>
