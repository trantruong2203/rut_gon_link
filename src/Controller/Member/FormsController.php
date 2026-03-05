<?php

namespace App\Controller\Member;

use App\Controller\Member\AppMemberController;
use App\Form\ContactForm;
use Cake\Event\Event;

class FormsController extends AppMemberController
{

    public function initialize(): void
    {
        parent::initialize();
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
    }

    public function support()
    {
        $contact = new ContactForm();

        if ($this->request->is('post')) {
            if ($contact->execute($this->request->getData())) {
                $this->Flash->success('We will get back to you soon.');
                return $this->redirect(['action' => 'support']);
            } else {
                $this->Flash->error('There was a problem submitting your form.');
            }
        }
        $this->set('contact', $contact);
    }
}
