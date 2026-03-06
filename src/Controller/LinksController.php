<?php

namespace App\Controller;

use App\Controller\FrontController;
use App\Service\CampaignVerificationService;
use App\Service\ProxyCheckService;
use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Security;

class LinksController extends FrontController
{

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Cookie');
        $this->loadComponent('Recaptcha');
        // Unlock go/popad/shorten from form protection to avoid token expiry breaking AJAX/form submissions
        $this->FormProtection->setConfig('unlockedActions', ['go', 'popad', 'shorten'], true);
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->viewBuilder()->setLayout('front');
        $this->Auth->allow(['shorten', 'st', 'api', 'view', 'go', 'stats', 'popad', 'code', 'landing', 'finalAd', 'r', 'campaignVerificationScript']);
    }

    public function campaignVerificationScript($token = null)
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/javascript');

        if (empty($token) || !preg_match('/^[a-f0-9]{32}$/', (string)$token)) {
            return $this->response->withStringBody('// invalid verification token');
        }

        $code = CampaignVerificationService::generateCode($token);
        $js = "(function(){window.adlinkflyVerify=window.adlinkflyVerify||{};window.adlinkflyVerify['{$token}']='{$code}';})();";

        return $this->response->withStringBody($js);
    }

    public function shorten()
    {
        $this->autoRender = false;

        $this->response = $this->response->withType('json');

        if (!$this->request->is('ajax')) {
            $content = [
                'status' => 'error',
                'message' => __('Bad Request.'),
                'url' => ''
            ];
            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        }

        $user_id = 1;
        if (null !== $this->Auth->user('id')) {
            $user_id = $this->Auth->user('id');
        }


        if ($user_id === 1 && (bool) get_option('enable_captcha_shortlink_anonymous', false) && isset_recaptcha() && !$this->Recaptcha->verify($this->request->getData('g-recaptcha-response'))) {
            $content = [
                'status' => 'error',
                'message' => __('The CAPTCHA was incorrect. Try again'),
                'url' => ''
            ];
            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        }


        if ($user_id == 1 && get_option('home_shortening_register') === 'yes') {
            $content = [
                'status' => 'error',
                'message' => __('Bad Request.'),
                'url' => ''
            ];
            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        }

        $user = $this->Links->Users->find()->where(['status' => 1, 'id' => $user_id])->first();

        if (!$user) {
            $content = [
                'status' => 'error',
                'message' => __('Invalid user'),
                'url' => ''
            ];
            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        }

        $reqData = $this->request->getData();
        $url = trim($reqData['url'] ?? '');
        $url = str_replace(" ", "%20", $url);
        $url = parse_url($url, PHP_URL_SCHEME) === null ? 'http://' . $url : $url;

        $domain = '';
        if (!empty($reqData['domain'])) {
            $domain = $reqData['domain'];
        }
        if (!in_array($domain, get_multi_domains_list())) {
            $domain = '';
        }

        $linkWhere = [
            'user_id' => $user->id,
            'status' => 1,
            'ad_type' => $reqData['ad_type'] ?? '',
            'url' => $url
        ];

        if (!empty($reqData['alias'])) {
            $linkWhere['alias'] = $reqData['alias'];
        }

        $link = $this->Links->find()->where($linkWhere)->first();

        if ($link) {
            $content = [
                'status' => 'success',
                'message' => '',
                'url' => get_short_url($link->alias, $domain)
            ];
            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        }

        $link = $this->Links->newEntity([]);
        $data = [];

        $data['user_id'] = $user->id;
        $data['url'] = $url;

        $data['domain'] = $domain;

        if (empty($reqData['alias'])) {
            $data['alias'] = $this->Links->geturl();
        } else {
            $data['alias'] = $reqData['alias'];
        }

        $data['ad_type'] = $reqData['ad_type'] ?? '';
        $link->status = 1;
        $link->hits = 0;

        $linkMeta = [
            'title' => '',
            'description' => '',
            'image' => ''
        ];

        if ($user_id === 1 && get_option('disable_meta_home') === 'no') {
            $linkMeta = $this->Links->getLinkMeta($data['url']);
        }

        if ($user_id !== 1 && get_option('disable_meta_member') === 'no') {
            $linkMeta = $this->Links->getLinkMeta($data['url']);
        }

        $link->title = $linkMeta['title'];
        $link->description = $linkMeta['description'];
        $link->image = $linkMeta['image'];


        $link = $this->Links->patchEntity($link, $data);
        if ($this->Links->save($link)) {
            $content = [
                'status' => 'success',
                'message' => '',
                'url' => get_short_url($link->alias, $domain)
            ];
            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        }

        $message = __('Invalid URL.');
        if ($link->errors()) {
            $error_msg = [];
            foreach ($link->errors() as $errors) {
                if (is_array($errors)) {
                    foreach ($errors as $error) {
                        $error_msg[] = $error;
                    }
                } else {
                    $error_msg[] = $errors;
                }
            }

            if (!empty($error_msg)) {
                $message = implode("<br>", $error_msg);
            }
        }

        $content = [
            'status' => 'error',
            'message' => $message,
            'url' => ''
        ];
        $this->response = $this->response->withStringBody(json_encode($content));
        return $this->response;
    }

    public function st()
    {
        $this->autoRender = false;

        $message = '';

        $query = $this->request->getQueryParams();
        if (empty($query['api']) || empty($query['url'])) {
            $message = __('Invalid Request.');
            $this->set('message', $message);
            return;
        }

        $api = $query['api'];
        $url = urldecode($query['url']);

        $ad_type = get_option('member_default_advert', 1);
        if (!empty($query['type']) && array_key_exists($query['type'], get_allowed_ads())) {
            $ad_type = $query['type'];
        }

        $user = $this->Links->Users->find()->where(['api_token' => $api, 'status' => 1])->first();

        if (!$user) {
            $message = __('Invalid API token.');
            $this->set('message', $message);
            return;
        }

        $url = trim($url);
        $url = str_replace(" ", "%20", $url);
        $url = parse_url($url, PHP_URL_SCHEME) === null ? 'http://' . $url : $url;

        $link = $this->Links->find()->where([
            'user_id' => $user->id,
            'status' => 1,
            'ad_type' => $ad_type,
            'url' => $url
        ])->first();

        if ($link) {
            return $this->redirect(get_short_url($link->alias));
        }

        $link = $this->Links->newEntity([]);
        $data = [];

        $data['user_id'] = $user->id;
        $data['url'] = $url;
        $data['alias'] = $this->Links->geturl();
        $data['ad_type'] = $ad_type;

        $link->status = 1;
        $link->hits = 0;

        $linkMeta = [
            'title' => '',
            'description' => '',
            'image' => ''
        ];

        if (get_option('disable_meta_api') === 'no') {
            $linkMeta = $this->Links->getLinkMeta($url);
        }

        $link->title = $linkMeta['title'];
        $link->description = $linkMeta['description'];
        $link->image = $linkMeta['image'];

        $link = $this->Links->patchEntity($link, $data);
        if ($this->Links->save($link)) {
            return $this->redirect(get_short_url($link->alias));
        }

        $message = __('Error.');
        $this->set('message', $message);
        return;
    }

    public function api()
    {
        $this->autoRender = false;

        $format = 'json';
        $formatParam = $this->request->getQuery('format');
        if ($formatParam !== null && strtolower($formatParam) === 'text') {
            $format = 'text';
        }
        $this->response = $this->response->withType($format);

        $query = $this->request->getQueryParams();
        if (empty($query['api']) || empty($query['url'])) {
            $content = [
                'status' => 'error',
                'message' => 'Invalid API call',
                'shortenedUrl' => ''
            ];
            $this->response = $this->response->withStringBody($this->apiContent($content, $format));
            return $this->response;
        }

        $api = $query['api'];
        $url = urldecode($query['url']);

        $ad_type = get_option('member_default_advert', 1);
        if (!empty($query['type']) && array_key_exists($query['type'], get_allowed_ads())) {
            $ad_type = $query['type'];
        }

        $user = $this->Links->Users->find()->where(['api_token' => $api, 'status' => 1])->first();

        if (!$user) {
            $content = [
                'status' => 'error',
                'message' => 'Invalid API token',
                'shortenedUrl' => ''
            ];
            $this->response = $this->response->withStringBody($this->apiContent($content, $format));
            return $this->response;
        }

        $url = trim($url);
        $url = str_replace(" ", "%20", $url);
        $url = parse_url($url, PHP_URL_SCHEME) === null ? 'http://' . $url : $url;

        $link = $this->Links->find()->where([
            'url' => $url,
            'user_id' => $user->id,
            'ad_type' => $ad_type
        ])->first();

        if ($link) {
            $content = [
                'status' => 'success',
                'shortenedUrl' => get_short_url($link->alias, $link->domain)
            ];
            $this->response = $this->response->withStringBody($this->apiContent($content, $format));
            return $this->response;
        }

        $link = $this->Links->newEntity([]);
        $data = [];

        $data['user_id'] = $user->id;
        $data['url'] = $url;
        if (empty($query['alias'])) {
            $data['alias'] = $this->Links->geturl();
        } else {
            $data['alias'] = $query['alias'];
        }
        $data['ad_type'] = $ad_type;

        $link->status = 1;
        $link->hits = 0;

        $linkMeta = [
            'title' => '',
            'description' => '',
            'image' => ''
        ];

        if (get_option('disable_meta_api') === 'no') {
            $linkMeta = $this->Links->getLinkMeta($url);
        }

        $link->title = $linkMeta['title'];
        $link->description = $linkMeta['description'];
        $link->image = $linkMeta['image'];

        $link = $this->Links->patchEntity($link, $data);

        if ($this->Links->save($link)) {
            $content = [
                'status' => 'success',
                'message' => '',
                'shortenedUrl' => get_short_url($link->alias, $link->domain)
            ];
            $this->response = $this->response->withStringBody($this->apiContent($content, $format));
            return $this->response;
        }

        $content = [
            'status' => 'error',
            'message' => 'Invalid URL',
            'shortenedUrl' => ''
        ];
        $this->response = $this->response->withStringBody($this->apiContent($content, $format));
        return $this->response;
    }

    protected function apiContent($content = [], $format = 'json')
    {
        $body = json_encode($content);
        if ($format === 'text') {
            $body = $content['shortenedUrl'];
        }
        return $body;
    }

    public function view($alias = null)
    {
        if (!$alias) {
            throw new NotFoundException(__('Invalid link'));
        }

        //$link = $this->Links->find()->where( ['alias' => $alias, 'status' => 1] )->contain(['Users'])->first();
        $link = $this->Links->find()->contain(['Users'])->where(['Links.alias' => $alias, 'Links.status <>' => 3])->first();
        if (!$link) {
            throw new NotFoundException(__('404 Not Found'));
        }
        $this->set('link', $link);

        $user = $this->Links->Users->find()->where(['id' => $link->user_id, 'status' => 1])->first();
        if (!$user) {
            throw new NotFoundException(__('404 Not Found'));
        }

        // ProxyCheck: block VPN/Proxy before showing interstitial
        if (!ProxyCheckService::checkIp(get_ip())) {
            $blockUrl = get_option('proxycheck_block_url', '');
            if (!empty($blockUrl)) {
                return $this->redirect($blockUrl);
            }
            $this->viewBuilder()->setLayout('front');
            $this->response = $this->response->withStatus(403);
            $this->set('message', __('Your IP appears to be using a VPN or proxy. Please disable it to continue.'));
            return $this->render('blocked');
        }

        // No Ads
        if ($link->ad_type == 0) {
            $this->_updateLinkHits($link);
            $this->_addNormalStatisticEntry($link, $link->ad_type, [
                'ci' => 0,
                'cui' => 0,
                'cii' => 0,
                'ref' => (env('HTTP_REFERER')) ? env('HTTP_REFERER') : '',
            ], get_ip(), 10);
            return $this->redirect($link->url);
        }

        $this->viewBuilder()->setLayout('captcha');
        $this->render('captcha');

        if (!((get_option('enable_captcha_shortlink') == 'yes') && isset_recaptcha()) || $this->request->is('post')) {

            if ((get_option('enable_captcha_shortlink') == 'yes') && isset_recaptcha() && !$this->Recaptcha->verify($this->request->getData('g-recaptcha-response'))) {
                throw new BadRequestException(__('The CAPTCHA was incorrect. Try again'));
            }

            //env('HTTP_REFERER', $this->request->getData('ref'));

            $_SERVER['HTTP_REFERER'] = (!((get_option('enable_captcha_shortlink') == 'yes') && isset_recaptcha())) ? env('HTTP_REFERER') : $this->request->getData('ref');

            $this->_setVisitorCookie();

            $country = $this->Links->Statistics->get_country(get_ip());

            $traffic_source = get_traffic_source_from_referrer();

            $campaign_item = $this->_getCampaignItem($link->ad_type, $traffic_source, $country);
            if (!$campaign_item) {
                Log::warning('No campaign for interstitial', [
                    'alias' => $alias,
                    'ad_type' => $link->ad_type,
                    'traffic_source' => $traffic_source,
                    'country' => $country,
                ]);
                $this->Flash->warning(__('No campaign available. Redirecting to destination.'));
                return $this->redirect($link->url);
            }
            $this->set('campaign_item', $campaign_item);

            // Generate code for interstitial (Link4M-style flow)
            // Reuse existing code if still valid - user may have navigated to landing and back
            // Also store in cookie as fallback (session may not be sent with AJAX on some domains)
            if ($link->ad_type == 1) {
                $session_time = (int) get_option('interstitial_session_time', 600);
                $expiryTime = time() + $session_time;
                $existingCode = $this->request->getSession()->read('link_code_' . $alias);
                $existingExpiry = $this->request->getSession()->read('link_code_start_' . $alias);
                $cookieKey = 'lc_' . md5($alias);
                if ($existingCode && $existingExpiry && time() < $existingExpiry) {
                    $link_code = $existingCode;
                    $expiryTime = $existingExpiry;
                } else {
                    $link_code = strtoupper(generate_random_string(6, false));
                    $this->request->getSession()->write('link_code_' . $alias, $link_code);
                    $this->request->getSession()->write('link_code_start_' . $alias, $expiryTime);
                }
                $this->Cookie->configKey($cookieKey, ['expires' => $expiryTime, 'path' => '/', 'httpOnly' => true]);
                $this->Cookie->write($cookieKey, $link_code . ':' . $expiryTime);
                $this->set('link_code', $link_code);
                $this->set('session_time', $session_time);
            }

            $pop_ad = [
                'link' => $link,
                'country' => $country,
                'traffic_source' => $traffic_source
            ];
            $this->set('pop_ad', $this->_encrypt($pop_ad));

            // Interstitial Ads
            if ($link->ad_type == 1) {
                $keywordTask = $this->_getKeywordTaskForCampaign($campaign_item->campaign_id);
                $this->set('keywordTask', $keywordTask);
                $this->viewBuilder()->setLayout('go_interstitial');
                $this->render('view_interstitial');
            }

            // Banner Ads
            if ($link->ad_type == 2) {

                $banner_728x90 = get_option('banner_728x90', '');
                if ('728x90' == $campaign_item->campaign->banner_size) {
                    $banner_728x90 = $campaign_item->campaign->banner_code;
                }

                $banner_468x60 = get_option('banner_468x60', '');
                if ('468x60' == $campaign_item->campaign->banner_size) {
                    $banner_468x60 = $campaign_item->campaign->banner_code;
                }

                $banner_336x280 = get_option('banner_336x280', '');
                if ('336x280' == $campaign_item->campaign->banner_size) {
                    $banner_336x280 = $campaign_item->campaign->banner_code;
                }

                $this->set('banner_728x90', $banner_728x90);
                $this->set('banner_468x60', $banner_468x60);
                $this->set('banner_336x280', $banner_336x280);

                $this->viewBuilder()->setLayout('go_banner');
                $this->render('view_banner');
            }
        }
    }

    /**
     * Page to get the code (Link4M-style - user opens this to see the code)
     */
    public function code($alias = null)
    {
        if (!$alias) {
            throw new NotFoundException(__('Invalid link'));
        }

        $link = $this->Links->find()->where(['Links.alias' => $alias, 'Links.status <>' => 3])->first();
        if (!$link || $link->ad_type != 1) {
            throw new NotFoundException(__('404 Not Found'));
        }

        $link_code = $this->request->getSession()->read('link_code_' . $alias);
        if (!$link_code && $this->Cookie->check('lc_' . md5($alias))) {
            $cookieVal = $this->Cookie->read('lc_' . md5($alias));
            if (is_string($cookieVal) && strpos($cookieVal, ':') !== false) {
                list($link_code, $expiry) = explode(':', $cookieVal, 2);
                if (time() > (int) $expiry) {
                    $link_code = null;
                }
            }
        }
        if (!$link_code) {
            throw new NotFoundException(__('Session expired. Please go back and try again.'));
        }

        $this->set('link', $link);
        $this->set('link_code', $link_code);
        $this->viewBuilder()->setLayout('go_interstitial');
    }

    /**
     * Landing page - user finds via Google search, clicks LẤY MÃ, waits 60s, gets code
     */
    public function landing($alias = null)
    {
        if (!$alias) {
            throw new NotFoundException(__('Invalid link'));
        }

        $link = $this->Links->find()->contain(['Users'])->where(['Links.alias' => $alias, 'Links.status <>' => 3])->first();
        if (!$link || $link->ad_type != 1) {
            throw new NotFoundException(__('404 Not Found'));
        }

        $link_code = $this->request->getSession()->read('link_code_' . $alias);
        if (!$link_code && $this->Cookie->check('lc_' . md5($alias))) {
            $cookieVal = $this->Cookie->read('lc_' . md5($alias));
            if (is_string($cookieVal) && strpos($cookieVal, ':') !== false) {
                list($link_code, $expiry) = explode(':', $cookieVal, 2);
                if (time() > (int) $expiry) {
                    $link_code = null;
                }
            }
        }
        if (!$link_code) {
            throw new NotFoundException(__('Session expired. Please go back to the link and try again.'));
        }

        $country = $this->Links->Statistics->get_country(get_ip());
        $traffic_source = get_traffic_source_from_referrer();
        $campaign_item = $this->_getCampaignItem(1, $traffic_source, $country);
        if (!$campaign_item) {
            return $this->redirect($link->url);
        }
        $campaign = $campaign_item->campaign ?? null;
        $wait_seconds = ($campaign && !empty($campaign->countdown_seconds))
            ? (int) $campaign->countdown_seconds
            : (int) get_option('landing_wait_seconds', 60);

        $this->set('link', $link);
        $this->set('link_code', $link_code);
        $this->set('campaign_item', $campaign_item);
        $this->set('wait_seconds', $wait_seconds);
        $this->viewBuilder()->setLayout('landing');
    }

    public function popad()
    {
        $this->autoRender = false;

        if ($this->request->is('post')) {
            try {
                if (empty($this->request->getData('pop_ad'))) {
                    throw new \Exception('Missing pop_ad data');
                }
                $pop_ad = $this->_decrypt($this->request->getData('pop_ad'));
                if (!is_array($pop_ad) || empty($pop_ad['link']) || empty($pop_ad['traffic_source']) || empty($pop_ad['country'])) {
                    throw new \Exception('Invalid pop_ad structure');
                }
            } catch (\Exception $e) {
                \Cake\Log\Log::error('popad decrypt error: ' . $e->getMessage());
                $blockUrl = get_option('proxycheck_block_url', '');
                if (!empty($blockUrl)) {
                    return $this->redirect($blockUrl);
                }
                return $this->redirect('/?error=invalid_request');
            }

            $campaign_item = $this->_getCampaignItem(3, $pop_ad['traffic_source'], $pop_ad['country']);
            if (!$campaign_item) {
                return $this->redirect($pop_ad['link']->url ?? '/');
            }
            $data = [
                'alias' => $pop_ad['link']->alias,
                'ci' => $campaign_item->campaign_id,
                'cui' => $campaign_item->campaign->user_id,
                'cii' => $campaign_item->id,
                'ref' => strtolower(env('HTTP_REFERER'))
            ];
            $content = $this->_calcEarnings($data, $pop_ad['link'], 3);

            return $this->redirect($campaign_item->campaign->website_url);
        }
    }

    protected function _encrypt($value)
    {
        $key = Security::getSalt();
        $value = serialize($value);
        $value = Security::encrypt($value, $key);
        return base64_encode($value);
    }

    protected function _decrypt($value)
    {
        $key = Security::getSalt();
        $value = base64_decode($value);
        $value = Security::decrypt($value, $key);
        return unserialize($value);
    }

    public function go()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('json');

        try {
            if (!$this->request->is('ajax')) {
                $content = [
                    'status' => 'error',
                    'message' => 'Bad Request.',
                    'url' => ''
                ];
                $this->response = $this->response->withStringBody(json_encode($content));
                return $this->response;
            }

            if (empty($this->request->getData('alias'))) {
                $content = [
                    'status' => 'error',
                    'message' => __('Invalid request.'),
                    'url' => ''
                ];
                $this->response = $this->response->withStringBody(json_encode($content));
                return $this->response;
            }

            $link = $this->Links->find()->contain(['Users'])->where([
                'Links.alias' => $this->request->getData('alias'),
                'Links.status <>' => 3
            ])->first();
            if (!$link) {
                $content = [
                    'status' => 'error',
                    'message' => '404 Not Found.',
                    'url' => ''
                ];
                $this->response = $this->response->withStringBody(json_encode($content));
                return $this->response;
            }

            // Verify code for interstitial (Link4M-style flow)
            if ($link->ad_type == 1) {
                $alias = $this->request->getData('alias');
                $sessionCode = $this->request->getSession()->read('link_code_' . $alias);
                $sessionExpiry = $this->request->getSession()->read('link_code_start_' . $alias);
                $cookieKey = 'lc_' . md5($alias);
                if (empty($sessionCode) && $this->Cookie->check($cookieKey)) {
                    $cookieVal = $this->Cookie->read($cookieKey);
                    if (is_string($cookieVal) && strpos($cookieVal, ':') !== false) {
                        list($sessionCode, $sessionExpiry) = explode(':', $cookieVal, 2);
                        $sessionExpiry = (int) $sessionExpiry;
                    }
                }

                // Check session timer
                if ($sessionExpiry && time() > $sessionExpiry) {
                    $this->request->getSession()->delete('link_code_' . $alias);
                    $this->request->getSession()->delete('link_code_start_' . $alias);
                    $this->Cookie->delete($cookieKey);
                    $content = [
                        'status' => 'error',
                        'message' => __('Session expired. Please go back and try again.'),
                        'url' => ''
                    ];
                    $this->response = $this->response->withStringBody(json_encode($content));
                    return $this->response;
                }

                // Verify reCAPTCHA - always required for interstitial
                if (empty($this->request->getData('g-recaptcha-response')) || !$this->Recaptcha->verify($this->request->getData('g-recaptcha-response'))) {
                    $content = [
                        'status' => 'error',
                        'message' => __('Please complete the reCAPTCHA verification.'),
                        'url' => ''
                    ];
                    $this->response = $this->response->withStringBody(json_encode($content));
                    return $this->response;
                }

                $codeFromRequest = $this->request->getData('code');
                $submittedCode = is_string($codeFromRequest) ? strtoupper(trim($codeFromRequest)) : '';
                $sessionCodeNormalized = is_string($sessionCode) ? strtoupper(trim($sessionCode)) : '';
                if (empty($sessionCodeNormalized)) {
                    $content = [
                        'status' => 'error',
                        'message' => __('Session expired. Please go back to the link and try again.'),
                        'url' => ''
                    ];
                    $this->response = $this->response->withStringBody(json_encode($content));
                    return $this->response;
                }
                if ($sessionCodeNormalized !== $submittedCode) {
                    Log::warning('Code mismatch', [
                        'alias' => $alias,
                        'sessionCode' => $sessionCode,
                        'submittedCode' => $submittedCode,
                        'sessionCodeNormalized' => $sessionCodeNormalized,
                        'hasSession' => $this->request->getSession()->check('link_code_' . $alias),
                        'hasCookie' => $this->Cookie->check($cookieKey),
                    ]);
                    $content = [
                        'status' => 'error',
                        'message' => __('Invalid code. Please enter the correct code.'),
                        'url' => ''
                    ];
                    $this->response = $this->response->withStringBody(json_encode($content));
                    return $this->response;
                }
                $this->request->getSession()->delete('link_code_' . $alias);
                $this->request->getSession()->delete('link_code_start_' . $alias);
                $this->Cookie->delete($cookieKey);
            }

            $data = $this->request->getData();

            $content = $this->_calcEarnings($data, $link, $link->ad_type);

            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        } catch (\Exception $e) {
            \Cake\Log\Log::error('go error: ' . $e->getMessage());
            $content = [
                'status' => 'error',
                'message' => __('An error occurred. Please try again.'),
                'url' => ''
            ];
            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        }
    }

    /**
     * Final Ads - Show Smartlink in new tab, then redirect to destination
     */
    public function finalAd()
    {
        $dest = $this->request->getQuery('dest');
        if (empty($dest)) {
            return $this->redirect('/');
        }

        $destUrl = base64_decode($dest, true);
        if ($destUrl === false || !filter_var($destUrl, FILTER_VALIDATE_URL)) {
            return $this->redirect('/');
        }

        $enabled = get_option('final_ad_enabled', 'no');
        $finalAdUrl = get_option('final_ad_url', '');
        $delaySeconds = (int) get_option('final_ad_delay_seconds', 5);

        if ($enabled !== 'yes' || empty($finalAdUrl)) {
            return $this->redirect($destUrl);
        }

        $this->set('dest_url', $destUrl);
        $this->set('final_ad_url', $finalAdUrl);
        $this->set('delay_seconds', max(1, $delaySeconds));
        $this->viewBuilder()->setLayout('go_interstitial');
    }

    protected function _getCampaignItem($ad_type, $traffic_source, $country)
    {
        $CampaignItems = $this->fetchTable('CampaignItems');
        $verified = \App\Service\CampaignVerificationService::STATUS_VERIFIED;
        // Fallback: khi không có campaign verified, dùng unverified/pending (để test)
        $verificationIn = [$verified];
        if (get_option('allow_unverified_campaigns_fallback', 'yes') === 'yes') {
            $verificationIn = [
                $verified,
                \App\Service\CampaignVerificationService::STATUS_UNVERIFIED,
                \App\Service\CampaignVerificationService::STATUS_PENDING,
            ];
        }

        $baseWhere = [
            'Campaigns.ad_type' => $ad_type,
            'Campaigns.status' => 1,
            'Campaigns.verification_status IN' => $verificationIn,
            "Campaigns.traffic_source IN (1, :traffic_source)",
            'CampaignItems.weight <' => 100,
        ];

        $campaign_items = $CampaignItems->find()
            ->contain(['Campaigns'])
            ->where(array_merge($baseWhere, [
                'Campaigns.default_campaign' => 0,
                'CampaignItems.country' => $country,
            ]))
            ->order(['CampaignItems.weight' => 'ASC'])
            ->bind(':traffic_source', $traffic_source, 'integer')
            ->limit(10)
            ->toArray();

        if (count($campaign_items) == 0) {
            $campaign_items = $CampaignItems->find()
                ->contain(['Campaigns'])
                ->where(array_merge($baseWhere, [
                    'Campaigns.default_campaign' => 0,
                    'CampaignItems.country' => 'all',
                ]))
                ->bind(':traffic_source', $traffic_source, 'integer')
                ->limit(10)
                ->toArray();
        }

        if (count($campaign_items) == 0) {
            $campaign_items = $CampaignItems->find()
                ->contain(['Campaigns'])
                ->where(array_merge($baseWhere, [
                    'Campaigns.default_campaign' => 1,
                    "CampaignItems.country IN ( 'all', :country)",
                ]))
                ->order(['CampaignItems.weight' => 'ASC'])
                ->bind(':traffic_source', $traffic_source, 'integer')
                ->bind(':country', $country, 'string')
                ->limit(10)
                ->toArray();
        }

        // Fallback: bỏ qua traffic_source khi không có campaign nào match (campaign mới có thể chưa set đúng)
        if (empty($campaign_items)) {
            $fallbackWhere = [
                'Campaigns.ad_type' => $ad_type,
                'Campaigns.status' => 1,
                'Campaigns.verification_status IN' => $verificationIn,
                'CampaignItems.weight <' => 100,
            ];
            $campaign_items = $CampaignItems->find()
                ->contain(['Campaigns'])
                ->where(array_merge($fallbackWhere, [
                    'Campaigns.default_campaign' => 0,
                    'CampaignItems.country' => $country,
                ]))
                ->limit(10)
                ->toArray();
        }
        if (empty($campaign_items)) {
            $campaign_items = $CampaignItems->find()
                ->contain(['Campaigns'])
                ->where(array_merge($fallbackWhere, [
                    'Campaigns.default_campaign' => 0,
                    'CampaignItems.country' => 'all',
                ]))
                ->limit(10)
                ->toArray();
        }
        if (empty($campaign_items)) {
            $campaign_items = $CampaignItems->find()
                ->contain(['Campaigns'])
                ->where(array_merge($fallbackWhere, [
                    'Campaigns.default_campaign' => 1,
                    "CampaignItems.country IN ( 'all', :country)",
                ]))
                ->order(['CampaignItems.weight' => 'ASC'])
                ->bind(':country', $country, 'string')
                ->limit(10)
                ->toArray();
        }

        if (empty($campaign_items)) {
            return null;
        }
        shuffle($campaign_items);
        return array_values($campaign_items)[0];
    }

    /**
     * Get a keyword task for the given campaign. Prefers campaign-specific, falls back to global.
     */
    protected function _getKeywordTaskForCampaign($campaignId)
    {
        $KeywordTasks = $this->fetchTable('KeywordTasks');
        $conditions = ['KeywordTasks.status' => 1];
        if ($campaignId !== null) {
            $conditions['OR'] = [
                'KeywordTasks.campaign_id' => $campaignId,
                'KeywordTasks.campaign_id IS' => null
            ];
        } else {
            $conditions['KeywordTasks.campaign_id IS'] = null;
        }
        $task = $KeywordTasks->find()
            ->where($conditions)
            ->order(['KeywordTasks.sort_order' => 'ASC', 'KeywordTasks.id' => 'DESC'])
            ->first();
        return $task;
    }

    protected function _getRedirectUrl($link)
    {
        $targetUrl = $link->url;
        if (get_option('final_ad_enabled', 'no') === 'yes' && !empty(get_option('final_ad_url', ''))) {
            $targetUrl = Router::url([
                'controller' => 'Links',
                'action' => 'finalAd',
                '?' => ['dest' => base64_encode($link->url)]
            ], true);
        }

        // Anti-bypass: truyền URL đích qua base64 (không phụ thuộc cache/session)
        if (get_option('anti_bypass_redirect_token', 'yes') === 'yes') {
            $uri = $this->request->getUri();
            $base = trim((string) $this->request->getAttribute('base', ''), '/');
            $dest = base64_encode($targetUrl);
            $path = ($base ? '/' . $base : '') . '/links/r?dest=' . urlencode($dest);
            return $uri->getScheme() . '://' . $uri->getHost() . $path;
        }
        return $targetUrl;
    }

    /**
     * Redirect - nhận URL đích qua dest=base64 (không phụ thuộc cache/session)
     */
    public function r()
    {
        $this->autoRender = false;
        $dest = $this->request->getQuery('dest');
        if (empty($dest) || !is_string($dest)) {
            return $this->redirect('/');
        }
        $url = base64_decode($dest, true);
        if ($url === false || empty($url)) {
            return $this->redirect('/');
        }
        // Chỉ cho phép http(s) hoặc path bắt đầu bằng /
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            $validUrl = filter_var($url, FILTER_VALIDATE_URL) !== false;
        } else {
            $validUrl = (strpos($url, '/') === 0 && strlen($url) > 1);
        }
        if (!$validUrl) {
            return $this->redirect('/');
        }
        return $this->redirect($url);
    }

    protected function _calcEarnings($data, $link, $ad_type)
    {
        /**
         * Views reasons
         * 1- Earn
         * 2- Disabled cookie
         * 3- Anonymous user
         * 4- Adblock
         * 5- Proxy
         * 6- IP changed
         * 7- Not unique
         * 8- Full weight
         * 9- Default campaign
         * 10- Direct
         */
        /**
         * Check if cookie valid
         */
        $cookie = $this->Cookie->read('visitor');
        if (!is_array($cookie)) {
            Log::info('Earn blocked: no cookie', ['alias' => $link->alias ?? null]);
            $this->_updateLinkHits($link);
            $this->_addNormalStatisticEntry($link, $ad_type, $data, get_ip(), 2);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because no cookie',
                'url' => $this->_getRedirectUrl($link)
            ];
            return $content;
        }

        /**
         * Check if anonymous user
         */
        if ('anonymous' == $link->user->username) {
            Log::info('Earn blocked: anonymous user', ['alias' => $link->alias ?? null]);
            $this->_updateLinkHits($link);
            $this->_addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 3);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because anonymous user',
                'url' => $this->_getRedirectUrl($link)
            ];
            return $content;
        }

        /**
         * Check for Adblock
         */
        if (!empty($this->request->getCookie('adblockUser'))) {
            Log::info('Earn blocked: Adblock', ['alias' => $link->alias ?? null]);
            $this->_updateLinkHits($link);
            $this->_addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 4);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because Adblock',
                'url' => $this->_getRedirectUrl($link)
            ];
            return $content;
        }

        /**
         * Check if proxy
         */
        /*
          if (!isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && $this->_isProxy()) {
          // Update link hits
          $this->_updateLinkHits($link);
          $this->_addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 5);
          $content = [
          'status' => 'success',
          'message' => 'Go without Earn because proxy',
          'url' => $this->_getRedirectUrl($link)
          ];
          return $content;
          }
         */

        /**
         * Check if IP changed
         */
        if ($cookie['ip'] != get_ip()) {
            Log::info('Earn blocked: IP changed', ['alias' => $link->alias ?? null, 'cookie_ip' => $cookie['ip'] ?? '', 'current_ip' => get_ip()]);
            $this->_updateLinkHits($link);
            $this->_addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 6);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because IP changed',
                'url' => $this->_getRedirectUrl($link)
            ];
            return $content;
        }

        /**
         * Rate limit: 1 IP/Link/Day = Max N views with earnings. Reset at midnight (timezone).
         */
        $timezone = get_option('timezone', 'UTC');
        try {
            $now = new Time('now', $timezone);
        } catch (\Exception $e) {
            $now = new Time('now', 'UTC');
        }
        $startOfToday = $now->copy()->startOfDay()->format('Y-m-d H:i:s');
        $endOfToday = $now->copy()->endOfDay()->format('Y-m-d H:i:s');

        $statistics = $this->Links->Statistics->find()
            ->where([
                'Statistics.ip' => $cookie['ip'],
                'Statistics.link_id' => $link->id,
                'Statistics.publisher_earn >' => 0,
                'Statistics.created BETWEEN :startOfToday AND :endOfToday'
            ])
            ->bind(':startOfToday', $startOfToday, 'datetime')
            ->bind(':endOfToday', $endOfToday, 'datetime')
            ->count();

        $maxViewsPerLinkDay = (int) get_option('rate_limit_views_per_link_day', 2);
        if ($statistics >= $maxViewsPerLinkDay) {
            Log::info('Earn blocked: rate limit', ['alias' => $link->alias ?? null, 'count' => $statistics, 'max' => $maxViewsPerLinkDay]);
            $this->_updateLinkHits($link);
            $this->_addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 7);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because Not unique.',
                'url' => $this->_getRedirectUrl($link)
            ];
            return $content;
        }

        /**
         * ProxyCheck: block VPN/Proxy before recording earnings
         */
        if (!ProxyCheckService::checkIp($cookie['ip'])) {
            Log::info('Earn blocked: VPN/Proxy', ['alias' => $link->alias ?? null]);
            $this->_updateLinkHits($link);
            $this->_addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 5);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because VPN/Proxy.',
                'url' => $this->_getRedirectUrl($link)
            ];
            return $content;
        }

        /**
         * Check Campaign Item weight
         */
        $CampaignItems = $this->fetchTable('CampaignItems');

        $cii = $data['cii'] ?? null;
        $campaign_item = null;
        if (!empty($cii)) {
            $campaign_item = $CampaignItems->find()
                ->contain(['Campaigns'])
                ->where(['CampaignItems.id' => $cii])
                ->where(['CampaignItems.weight <' => 100])
                ->where(['Campaigns.status' => 1])
                ->first();
        }

        if (!$campaign_item) {
            Log::info('Earn blocked: campaign item not found or full', ['alias' => $link->alias ?? null, 'cii' => $cii, 'data_keys' => array_keys($data ?? [])]);
            $this->_updateLinkHits($link);
            $this->_addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 8);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because Campaign Item weight is full.',
                'url' => $this->_getRedirectUrl($link)
            ];
            return $content;
        }

        /**
         * Add statistic record (tất cả campaign đều trả tiền, kể cả default)
         */
        Log::info('Earn success', ['alias' => $link->alias ?? null, 'user_id' => $link->user_id, 'publisher_price' => $campaign_item->publisher_price ?? 0]);

        $user_update = $this->Links->Users->get($link->user_id);
        $user_update->publisher_earnings += $campaign_item->publisher_price / 1000;

        $this->Links->Users->save($user_update);

        $referral_id = $referral_earn = 0;

        if (!empty($user_update->referred_by)) {
            $referral_percentage = get_option('referral_percentage', 20) / 100;
            $referral_value = ($campaign_item->publisher_price / 1000) * $referral_percentage;

            $user_referred_by = $this->Links->Users->get($user_update->referred_by);
            $user_referred_by->referral_earnings += $referral_value;

            $this->Links->Users->save($user_referred_by);

            $referral_id = $user_update->referred_by;
            $referral_earn = $referral_value;
        }


        $country = $this->Links->Statistics->get_country($cookie['ip']);

        $statistic = $this->Links->Statistics->newEntity([]);

        $statistic->link_id = $link->id;
        $statistic->user_id = $link->user_id;
        $statistic->ad_type = $campaign_item->campaign->ad_type;
        $statistic->campaign_id = $campaign_item->campaign->id;
        $statistic->campaign_user_id = $campaign_item->campaign->user_id;
        $statistic->campaign_item_id = $campaign_item->id;
        $statistic->ip = $cookie['ip'];
        $statistic->country = $country;
        $statistic->owner_earn = ($campaign_item->advertiser_price - $campaign_item->publisher_price) / 1000;
        $statistic->publisher_earn = $campaign_item->publisher_price / 1000;
        $statistic->referral_id = $referral_id;
        $statistic->referral_earn = $referral_earn;
        $statistic->referer_domain = (parse_url($data['ref'], PHP_URL_HOST) ? parse_url($data['ref'], PHP_URL_HOST) : 'Direct');
        $statistic->referer = $data['ref'];
        $statistic->user_agent = env('HTTP_USER_AGENT');
        $statistic->reason = 1;
        $this->Links->Statistics->save($statistic);

        /**
         * Update campaign item views and weight
         * Interstitial (country=all): purchase = tổng view → weight = views/purchase*100
         * Banner/Popup (theo quốc gia): purchase = số đơn vị 1000 → weight = views/(purchase*1000)*100
         */
        $itemCountry = $campaign_item->country ?? '';
        $purchase = (int) ($campaign_item->purchase ?? 1);
        $totalSlots = ('all' === $itemCountry) ? $purchase : ($purchase * 1000);
        $campaign_item_update = $CampaignItems->newEntity([]);
        $campaign_item_update->id = $campaign_item->id;
        $campaign_item_update->views = $campaign_item->views + 1;
        $campaign_item_update->weight = (($campaign_item->views + 1) / $totalSlots) * 100;
        $CampaignItems->save($campaign_item_update);

        // Update link hits
        $this->_updateLinkHits($link);
        $content = [
            'status' => 'success',
            'message' => 'Go With earning :)',
            'url' => $this->_getRedirectUrl($link)
        ];
        return $content;
    }

    protected function _addNormalStatisticEntry($link, $ad_type, $data, $ip, $reason = 0)
    {
        if (!$ip) {
            $ip = get_ip();
        }
        $country = $this->Links->Statistics->get_country($ip);

        $statistic = $this->Links->Statistics->newEntity([]);

        $statistic->link_id = $link->id;
        $statistic->user_id = $link->user_id;
        $statistic->ad_type = $ad_type;
        $statistic->campaign_id = $data['ci'];
        $statistic->campaign_user_id = $data['cui'];
        $statistic->campaign_item_id = $data['cii'];
        $statistic->ip = $ip;
        $statistic->country = $country;
        $statistic->owner_earn = 0;
        $statistic->publisher_earn = 0;
        $statistic->referer_domain = (parse_url($data['ref'], PHP_URL_HOST) ? parse_url($data['ref'], PHP_URL_HOST) : 'Direct');
        $statistic->referer = $data['ref'];
        $statistic->user_agent = env('HTTP_USER_AGENT');
        $statistic->reason = $reason;
        $this->Links->Statistics->save($statistic);
    }

    protected function _setVisitorCookie()
    {
        $cookie = $this->Cookie->read('visitor');

        if (isset($cookie)) {
            return true;
        }

        $cookie_data = [
            'ip' => get_ip(),
            'date' => (new Time())->toDateTimeString()
        ];
        $this->Cookie->configKey('visitor', [
            'expires' => '+1 day',
            'httpOnly' => true
        ]);
        $this->Cookie->write('visitor', $cookie_data);

        return true;
    }

    protected function _updateLinkHits($link = null)
    {
        if (!$link) {
            return;
        }
        $link->hits += 1;
        $link->setDirty('modified', false); // Không tự động cập nhật timestamp khi chỉ tăng hits
        $this->Links->save($link);
        return;
    }

    protected function _isProxy()
    {
        $proxy_headers = [
            'HTTP_VIA',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED_FOR_IP',
            'VIA',
            'X_FORWARDED_FOR',
            'FORWARDED_FOR',
            'X_FORWARDED',
            'FORWARDED',
            'CLIENT_IP',
            'FORWARDED_FOR_IP',
            'HTTP_PROXY_CONNECTION'
        ];
        foreach ($proxy_headers as $proxy_header) {
            if (isset($_SERVER[$proxy_header])) {
                return true;
            }
        }
        return false;
    }
}
