<?php

namespace App\Controller;

use App\Controller\FrontController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\Cache\Cache;

class PagesController extends FrontController
{

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['home', 'view']);
    }

    public function home()
    {
        $this->loadModel('Users');

        /*
          $todayClicks = $this->Users->Statistics->find()
          ->where([
          'DATE(Statistics.created) = CURDATE()'
          ])
          ->count();
          $this->set('todayClicks', $todayClicks);
         */

        $lang = locale_get_default();

        //if (($totalLinks = Cache::read('home_totalLinks_' . $lang, '1day')) === false) {
            $totalLinks = $this->Users->Links->find()
                ->where(['id >= 1'])
                ->count();

            $totalLinks += (int) get_option('fake_links', 0);
            
            $totalLinks = display_price_currency($totalLinks, [
                'places' => 0,
                'before' => '',
                'after' => '',
            ]);

            //Cache::write('home_totalLinks_' . $lang, $totalLinks, '1day');
        //}

        $this->set('totalLinks', $totalLinks);

        //if (($totalClicks = Cache::read('home_totalClicks_' . $lang, '1day')) === false) {
            $totalClicks = $this->Users->Statistics->find()
                ->where([
                    'id >=' => 1,
                    'ad_type <>' => 3
                ])
                ->count();

            $totalClicks += (int) get_option('fake_clicks', 0);
            
            $totalClicks = display_price_currency($totalClicks, [
                'places' => 0,
                'before' => '',
                'after' => '',
            ]);

            //Cache::write('home_totalClicks_' . $lang, $totalClicks, '1day');
        //}

        $this->set('totalClicks', $totalClicks);

        //if (($totalUsers = Cache::read('home_totalUsers_' . $lang, '1day')) === false) {
            $totalUsers = $this->Users->find()
                ->where(['id >= 1'])
                ->count();

            $totalUsers += (int) get_option('fake_users', 0);

            $totalUsers = display_price_currency($totalUsers, [
                'places' => 0,
                'before' => '',
                'after' => '',
            ]);

           // Cache::write('home_totalUsers_' . $lang, $totalUsers, '1day');
        //}

        $this->set('totalUsers', $totalUsers);
    }

    public function view($slug = null)
    {
        if (!$slug) {
            throw new NotFoundException(__('Invalid Page.'));
        }

        $page = $this->Pages->find()->where(['slug' => $slug, 'published' => 1])->first();

        if (!$page) {
            throw new NotFoundException(__('Invalid Page.'));
        }

        if (strpos($page->content, '[advertising_rates]') !== false) {
            $page->content = str_replace('[advertising_rates]', $this->advertisingRates(), $page->content);
        }

        if (strpos($page->content, '[payout_rates]') !== false) {
            $page->content = str_replace('[payout_rates]', $this->payoutRates(), $page->content);
        }

        $this->set('page', $page);
    }

    protected function advertisingRates()
    {
        $lang = locale_get_default();
        
        if (($advertisingRates = Cache::read('advertising_rates_'.$lang, '1day')) === false) {

            ob_start();

            ?>
            <div class="advertising-rates">

                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <?php if (get_option('enable_interstitial', 'yes') == 'yes') : ?>
                        <li role="presentation"><a href="#interstitial" aria-controls="interstitial" role="tab" data-toggle="tab"><?= __('Interstitial') ?></a></li>
                    <?php endif; ?>
                    <?php if (get_option('enable_banner', 'yes') == 'yes') : ?>
                        <li role="presentation"><a href="#banner-ads" aria-controls="banner-ads" role="tab" data-toggle="tab"><?= __('Banner') ?></a></li>
                    <?php endif; ?>
                    <?php if (get_option('enable_popup', 'yes') == 'yes') : ?>
                        <li role="presentation"><a href="#popup-ads" aria-controls="popup-ads" role="tab" data-toggle="tab"><?= __('Popup') ?></a></li>
                    <?php endif; ?>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <?php if (get_option('enable_interstitial', 'yes') == 'yes') : ?>
                        <div role="tabpanel" class="tab-pane" id="interstitial">
                            <p class="help-block"><?= __('View từ đâu cũng tính. Giá đã giảm 40%%. Chia 50-50 với member.') ?></p>
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><?= __('Thời gian') ?></th>
                                        <th><?= __('Version 1 (2 bước)') ?> <span class="label label-primary"><?= __('Tốt nhất') ?></span></th>
                                        <th><?= __('Version 2 (1 bước)') ?></th>
                                    </tr>
                                </thead>
                                <?php foreach (get_countdown_options() as $sec => $label) : ?>
                                    <?php $p1 = calc_interstitial_price_per_1000($sec, 1); $p2 = calc_interstitial_price_per_1000($sec, 2); ?>
                                    <tr>
                                        <td><?= h($label) ?></td>
                                        <td><?= display_price_currency($p1['advertiser']); ?> / 1.000</td>
                                        <td><?= display_price_currency($p2['advertiser']); ?> / 1.000</td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
<?php endif; ?>
                    <?php if (get_option('enable_banner', 'yes') == 'yes') : ?>
                        <div role="tabpanel" class="tab-pane" id="banner-ads">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><?= __('Package Description / Country') ?></th>
                                        <th><?= __('Price 1,000') ?></th>
                                    </tr>
                                </thead>
                                <?php
                                $banner_price = get_option('banner_price');
                                $countries = get_countries(true);
                                $banner_prices = [];
                                foreach ($banner_price as $key => $value) {
                                    if (!empty($value['advertiser'])) {
                                        $banner_prices[$key] = $value['advertiser'];
                                    }
                                }
                                //arsort($advertiser_prices);

                                ?>
                                <?php foreach ($banner_prices as $key => $value) : ?>
                                    <tr>
                                        <td><?= $countries[$key] ?></td>
                                        <td><?= display_price_currency($value); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
<?php endif; ?>
                    <?php if (get_option('enable_popup', 'yes') == 'yes') : ?>
                        <div role="tabpanel" class="tab-pane" id="popup-ads">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><?= __('Package Description / Country') ?></th>
                                        <th><?= __('Price 1,000') ?></th>
                                    </tr>
                                </thead>
                                <?php
                                $banner_price = get_option('popup_price');
                                $countries = get_countries(true);
                                $banner_prices = [];
                                foreach ($banner_price as $key => $value) {
                                    if (!empty($value['advertiser'])) {
                                        $banner_prices[$key] = $value['advertiser'];
                                    }
                                }
                                //arsort($advertiser_prices);

                                ?>
                                <?php foreach ($banner_prices as $key => $value) : ?>
                                    <tr>
                                        <td><?= $countries[$key] ?></td>
                                        <td><?= display_price_currency($value); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
<?php endif; ?>
                </div>

            </div>

            <?php
            $advertisingRates = ob_get_contents();
            ob_end_clean();


            Cache::write('advertising_rates_'.$lang, $advertisingRates, '1day');
        }


        return $advertisingRates;
    }

    protected function payoutRates()
    {
        $lang = locale_get_default();
        
        if (($payoutRates = Cache::read('payout_rates_'.$lang, '1day')) === false) {
            ob_start();

            ?>
            <div class="payout-rates">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <?php if (get_option('enable_interstitial', 'yes') == 'yes') : ?>
                        <li role="presentation"><a href="#interstitial" aria-controls="interstitial" role="tab" data-toggle="tab"><?= __('Interstitial') ?></a></li>
                    <?php endif; ?>
                    <?php if (get_option('enable_banner', 'yes') == 'yes') : ?>
                        <li role="presentation"><a href="#banner-ads" aria-controls="banner-ads" role="tab" data-toggle="tab"><?= __('Banner') ?></a></li>
                    <?php endif; ?>
                    <?php if (get_option('enable_popup', 'yes') == 'yes') : ?>
                            <li role="presentation"><a href="#popup-ads" aria-controls="popup-ads" role="tab" data-toggle="tab"><?= __('Popup') ?></a></li>
                        <?php endif; ?>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <?php if (get_option('enable_interstitial', 'yes') == 'yes') : ?>
                        <div role="tabpanel" class="tab-pane" id="interstitial">
                            <p class="help-block"><?= __('Member nhận 50%% mỗi view.') ?></p>
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><?= __('Thời gian') ?></th>
                                        <th><?= __('Version 1 - Member / 1.000') ?></th>
                                        <th><?= __('Version 2 - Member / 1.000') ?></th>
                                    </tr>
                                </thead>
                                <?php foreach (get_countdown_options() as $sec => $label) : ?>
                                    <?php $p1 = calc_interstitial_price_per_1000($sec, 1); $p2 = calc_interstitial_price_per_1000($sec, 2); ?>
                                    <tr>
                                        <td><?= h($label) ?></td>
                                        <td><?= display_price_currency($p1['publisher']); ?></td>
                                        <td><?= display_price_currency($p2['publisher']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endif; ?>
                    <?php if (get_option('enable_banner', 'yes') == 'yes') : ?>
                        <div role="tabpanel" class="tab-pane" id="banner-ads">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><?= __('Package Description / Country') ?></th>
                                        <th><?= __('Price 1,000') ?></th>
                                    </tr>
                                </thead>
                                <?php
                                $banner_prices = get_option('banner_price');
                                $countries = get_countries(true);
                                $publisher_prices = [];
                                foreach ($banner_prices as $key => $value) {
                                    if (!empty($value['advertiser'])) {
                                        $publisher_prices[$key] = $value['publisher'];
                                    }
                                }
                                arsort($publisher_prices);

                                ?>
                                <?php foreach ($publisher_prices as $key => $value) : ?>
                                    <tr>
                                        <td><?= $countries[$key] ?></td>
                                        <td><?= display_price_currency($value); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endif; ?>
                    <?php if (get_option('enable_popup', 'yes') == 'yes') : ?>
                        <div role="tabpanel" class="tab-pane" id="popup-ads">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><?= __('Package Description / Country') ?></th>
                                        <th><?= __('Price 1,000') ?></th>
                                    </tr>
                                </thead>
                                <?php
                                $banner_prices = get_option('popup_price');
                                $countries = get_countries(true);
                                $publisher_prices = [];
                                foreach ($banner_prices as $key => $value) {
                                    if (!empty($value['advertiser'])) {
                                        $publisher_prices[$key] = $value['publisher'];
                                    }
                                }
                                arsort($publisher_prices);

                                ?>
                                <?php foreach ($publisher_prices as $key => $value) : ?>
                                    <tr>
                                        <td><?= $countries[$key] ?></td>
                                        <td><?= display_price_currency($value); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            
            </div>

            <?php
            $payoutRates = ob_get_contents();
            ob_end_clean();

            Cache::write('payout_rates_'.$lang, $payoutRates, '1day');
        }
        return $payoutRates;
    }
}
