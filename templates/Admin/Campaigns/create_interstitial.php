<?php
$this->assign('title', __('Tạo chiến dịch mới'));
$this->assign('description', __('Bắt đầu chạy traffic cho website của bạn'));
$this->assign('content_title', __('Tạo chiến dịch mới - Interstitial'));

$countdown_options = get_countdown_options();
$price_matrix = [];
foreach ([1, 2] as $ver) {
    foreach (array_keys($countdown_options) as $sec) {
        $price_matrix[$ver][$sec] = calc_interstitial_price_per_1000($sec, $ver);
    }
}
?>

<div class="box box-primary">
    <div class="box-body">
        <p class="text-muted"><?= __('Bắt đầu chạy traffic cho website của bạn') ?></p>

        <div class="callout callout-info">
            <h4><i class="fa fa-info-circle"></i> <?= __("Lưu ý") ?></h4>
            <ul>
                <li><?= __("Địa chỉ Website là trang web cần view - khi vào đúng trang web đó thì mới tính view để trả tiền.") ?></li>
                <li><?= __("Loại traffic Google Search: từ khóa để tìm kiếm từ Google vào website.") ?></li>
                <li><?= __("Loại traffic Backlink: từ khóa hoặc link click để vào website.") ?></li>
            </ul>
        </div>

        <?= $this->Form->create($campaign, ['id' => 'campaign-create', 'type' => 'file']); ?>

        <label><?= $this->Form->checkbox('default_campaign') ?> <?= __('Default Campaign') ?></label>
        <span class="help-block"><?= __('Default = campaign dự phòng khi không có campaign khác. Tất cả campaign đều trả tiền, xoay vòng khi có nhiều.') ?></span>

        <?= $this->Form->control('user_id', [
            'label' => __('User'),
            'options' => $users,
            'empty' => __('Chọn'),
            'class' => 'form-control'
        ]); ?>

        <?= $this->Form->control('name', [
            'label' => __('Campaign Name'),
            'class' => 'form-control'
        ]); ?>

        <?= $this->Form->control('status', [
            'label' => __('Status'),
            'options' => [
                1 => __('Active'),
                2 => __('Paused'),
                3 => __('Canceled'),
                4 => __('Finished'),
                5 => __('Under Review'),
                6 => __('Pending Payment'),
                7 => __('Invalid Payment'),
                8 => __('Refunded')
            ],
            'empty' => __('Chọn'),
            'class' => 'form-control'
        ]); ?>

        <legend><?= __('Loại traffic') ?></legend>

        <?= $this->Form->control('traffic_source', [
            'label' => __('Loại traffic') . ' *',
            'options' => get_traffic_source_options(),
            'empty' => __('Chọn loại traffic'),
            'class' => 'form-control'
        ]); ?>
        <p class="help-block"><?= __('Loại traffic khác nhau sẽ có mức giá khác nhau') ?></p>

        <legend><?= __('Version') ?></legend>

        <?= $this->Form->control('campaign_version', [
            'label' => false,
            'type' => 'radio',
            'options' => get_campaign_version_options(),
            'value' => 1
        ]); ?>
        <p class="help-block"><?= __('Version khác nhau sẽ có mức giá khác nhau.') ?></p>

        <legend><?= __('Thời gian & View') ?></legend>

        <?= $this->Form->control('countdown_seconds', [
            'label' => __('Thời gian') . ' *',
            'options' => get_countdown_options(),
            'empty' => __('Chọn thời gian'),
            'class' => 'form-control'
        ]); ?>
        <p class="help-block"><?= __('Thời gian bộ đếm ngược hiển thị code vượt link. Thời gian khác nhau sẽ có mức giá khác nhau.') ?></p>

        <?= $this->Form->control('daily_view_limit', [
            'label' => __('Số lượng view trong ngày') . ' *',
            'type' => 'number',
            'class' => 'form-control',
            'min' => 1
        ]); ?>
        <p class="help-block"><?= __('Số lượng view tối đa trong 1 ngày') ?></p>

        <?= $this->Form->control('total_view_limit', [
            'label' => __('Tổng view mua') . ' *',
            'type' => 'number',
            'class' => 'form-control',
            'min' => 1
        ]); ?>
        <p class="help-block"><?= __('Tổng view mua khi 1 ngày không dùng hết sẽ chuyển sang ngày hôm sau để view tiếp') ?></p>

        <div class="form-group">
            <label><?= $this->Form->checkbox('view_by_hour'); ?> <?= __('View theo giờ') ?></label>
            <p class="help-block"><?= __('Chia view theo giờ: Số view ngày/24h') ?></p>
        </div>

        <legend><?= __('Website & Từ khóa') ?></legend>

        <?= $this->Form->control('keyword_or_url', [
            'label' => __('Từ khóa hoặc URL') . ' *',
            'type' => 'text',
            'class' => 'form-control',
            'placeholder' => __('Nhập từ khóa. Ví dụ: traffic24h')
        ]); ?>
        <p class="help-block">
            <?= __('Loại traffic Google Search: từ khóa để tìm kiếm từ Google vào website.') ?><br>
            <?= __('Loại traffic Backlink: từ khóa hoặc link click để vào website.') ?>
        </p>

        <?= $this->Form->control('website_title', [
            'label' => __('Title'),
            'class' => 'form-control'
        ]); ?>

        <?= $this->Form->control('website_url', [
            'label' => __('Địa chỉ trang web') . ' *',
            'type' => 'url',
            'class' => 'form-control',
            'placeholder' => __('Nhập địa chỉ web. Ví dụ: https://traffic24h.top/')
        ]); ?>
        <p class="help-block"><?= __('Nhập địa chỉ trang web cần view - khi vào đúng trang web đó thì mới tính view để trả tiền') ?></p>

        <legend><?= __('Hình ảnh') ?></legend>

        <?= $this->Form->control('image_1_file', [
            'label' => __('Image 1') . ' *',
            'type' => 'file',
            'class' => 'form-control'
        ]); ?>
        <p class="help-block"><?= __('Image 1: hình ảnh tìm kiếm từ khóa theo Google hoặc từ backlink sau đó click vào web') ?></p>

        <?= $this->Form->control('image_2_file', [
            'label' => __('Image 2'),
            'type' => 'file',
            'class' => 'form-control'
        ]); ?>
        <p class="help-block"><?= __('Image 2: (ảnh phụ) có thể điền hoặc không điền') ?></p>

        <legend><?= __('Anchors') ?></legend>

        <div class="form-group">
            <label><?= __('Anchors') . ' *' ?></label>
            <?= $this->Form->control('anchor_mode', [
                'label' => false,
                'type' => 'radio',
                'options' => ['default' => __('Mặc định'), 'specify' => __('Chỉ định')],
                'value' => 'default'
            ]); ?>
            <p class="help-block"><?= __('Chọn mặc định sẽ do khách click random trong trang web khi đếm giây bước 1 xong. Nếu muốn chỉ định anchor text, link click thì chọn Chỉ định và điền thông tin bên dưới.') ?></p>
        </div>

        <div id="anchor-specify-fields" style="display: none;">
            <?= $this->Form->control('anchor_text', [
                'label' => __('Anchor text'),
                'class' => 'form-control'
            ]); ?>
            <?= $this->Form->control('anchor_link', [
                'label' => __('Link anchor'),
                'type' => 'url',
                'class' => 'form-control'
            ]); ?>
        </div>

        <legend><?= __('Mã giảm giá & Ghi chú') ?></legend>

        <?= $this->Form->control('discount_code', [
            'label' => __('Mã giảm giá'),
            'class' => 'form-control',
            'value' => 'DISCOUNT_40'
        ]); ?>
        <p class="help-block text-success"><?= __('Chú ý: Nhờ áp mã giảm giá để được giá tốt nhất. (DISCOUNT_40)') ?></p>

        <?= $this->Form->control('note', [
            'label' => __('Ghi chú') . ' *',
            'type' => 'textarea',
            'class' => 'form-control',
            'placeholder' => __('Ghi chú: Mã đơn hàng...')
        ]); ?>
        <p class="help-block"><?= __('Ghi chú thông tin đơn hàng') ?></p>

        <legend><?= __('Giá dịch vụ') ?></legend>
        <p class="help-block"><?= __('View từ đâu cũng tính. Giá tự động theo Tổng view và Thời gian countdown.') ?></p>
        <p class="help-block"><?= __('Chia 50-50: Member nhận 50%, nền tảng 50% mỗi view.') ?></p>

        <?= $this->Form->hidden('campaign_items.0.country', ['value' => 'all']); ?>
        <?= $this->Form->hidden('campaign_items.0.purchase', ['id' => 'campaign-purchase']); ?>
        <?= $this->Form->hidden('campaign_items.0.advertiser_price', ['id' => 'campaign-advertiser-price']); ?>
        <?= $this->Form->hidden('campaign_items.0.publisher_price', ['id' => 'campaign-publisher-price']); ?>

        <div class="well" id="price-summary">
            <h4><?= __('Bảng giá') ?></h4>
            <p><strong><?= __('Giá / 1.000 view') ?>:</strong> <span id="price-per-1000">-</span></p>
            <p><strong><?= __('Member nhận / 1 view') ?>:</strong> <span id="publisher-per-view">-</span> (50%)</p>
            <p><strong><?= __('Tổng tiền') ?>:</strong> <span id="total-price" class="text-success" style="font-size: 20px;">0.00</span> <?= h(get_option('currency_symbol', '$')) ?></p>
        </div>

        <div class="text-center">
            <?= $this->Form->button(__('Tạo chiến dịch'), ['class' => 'btn btn-success btn-lg']); ?>
        </div>

        <?= $this->Form->end(); ?>
    </div>
</div>

<?php $this->start('scriptBottom'); ?>
<script>
var priceMatrix = <?= json_encode($price_matrix) ?>;
var currencySymbol = <?= json_encode(get_option('currency_symbol', '$')) ?>;

function updatePrice() {
    var version = parseInt($('input[name="campaign_version"]:checked').val(), 10) || 1;
    var countdown = parseInt($('select[name="countdown_seconds"]').val(), 10) || 60;
    var total = parseInt($('input[name="total_view_limit"]').val(), 10) || 0;
    var verPrices = priceMatrix[version] || priceMatrix[1];
    var prices = verPrices[countdown] || verPrices[60] || {advertiser: 420, publisher: 210};
    var totalPrice = (prices.advertiser * total / 1000).toFixed(0); // giá × tổng view / 1000
    var units = Math.max(1, Math.ceil(total / 1000)); // purchase cho campaign_item
    var per1000 = prices.advertiser.toFixed(2);
    var perView = (prices.publisher / 1000).toFixed(4);

    $('#price-per-1000').text(currencySymbol + per1000);
    $('#publisher-per-view').text(currencySymbol + perView);
    $('#total-price').text(totalPrice);

    $('#campaign-purchase').val(units);
    $('#campaign-advertiser-price').val(prices.advertiser);
    $('#campaign-publisher-price').val(prices.publisher);
}

$(document).ready(function() {
    $('input[name="anchor_mode"]').on('change', function() {
        var v = $(this).val();
        $('#anchor-specify-fields').toggle(v === 'specify');
    });
    if ($('input[name="anchor_mode"]:checked').val() === 'specify') {
        $('#anchor-specify-fields').show();
    }
    $('select[name="countdown_seconds"], input[name="total_view_limit"], input[name="campaign_version"]').on('change input', updatePrice);
    updatePrice();
});
</script>
<?php $this->end(); ?>
