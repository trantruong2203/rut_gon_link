<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use App\Service\CampaignVerificationService;
use Cake\Http\Exception\NotFoundException;

class CampaignsController extends AppAdminController
{

    public function initialize(): void
    {
        parent::initialize();
        $this->FormProtection->setConfig('unlockedActions', ['edit', 'createInterstitial'], true);
        // Chạy trước FormProtection để tắt validate cho edit/createInterstitial (campaign_items tính từ form)
        $this->getEventManager()->on('Controller.startup', ['priority' => 1], function () {
            $action = $this->request->getParam('action');
            if (in_array($action, ['edit', 'createInterstitial'], true)) {
                $this->FormProtection->setConfig('validate', false);
            }
        });
    }

    public function index()
    {
        $conditions = [];
        
        $filter_fields = ['id', 'user_id', 'status', 'ad_type', 'name', 'other_fields', 'verification_status'];
        
        //Transform POST into GET
        if ($this->request->is(['post', 'put']) && $this->request->getData('Filter') !== null) {
            
            $filter_url = [];
            
            $filter_url['controller'] = $this->request->getParam('controller');
            
            $filter_url['action'] = $this->request->getParam('action');
            
            // We need to overwrite the page every time we change the parameters
            $filter_url['page'] = 1;

            // for each filter we will add a GET parameter for the generated url
            foreach ($this->request->getData('Filter') as $name => $value) {
                if (in_array($name, $filter_fields) && $value) {
                    // You might want to sanitize the $value here
                    // or even do a urlencode to be sure
                    $filter_url[$name] = urlencode($value);
                }
            }
            // now that we have generated an url with GET parameters,
            // we'll redirect to that page
            return $this->redirect($filter_url);
        } else {
            // Inspect all the named parameters to apply the filters
            $filterData = $this->request->getData('Filter') ?? [];
            foreach ($this->request->getQueryParams() as $param_name => $value) {
                if (in_array($param_name, $filter_fields)) {

                    if (in_array($param_name, ['name'])) {
                        $conditions[] = [
                            ['Campaigns.' . $param_name . ' LIKE' => '%' . $value . '%']
                        ];
                    } elseif (in_array($param_name, ['other_fields'])) {
                        $conditions['OR'] = [
                            ['Campaigns.website_title LIKE' => '%' . $value . '%'],
                            ['Campaigns.website_url LIKE' => '%' . $value . '%'],
                            ['Campaigns.banner_name LIKE' => '%' . $value . '%'],
                            ['Campaigns.banner_size LIKE' => '%' . $value . '%']
                        ];
                    } elseif (in_array($param_name, ['id', 'user_id', 'status', 'ad_type', 'verification_status']) ) {
                        if( $param_name == 'status' && !in_array($value, [1, 2, 3, 4, 5, 6, 7, 8]) ) {
                            continue;
                        }
                        if( $param_name == 'ad_type' && !in_array($value, [1, 2, 3]) ) {
                            continue;
                        }
                        if( $param_name == 'verification_status' && !in_array((int)$value, [0, 1, 2, 3], true) ) {
                            continue;
                        }
                        $conditions['Campaigns.' . $param_name] = $value;
                    }
                    $filterData[$param_name] = $value;
                }
            }
            $this->request = $this->request->withData('Filter', $filterData);
        }
        
        $query = $this->Campaigns->find()
            ->contain(['Users', 'CampaignItems'])
            ->where($conditions);
        $campaigns = $this->paginate($query);

        $this->set('campaigns', $campaigns);
    }

    public function view($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }

        $campaign = $this->Campaigns->findById($id)
            ->contain(['CampaignItems'])
            ->first();

        if (!$campaign) {
            throw new NotFoundException(__('Campaign Not Found'));
        }

        $this->set('campaign', $campaign);

        $this->loadModel('Statistics');

        $campaign_earnings = $this->Statistics->find()
            ->select([
                'reason',
                'count' => 'COUNT(reason)',
                'earnings' => 'SUM(publisher_earn)',
            ])
            ->where([
                'campaign_id' => $campaign->id
            ])
            ->order(['count' => 'DESC'])
            ->group(['reason'])
            ->toArray();

        $this->set('campaign_earnings', $campaign_earnings);

        $campaign_countries = $this->Statistics->find()
            ->select([
                'country',
                'count' => 'COUNT(country)',
                'earnings' => 'SUM(publisher_earn)',
            ])
            ->where([
                'campaign_id' => $campaign->id
            ])
            ->order(['count' => 'DESC'])
            ->group(['country'])
            ->toArray();

        $this->set('campaign_countries', $campaign_countries);

        $campaign_referers = $this->Statistics->find()
            ->select([
                'referer_domain',
                'count' => 'COUNT(referer_domain)',
                'earnings' => 'SUM(publisher_earn)',
            ])
            ->where([
                'campaign_id' => $campaign->id
            ])
            ->order(['count' => 'DESC'])
            ->group(['referer_domain'])
            ->toArray();

        $this->set('campaign_referers', $campaign_referers);

        /*
        $campaign_statistics = $this->Statistics->find()
            ->select([
                'reason',
                'reason_count' => 'COUNT(reason)',
                'earnings' => 'SUM(publisher_earn)',
            ])
            ->where([
                'campaign_id' => $campaign->id
            ])
            ->group(['reason'])
            ->toArray();

        $this->set('campaign_statistics', $campaign_statistics);
        */
    }

    public function createInterstitial()
    {
        $campaign = $this->Campaigns->newEntity([], ['associated' => ['CampaignItems']]);
        $this->set('campaign', $campaign);

        $users = $this->Campaigns->Users->find('list', [
            'keyField' => 'id',
            'valueField' => 'username'
        ]);
        $this->set('users', $users);

        if ($this->request->is('post')) {
            $campaign->ad_type = 1;

            $data = $this->request->getData();
            $data['price'] = 0;
            $data['verification_token'] = $data['verification_token'] ?? CampaignVerificationService::generateToken();
            $data['verification_status'] = $data['verification_status'] ?? CampaignVerificationService::STATUS_UNVERIFIED;

            // Handle image uploads
            $uploadDir = WWW_ROOT . 'uploads' . DS . 'campaigns' . DS;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $img1 = $this->request->getUploadedFile('Campaign.image_1_file') ?: $this->request->getUploadedFile('image_1_file');
            if ($img1 && $img1->getError() === UPLOAD_ERR_OK) {
                $clientName = $img1->getClientFilename();
                $ext = $clientName ? pathinfo($clientName, PATHINFO_EXTENSION) : 'jpg';
                $ext = $ext ?: 'jpg';
                $filename = 'img1_' . time() . '_' . uniqid() . '.' . $ext;
                $img1->moveTo($uploadDir . $filename);
                $data['image_1'] = 'uploads/campaigns/' . $filename;
            }
            unset($data['image_1_file']);
            $img2 = $this->request->getUploadedFile('Campaign.image_2_file') ?: $this->request->getUploadedFile('image_2_file');
            if ($img2 && $img2->getError() === UPLOAD_ERR_OK) {
                $clientName = $img2->getClientFilename();
                $ext = $clientName ? pathinfo($clientName, PATHINFO_EXTENSION) : 'jpg';
                $ext = $ext ?: 'jpg';
                $filename = 'img2_' . time() . '_' . uniqid() . '.' . $ext;
                $img2->moveTo($uploadDir . $filename);
                $data['image_2'] = 'uploads/campaigns/' . $filename;
            }
            unset($data['image_2_file']);

            // Interstitial: 1 campaign_item (country=all), tổng tiền = giá/1000 × tổng view
            $totalView = (int) ($data['total_view_limit'] ?? 0);
            $countdown = (int) ($data['countdown_seconds'] ?? 60);
            $version = (int) ($data['campaign_version'] ?? 1);
            $calc = calc_interstitial_total_price($totalView, $countdown, $version);
            $data['campaign_items'] = [
                [
                    'country' => 'all',
                    'purchase' => $calc['purchase'],
                    'advertiser_price' => $calc['advertiser_price'],
                    'publisher_price' => $calc['publisher_price'],
                ],
            ];
            $data['price'] = $calc['total'];

            if ($totalView < 1000) {
                $this->Flash->error(__('Tổng view tối thiểu 1000.'));
            } else {
                $campaign = $this->Campaigns->patchEntity($campaign, $data);

                if ($this->Campaigns->save($campaign)) {
                    $this->Flash->success(__('Your campaign has been created.'));
                    return $this->redirect(['action' => 'index']);
                } else {
                    $this->Flash->error(__('Unable to create your campaign.'));
                }
            }
        }
        $this->set('campaign', $campaign);
    }
    
    public function createBanner()
    {
        $campaign = $this->Campaigns->newEntity([], ['associated' => ['CampaignItems']]);
        $this->set('campaign', $campaign);
        
        $users = $this->Campaigns->Users->find('list', [
            'keyField' => 'id',
            'valueField' => 'username'
        ]);
        $this->set('users', $users);
        
        if ($this->request->is('post')) {
            $campaign->ad_type = 2;

            $data = $this->request->getData();
            $data['price'] = 0;
            $data['verification_token'] = $data['verification_token'] ?? CampaignVerificationService::generateToken();
            $data['verification_status'] = $data['verification_status'] ?? CampaignVerificationService::STATUS_UNVERIFIED;

            foreach (($data['campaign_items'] ?? []) as $key => $value) {
                if (empty($value['purchase'])) {
                    unset($data['campaign_items'][$key]);
                    continue;
                }
                $data['price'] += $value['purchase'] * $value['advertiser_price'];
            }
            
            if(count($data['campaign_items'] ?? []) == 0){
                return $this->Flash->error(__('You must purchase at least from one country.'));
            }

            $campaign = $this->Campaigns->patchEntity($campaign, $data);

            if ($this->Campaigns->save($campaign)) {
                $this->Flash->success(__('Your campaign has been created.'));
                return $this->redirect( ['action' => 'index' ] );
            } else {
                $this->Flash->error(__('Unable to create your campaign.'));
            }
        }
        $this->set('campaign', $campaign);
    }
    
    public function createPopup()
    {
        $campaign = $this->Campaigns->newEntity([], ['associated' => ['CampaignItems']]);
        $this->set('campaign', $campaign);
        
        $users = $this->Campaigns->Users->find('list', [
            'keyField' => 'id',
            'valueField' => 'username'
        ]);
        $this->set('users', $users);
        
        if ($this->request->is('post')) {
            $campaign->ad_type = 3;

            $data = $this->request->getData();
            $data['price'] = 0;
            $data['verification_token'] = $data['verification_token'] ?? CampaignVerificationService::generateToken();
            $data['verification_status'] = $data['verification_status'] ?? CampaignVerificationService::STATUS_UNVERIFIED;

            foreach (($data['campaign_items'] ?? []) as $key => $value) {
                if (empty($value['purchase'])) {
                    unset($data['campaign_items'][$key]);
                    continue;
                }
                $data['price'] += $value['purchase'] * $value['advertiser_price'];
            }
            
            if(count($data['campaign_items'] ?? []) == 0){
                return $this->Flash->error(__('You must purchase at least from one country.'));
            }

            $campaign = $this->Campaigns->patchEntity($campaign, $data);

            if ($this->Campaigns->save($campaign)) {
                $this->Flash->success(__('Your campaign has been created.'));
                return $this->redirect( ['action' => 'index' ] );
            } else {
                $this->Flash->error(__('Unable to create your campaign.'));
            }
        }
        $this->set('campaign', $campaign);
    }
    
    public function edit($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }

        $campaign = $this->Campaigns->find()
            ->where(['Campaigns.id' => $id])
            ->contain(['CampaignItems'])
            ->first();

        if (!$campaign) {
            throw new NotFoundException(__('Invalid campaign'));
        }
        
        $users = $this->Campaigns->Users->find('list', [
            'keyField' => 'id',
            'valueField' => 'username'
        ]);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            $newStatus = (int)($data['status'] ?? $campaign->status);

            // Nếu cố kích hoạt (Active) nhưng chưa verify → giữ status cũ, vẫn cho lưu các trường khác
            if ($newStatus === 1 && (int)$campaign->verification_status !== CampaignVerificationService::STATUS_VERIFIED) {
                $this->Flash->warning(__('Campaign must be verified before it can be activated. Status unchanged.'));
                $data['status'] = $campaign->status;
            }

            $this->Campaigns->patchEntity($campaign, $data, ['associated' => ['CampaignItems']]);
            if ($this->Campaigns->save($campaign)) {
                $this->Flash->success(__('Campaign has been updated.'));
                return $this->redirect(['action' => 'edit', $id]);
            } else {
                $errors = $campaign->getErrors();
                $msg = __('Unable to update campaign.');
                if (!empty($errors)) {
                    $flat = [];
                    array_walk_recursive($errors, function ($v) use (&$flat) {
                        $flat[] = $v;
                    });
                    $msg .= ' ' . implode(' ', $flat);
                }
                $this->Flash->error($msg);
            }
        }

        $this->set('campaign', $campaign);
        $this->set('users', $users);
    }

    public function checkVerification($id = null)
    {
        $this->request->allowMethod(['post', 'put']);

        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }

        $campaign = $this->Campaigns->findById($id)->first();
        if (!$campaign) {
            throw new NotFoundException(__('Campaign Not Found'));
        }

        if (empty($campaign->verification_token)) {
            $campaign->verification_token = CampaignVerificationService::generateToken();
        }

        $campaign->verification_status = CampaignVerificationService::STATUS_PENDING;
        $this->Campaigns->save($campaign);

        $result = CampaignVerificationService::verifyAndApply($campaign, 'admin_manual');
        if ($this->Campaigns->save($campaign)) {
            if ($result['verified']) {
                $this->Flash->success(__('Website verification succeeded.'));
            } else {
                $this->Flash->error(__('Website verification failed: {0}', $result['note']));
            }
        } else {
            $this->Flash->error(__('Unable to update verification status.'));
        }

        return $this->redirect(['action' => 'edit', $id]);
    }

    public function pause($id)
    {

        $this->request->allowMethod(['post', 'put']);

        $campaign = $this->Campaigns->findById($id)
            ->where(['status' => 1])
            ->first();

        if (!$campaign) {
            $this->Flash->success(__('Campaign not found'));
            return $this->redirect(['action' => 'index']);
        }

        $campaign->status = 2;
        $this->Campaigns->save($campaign);

        return $this->redirect(['action' => 'index']);
    }

    public function resume($id)
    {

        $this->request->allowMethod(['post', 'put']);

        $campaign = $this->Campaigns->findById($id)
            ->where(['status' => 2])
            ->first();

        if (!$campaign) {
            $this->Flash->success(__('Campaign not found'));
            return $this->redirect(['action' => 'index']);
        }

        if ((int)$campaign->verification_status !== CampaignVerificationService::STATUS_VERIFIED) {
            $this->Flash->error(__('Campaign must be verified before it can be resumed.'));
            return $this->redirect(['action' => 'view', $id]);
        }

        $campaign->status = 1;
        $this->Campaigns->save($campaign);

        return $this->redirect(['action' => 'index']);
    }
}
