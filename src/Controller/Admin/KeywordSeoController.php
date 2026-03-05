<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use Cake\Http\Exception\NotFoundException;

class KeywordSeoController extends AppAdminController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->FormProtection->setConfig('unlockedActions', ['add', 'edit'], true);
    }

    public function index()
    {
        $conditions = [];
        
        $filter_fields = ['id', 'keyword_seo_status', 'name'];
        
        if ($this->request->is(['post', 'put']) && $this->request->getData('Filter') !== null) {
            $filter_url = [];
            $filter_url['controller'] = $this->request->getParam('controller');
            $filter_url['action'] = $this->request->getParam('action');
            $filter_url['page'] = 1;
            
            foreach ($this->request->getData('Filter') as $name => $value) {
                if (in_array($name, $filter_fields) && $value) {
                    $filter_url[$name] = urlencode($value);
                }
            }
            return $this->redirect($filter_url);
        } else {
            $filterData = $this->request->getData('Filter') ?? [];
            foreach ($this->request->getQueryParams() as $param_name => $value) {
                if (in_array($param_name, $filter_fields)) {
                    if (in_array($param_name, ['name'])) {
                        $conditions[] = [
                            ['Campaigns.name LIKE' => '%' . $value . '%']
                        ];
                    } elseif (in_array($param_name, ['id', 'keyword_seo_status'])) {
                        $conditions['Campaigns.' . $param_name] = $value;
                    }
                    $filterData[$param_name] = $value;
                }
            }
            $this->request = $this->request->withData('Filter', $filterData);
        }
        
        // Chỉ lấy các chiến dịch có keyword_seo_code (là chiến dịch SEO)
        $conditions['Campaigns.keyword_seo_code IS NOT'] = null;
        
        $query = $this->Campaigns->find()
            ->contain(['Users'])
            ->where($conditions)
            ->order(['Campaigns.created' => 'DESC']);
        
        $campaigns = $this->paginate($query);
        
        $this->set('campaigns', $campaigns);
    }

    public function add()
    {
        $campaign = $this->Campaigns->newEntity([]);
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Generate unique 6-digit code
            $data['keyword_seo_code'] = $this->generateSeoCode();
            $data['keyword_seo_status'] = 'pending';
            $data['seo_current_views'] = 0;
            $data['status'] = 0; // Disabled by default
            
            // Set default values
            if (empty($data['seo_target_views'])) {
                $data['seo_target_views'] = 1000;
            }
            if (empty($data['seo_wait_seconds'])) {
                $data['seo_wait_seconds'] = 60;
            }
            if (empty($data['seo_price_usd'])) {
                $data['seo_price_usd'] = 80;
            }
            
            // Admin created - no user association
            $data['user_id'] = 1;
            $data['ad_type'] = 1;
            $data['payment_method'] = 'admin';
            $data['traffic_source'] = 4;
            $data['price'] = $data['seo_price_usd'];
            
            $campaign = $this->Campaigns->patchEntity($campaign, $data);
            
            if ($this->Campaigns->save($campaign)) {
                $this->Flash->success(__('SEO Campaign has been created. Code: ') . $campaign->keyword_seo_code);
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
        }
        
        $this->set('campaign', $campaign);
    }

    public function edit($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }
        
        $campaign = $this->Campaigns->findById($id)->first();
        if (!$campaign || empty($campaign->keyword_seo_code)) {
            throw new NotFoundException(__('Invalid SEO campaign'));
        }
        
        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            
            // Don't allow changing the code after creation
            unset($data['keyword_seo_code']);
            
            $campaign = $this->Campaigns->patchEntity($campaign, $data);
            
            if ($this->Campaigns->save($campaign)) {
                $this->Flash->success(__('SEO Campaign has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
        }
        
        $this->set('campaign', $campaign);
    }

    public function view($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }
        
        $campaign = $this->Campaigns->findById($id)
            ->contain(['Users'])
            ->first();
        
        if (!$campaign || empty($campaign->keyword_seo_code)) {
            throw new NotFoundException(__('Invalid SEO campaign'));
        }
        
        // Calculate statistics
        $progress = 0;
        if ($campaign->seo_target_views > 0) {
            $progress = round(($campaign->seo_current_views / $campaign->seo_target_views) * 100, 2);
        }
        
        $this->set(compact('campaign', 'progress'));
    }

    public function delete($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }
        
        $campaign = $this->Campaigns->findById($id)->first();
        if (!$campaign || empty($campaign->keyword_seo_code)) {
            throw new NotFoundException(__('Invalid SEO campaign'));
        }
        
        if ($this->Campaigns->delete($campaign)) {
            $this->Flash->success(__('SEO Campaign has been deleted.'));
        } else {
            $this->Flash->error(__('Could not delete campaign.'));
        }
        
        return $this->redirect(['action' => 'index']);
    }

    public function start($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }
        
        $campaign = $this->Campaigns->findById($id)->first();
        if (!$campaign || empty($campaign->keyword_seo_code)) {
            throw new NotFoundException(__('Invalid SEO campaign'));
        }
        
        // Update status to running
        $campaign->keyword_seo_status = 'running';
        $campaign->status = 1; // Active
        
        if ($this->Campaigns->save($campaign)) {
            $this->Flash->success(__('SEO Campaign is now running.'));
        } else {
            $this->Flash->error(__('Could not start campaign.'));
        }
        
        return $this->redirect(['action' => 'view', $id]);
    }

    public function stop($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }
        
        $campaign = $this->Campaigns->findById($id)->first();
        if (!$campaign || empty($campaign->keyword_seo_code)) {
            throw new NotFoundException(__('Invalid SEO campaign'));
        }
        
        // Update status to stopped
        $campaign->keyword_seo_status = 'stopped';
        $campaign->status = 0; // Inactive
        
        if ($this->Campaigns->save($campaign)) {
            $this->Flash->success(__('SEO Campaign has been stopped.'));
        } else {
            $this->Flash->error(__('Could not stop campaign.'));
        }
        
        return $this->redirect(['action' => 'view', $id]);
    }

    public function reset($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }
        
        $campaign = $this->Campaigns->findById($id)->first();
        if (!$campaign || empty($campaign->keyword_seo_code)) {
            throw new NotFoundException(__('Invalid SEO campaign'));
        }
        
        // Reset views
        $campaign->seo_current_views = 0;
        $campaign->keyword_seo_status = 'pending';
        
        if ($this->Campaigns->save($campaign)) {
            $this->Flash->success(__('SEO Campaign views have been reset.'));
        } else {
            $this->Flash->error(__('Could not reset campaign.'));
        }
        
        return $this->redirect(['action' => 'view', $id]);
    }

    private function generateSeoCode()
    {
        // Generate unique 6-digit code
        do {
            $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $exists = $this->Campaigns->find()
                ->where(['keyword_seo_code' => $code])
                ->exists();
        } while ($exists);
        
        return $code;
    }
}
