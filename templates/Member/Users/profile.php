<?php
$this->assign('title', __('Profile'));
$this->assign('description', '');
$this->assign('content_title', __('Profile'));

?>

<div class="box box-primary">
    <div class="box-body">

        <?= $this->Form->create($user); ?>

        <?= $this->Form->hidden('id'); ?>

        <legend><?= __('Billing Address') ?></legend>

        <div class="row">
            <div class="col-sm-6">
                <?=
                $this->Form->input('first_name', [
                    'label' => __('First Name'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
            <div class="col-sm-6">
                <?=
                $this->Form->input('last_name', [
                    'label' => __('Last Name'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <?=
                $this->Form->input('address1', [
                    'label' => __('Address 1'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
            <div class="col-sm-6">
                <?=
                $this->Form->input('address2', [
                    'label' => __('Address 2'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <?=
                $this->Form->input('city', [
                    'label' => __('City'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
            <div class="col-sm-6">
                <?=
                $this->Form->input('state', [
                    'label' => __('State'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <?=
                $this->Form->input('zip', [
                    'label' => __('ZIP'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
            <div class="col-sm-6">
                <?=
                $this->Form->input('country', [
                    'label' => __('Country'),
                    'options' => get_countries(),
                    'empty' => __('Choose'),
                    'class' => 'form-control'
                ]);

                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <?=
                $this->Form->input('phone_number', [
                    'label' => __('Phone Number'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
        </div>

        <legend><?= __('Withdrawal Info') ?></legend>

        <h4><?= __('Traffic Source') ?> (<?= __('Nguồn lưu lượng') ?>)</h4>
        <p class="help-block"><?= __('Add your traffic sources (YouTube, TikTok, Telegram, game docs, etc.) for withdrawal verification.') ?></p>
        <div id="traffic-sources-container">
            <?php
            $sources = $user->traffic_sources ?? [];
            if (empty($sources)) {
                $sources = [['type' => '', 'url' => '']];
            }
            $sourceTypes = [
                'youtube' => __('kênh youtube'),
                'tiktok' => __('kênh tiktok'),
                'telegram' => __('telegram'),
                'game' => __('tài liệu game'),
                'other' => __('khác'),
            ];
            foreach ($sources as $i => $src):
                $src = is_array($src) ? $src : ['type' => '', 'url' => ''];
            ?>
            <div class="traffic-source-row form-group row" style="margin-bottom: 8px;">
                <div class="col-sm-4">
                    <select name="traffic_sources[<?= $i ?>][type]" class="form-control traffic-source-type">
                        <option value=""><?= __('Choose') ?></option>
                        <?php foreach ($sourceTypes as $k => $v): ?>
                        <option value="<?= h($k) ?>" <?= ($src['type'] ?? '') === $k ? 'selected' : '' ?>><?= h($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <input type="url" name="traffic_sources[<?= $i ?>][url]" class="form-control" placeholder="https://..."
                        value="<?= h($src['url'] ?? '') ?>">
                </div>
                <div class="col-sm-2">
                    <button type="button" class="btn btn-default btn-sm btn-remove-source" title="<?= __('Remove') ?>">×</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="btn-add-traffic-source" class="btn btn-sm btn-default">+ <?= __('Add source') ?></button>

        <?php
        $withdrawal_methods = [];
        
        if( (bool) get_option('wallet_enable', false) ) {
            $withdrawal_methods['wallet'] = __('My Wallet');
        }
        
        if(get_option('paypal_enable', 'no') == 'yes' ) {
            $withdrawal_methods['paypal'] = __('PayPal');
        }
        
        if(get_option('payza_enable', 'no') == 'yes' ) {
            $withdrawal_methods['payza'] = __('Payza');
        }
        
        if( (bool) get_option('skrill_enable', false) ) {
            $withdrawal_methods['skrill'] = __('Skrill');
        }
        
        if(get_option('coinbase_enable', 'no') == 'yes' ) {
            $withdrawal_methods['coinbase'] = __('Bitcoin');
        }
        
        if (get_option('webmoney_enable', 'no') == 'yes') {
            $withdrawal_methods['webmoney'] = __('Web Money');
        }
        
        if(get_option('banktransfer_enable', 'no') == 'yes' ) {
            $withdrawal_methods['banktransfer'] = __('Bank Transfer');
        }
        ?>
        
        <?=
        $this->Form->input('withdrawal_method', [
            'label' => __('Withdrawal Method'),
            'options' => $withdrawal_methods,
            'empty' => __('Choose'),
            'class' => 'form-control'
        ]);

        ?>

        <?=
        $this->Form->input('withdrawal_account', [
            'label' => __('Withdrawal Account'),
            'class' => 'form-control',
            'type' => 'textarea',
            'required' => false
        ])

        ?>
        <div class="help-block">
            <p><?= __('- For PayPal, Payza and Skrill add your email.') ?></p>
            <p><?= __('- For Coinbase add your wallet address.') ?></p>
            <p><?= __('- For Web Money add your purse.') ?></p>
            <p><?= __('- For bank transfer add your account holder name, Bank Name, City/Town, Country, Account number, SWIFT, IBAN and Account currency') ?></p>
        </div>

        <?= $this->Form->button(__('Submit'), [ 'class' => 'btn btn-primary btn-lg']); ?>

        <?= $this->Form->end() ?>

    </div>
</div>

<?php
$sourceTypesJson = json_encode([
    'youtube' => __('kênh youtube'),
    'tiktok' => __('kênh tiktok'),
    'telegram' => __('telegram'),
    'game' => __('tài liệu game'),
    'other' => __('khác'),
]);
$this->Html->scriptBlock(<<<JS
(function() {
    var sourceTypes = {$sourceTypesJson};
    var idx = document.querySelectorAll('.traffic-source-row').length;
    var container = document.getElementById('traffic-sources-container');
    var addBtn = document.getElementById('btn-add-traffic-source');

    addBtn.addEventListener('click', function() {
        var opts = '<option value="">' + (document.documentElement.lang === 'vi' ? 'Chọn' : 'Choose') + '</option>';
        for (var k in sourceTypes) opts += '<option value="' + k + '">' + sourceTypes[k] + '</option>';
        var row = document.createElement('div');
        row.className = 'traffic-source-row form-group row';
        row.style.marginBottom = '8px';
        row.innerHTML = '<div class="col-sm-4"><select name="traffic_sources[' + idx + '][type]" class="form-control">' + opts + '</select></div>' +
            '<div class="col-sm-6"><input type="url" name="traffic_sources[' + idx + '][url]" class="form-control" placeholder="https://..."></div>' +
            '<div class="col-sm-2"><button type="button" class="btn btn-default btn-sm btn-remove-source" title="Remove">×</button></div>';
        container.appendChild(row);
        idx++;
        row.querySelector('.btn-remove-source').addEventListener('click', function() { row.remove(); });
    });

    container.querySelectorAll('.btn-remove-source').forEach(function(btn) {
        btn.addEventListener('click', function() { btn.closest('.traffic-source-row').remove(); });
    });
})();
JS
, ['block' => 'scriptBottom']);
?>
