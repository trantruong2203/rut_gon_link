<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use Cake\Http\Exception\NotFoundException;

class OptionsController extends AppAdminController
{

    public function initialize(): void
    {
        parent::initialize();
        // Unlock action index để cho phép lưu settings (fix CSRF issue trên VPS)
        $this->FormProtection->setConfig('unlockedActions', ['index', 'email', 'socialLogin'], true);
    }

    public function index()
    {
        $options = $this->Options->find()->all();

        $settings = [];
        foreach ($options as $option) {
            $settings[$option->name] = [
                'id' => $option->id,
                'value' => $option->value
            ];
        }

        if ($this->request->is(['post', 'put'])) {
            foreach ($this->request->getData('Options') ?? [] as $key => $optionData) {
                if(is_array( $optionData['value']) ) {
                    $optionData['value'] = serialize( $optionData['value'] );
                }
                $option = $this->Options->newEntity([]);
                $option->id = $key;
                $option = $this->Options->patchEntity($option, $optionData);
                $this->Options->save($option);
            }
            
            emptyTmp();
            
            $this->Flash->success('Settings have been saved.');
            return $this->redirect(['action' => 'index']);
        }

        $this->set('options', $options);
        $this->set('settings', $settings);
    }
    
    public function email()
    {
        $options = $this->Options->find()->all();

        $settings = [];
        foreach ($options as $option) {
            $settings[$option->name] = [
                'id' => $option->id,
                'value' => $option->value
            ];
        }

        if ($this->request->is(['post', 'put'])) {
            foreach ($this->request->getData('Options') ?? [] as $key => $optionData) {
                if(is_array( $optionData['value']) ) {
                    $optionData['value'] = serialize( $optionData['value'] );
                }
                $option = $this->Options->newEntity([]);
                $option->id = $key;
                $option = $this->Options->patchEntity($option, $optionData);
                $this->Options->save($option);
            }
            
            $this->createEmailFile();
            
            $this->Flash->success('Email settings have been saved.');
            return $this->redirect(['action' => 'email']);
        }

        $this->set('options', $options);
        $this->set('settings', $settings);
    }
    
    public function socialLogin()
    {
        $options = $this->Options->find()->all();

        $settings = [];
        foreach ($options as $option) {
            $settings[$option->name] = [
                'id' => $option->id,
                'value' => $option->value
            ];
        }

        if ($this->request->is(['post', 'put'])) {
            foreach ($this->request->getData('Options') ?? [] as $key => $optionData) {
                if(is_array( $optionData['value']) ) {
                    $optionData['value'] = serialize( $optionData['value'] );
                }
                $option = $this->Options->newEntity([]);
                $option->id = $key;
                $option = $this->Options->patchEntity($option, $optionData);
                $this->Options->save($option);
            }
            
            $this->Flash->success('Social login settings have been saved.');
            return $this->redirect(['action' => 'socialLogin']);
        }

        $this->set('options', $options);
        $this->set('settings', $settings);
    }

    public function interstitial()
    {
        $option = $this->Options->findByName('interstitial_price')->first();
        if (!$option) {
            throw new NotFoundException(__('Invalid option'));
        }

        $option->value = unserialize($option->value);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData() ?? [];
            foreach (($data['value'] ?? []) as $key => $value) {
                if (!empty($value['advertiser']) && !empty($value['publisher'])) {
                    $data['value'][$key] = [
                        'advertiser' => abs($value['advertiser']),
                        'publisher' => abs($value['publisher'])
                    ];
                } else {
                    $data['value'][$key] = [
                        'advertiser' => '',
                        'publisher' => ''
                    ];
                }
            }

            //debug( $valuse );

            $data['value'] = serialize($data['value']);

            $option = $this->Options->patchEntity($option, $data);
            if ($this->Options->save($option)) {
                //debug($option);
                $this->Flash->success('Prices have been updated.');
                
                \Cake\Cache\Cache::delete('advertising_rates', '1day');
                \Cake\Cache\Cache::delete('payout_rates', '1day');
                
                return $this->redirect(['action' => 'interstitial']);
            } else {
                $this->Flash->error('Oops! There are mistakes in the form. Please make the correction.');
            }
        }

        $this->set('option', $option);
    }
    
    public function banner()
    {
        $option = $this->Options->findByName('banner_price')->first();
        if (!$option) {
            throw new NotFoundException(__('Invalid option'));
        }

        $option->value = unserialize($option->value);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData() ?? [];
            foreach (($data['value'] ?? []) as $key => $value) {
                if (!empty($value['advertiser']) && !empty($value['publisher'])) {
                    $data['value'][$key] = [
                        'advertiser' => abs($value['advertiser']),
                        'publisher' => abs($value['publisher'])
                    ];
                } else {
                    $data['value'][$key] = [
                        'advertiser' => '',
                        'publisher' => ''
                    ];
                }
            }

            $data['value'] = serialize($data['value']);

            $option = $this->Options->patchEntity($option, $data);
            if ($this->Options->save($option)) {
                //debug($option);
                $this->Flash->success('Prices have been updated.');
                
                \Cake\Cache\Cache::delete('advertising_rates', '1day');
                \Cake\Cache\Cache::delete('payout_rates', '1day');
                
                return $this->redirect(['action' => 'banner']);
            } else {
                $this->Flash->error('Oops! There are mistakes in the form. Please make the correction.');
            }
        }

        $this->set('option', $option);
    }
    
    public function popup()
    {
        $option = $this->Options->findByName('popup_price')->first();
        if (!$option) {
            throw new NotFoundException(__('Invalid option'));
        }

        $option->value = unserialize($option->value);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData() ?? [];
            foreach (($data['value'] ?? []) as $key => $value) {
                if (!empty($value['advertiser']) && !empty($value['publisher'])) {
                    $data['value'][$key] = [
                        'advertiser' => abs($value['advertiser']),
                        'publisher' => abs($value['publisher'])
                    ];
                } else {
                    $data['value'][$key] = [
                        'advertiser' => '',
                        'publisher' => ''
                    ];
                }
            }

            $data['value'] = serialize($data['value']);

            $option = $this->Options->patchEntity($option, $data);
            if ($this->Options->save($option)) {
                //debug($option);
                $this->Flash->success('Prices have been updated.');
                
                \Cake\Cache\Cache::delete('advertising_rates', '1day');
                \Cake\Cache\Cache::delete('payout_rates', '1day');
                
                return $this->redirect(['action' => 'popup']);
            } else {
                $this->Flash->error('Oops! There are mistakes in the form. Please make the correction.');
            }
        }

        $this->set('option', $option);
    }
    
    protected function createEmailFile()
    {
        $options = $this->Options->find()->all();
        
        $config = array(
            'email_from' => '',
            'email_method' => '',
            'email_smtp_host' => '',
            'email_smtp_port' => '',
            'email_smtp_username' => '',
            'email_smtp_password' => '',
            'email_smtp_tls' => ''
        );
        
        foreach ($options as $value) {
            $optName = is_object($value) ? $value->name : $value['name'];
            $optVal = is_object($value) ? $value->value : $value['value'];
            if (array_key_exists($optName, $config)) {
                $config[$optName] = str_replace('\'', '\\\'', (string)($optVal ?? ''));
            }
        }

        $result = copy(CONFIG . 'email.install', CONFIG . 'email.php');
        if (!$result) {
            return __('Could not copy email.php file.');
        }
        $file    = new \Cake\Filesystem\File(CONFIG . 'email.php');
        $content = $file->read();

        foreach ($config as $configKey => $configValue) {
            $content = str_replace('{' . $configKey . '}', (string)($configValue ?? ''), $content);
        }

        if (!$file->write($content)) {
            return __('Could not write email.php file.');
        }

        return true;
    }

}
