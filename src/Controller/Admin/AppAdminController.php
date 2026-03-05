<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

class AppAdminController extends AppController
{

    public $paginate = [
        'limit' => 10,
        'order' => ['id' => 'DESC']
    ];

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        if ($this->Auth->user('role') === 'admin') {
            // Allow all actions
            $this->Auth->allow();
        }
        $this->viewBuilder()->setLayout('admin');
        
        if($this->redirect_for_database_upgrade()) {
            return $this->redirect(['controller' => 'Upgrade', 'action' => 'index'], 307);
        }
        
        if($this->redirect_for_license_activate()) {
            return $this->redirect(['controller' => 'Activation', 'action' => 'index'], 307);
        }
        
        $this->checkDefaultCampaigns();
        
    }

    public function isAuthorized($user = null)
    {
        // Admin can access every action
        if ($user['role'] === 'admin') {
            return true;
        }
        // Default deny
        return false;
    }
    
    protected function redirect_for_database_upgrade()
    {
        if(require_database_upgrade() && $this->request->getParam('controller') !== 'Upgrade' ) {
            return true;
        }
        return false;
        
    }
    
    protected function redirect_for_license_activate()
    {
        if(require_database_upgrade()) {
            return false;
        }
        
        $Activation = $this->fetchTable('Activation');
        if( $Activation->checkLicense() === false && $this->request->getParam('controller') !== 'Activation' ) {
            return true;
        }
        return false;
        
    }
    
    protected function checkDefaultCampaigns()
    {
        if (require_database_upgrade()) {
            return true;
        }

        $Campaigns = $this->fetchTable('Campaigns');

        // Only require default interstitial if there are any interstitial campaigns
        $hasInterstitial = $Campaigns->find()->where(['ad_type' => 1])->count() > 0;
        if ($hasInterstitial) {
            $defaultInterstitial = $Campaigns->find()
                ->where(['default_campaign' => 1, 'ad_type' => 1, 'status' => 1])
                ->count();
            if ($defaultInterstitial == 0) {
                $this->Flash->error(__('You must have at least one interstitial campaign marked as default.'));
            }
        }

        // Only require default banner if there are any banner campaigns
        $hasBanner = $Campaigns->find()->where(['ad_type' => 2])->count() > 0;
        if ($hasBanner) {
            $defaultBanner = $Campaigns->find()
                ->where(['default_campaign' => 1, 'ad_type' => 2, 'status' => 1])
                ->count();
            if ($defaultBanner == 0) {
                $this->Flash->error(__('You must have at least one banner campaign marked as default.'));
            }
        }
    }
    
}
