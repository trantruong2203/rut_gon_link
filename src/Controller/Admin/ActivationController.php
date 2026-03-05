<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use Cake\Cache\Cache;

class ActivationController extends AppAdminController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->viewBuilder()->setLayout('admin');
        
    }
    
    public function index()
    {
        if ($this->request->is('post')) {
            
            $result = $this->Activation->licenseCurlRequest($this->request->getData());
            
            if( isset($result['item']['id']) && $result['item']['id'] == 16887109 ) {
                Cache::write('license_response_result', $result, '1week');
                
                $Options = $this->fetchTable('Options');
                
                $personal_token = $Options->find()->where(['name' => 'personal_token'])->first();
                $personal_token->value = trim($this->request->getData('personal_token'));
                $Options->save($personal_token);
                
                $purchase_code = $Options->find()->where(['name' => 'purchase_code'])->first();
                $purchase_code->value = trim($this->request->getData('purchase_code'));
                $Options->save($purchase_code);
                
                $this->Flash->success(__('Your license has been verified.'));
                return $this->redirect(['controller' => 'Users', 'action' => 'dashboard', 'prefix' => 'Admin']);
            } else {
                if( isset($result['description']) && !empty($result['description']) ) {
                    $this->Flash->error( $result['description'] );
                } elseif( isset($result['error_description']) && !empty($result['error_description']) ) {
                    $this->Flash->error( $result['error_description'] );
                } else {
                    $this->Flash->error( $result['error'] );
                }
                
            }
            
        }
    }
    
}
