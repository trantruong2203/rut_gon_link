<?php

namespace App\Controller;

use App\Controller\AppController;
use App\Form\ContactForm;
use Cake\Event\Event;

class FormsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Recaptcha');
    }
    
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['contact']);
    }

    public function contact()
    {
        $this->autoRender = false;

        $this->response = $this->response->withType('json');

        $contact = new ContactForm();

        if (!$this->request->is('ajax')) {
            $content = [
                'status' => 'error',
                'message' => __('Bad Request.'),
            ];
            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        }
        
        if ((get_option('enable_captcha_contact') == 'yes') && isset_recaptcha() && !$this->Recaptcha->verify($this->request->getData('g-recaptcha-response'))) {
            $content = [
                'status' => 'error',
                'message' => __('The CAPTCHA was incorrect. Try again'),
            ];
            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        }

        if ($contact->execute($this->request->getData())) {
            $content = [
                'status' => 'success',
                'message' => __('Your message has been sent!'),
            ];
            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        } else {
            $content = [
                'status' => 'error',
                //'message' => serialize($contact->errors()),
                'message' => __('Can\'t send the message. Please try again latter.'),
            ];
            $this->response = $this->response->withStringBody(json_encode($content));
            return $this->response;
        }
    }
}
