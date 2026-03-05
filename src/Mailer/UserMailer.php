<?php

namespace App\Mailer;

use Cake\Mailer\Mailer;

class UserMailer extends Mailer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function activation($user)
    {
        $emailConfig = get_option('email_method', 'default') ?: 'default';
        
        $this->setTransport($emailConfig);
        $this->setFrom([(string)(get_option('email_from', 'no_reply@localhost') ?: 'no_reply@localhost') => (string)(get_option('site_name', 'Site') ?: 'Site')]);
        $this->setTo((string)($user->email ?? ''));
        $this->setSubject((string)(__("{0}: New Account", h(get_option('site_name', 'Site') ?: 'Site'))));
        $this->setViewVars([
            'username' => $user->username,
            'activation_key' => $user->activation_key
        ]);
        $this->viewBuilder()->setTemplate('register');
        $this->viewBuilder()->setLayout('app');
        $this->setEmailFormat('html');
    }

    public function changeEmail($user)
    {
        $emailConfig = get_option('email_method', 'default') ?: 'default';
        
        $this->setTransport($emailConfig);
        $this->setFrom([(string)(get_option('email_from', 'no_reply@localhost') ?: 'no_reply@localhost') => (string)(get_option('site_name', 'Site') ?: 'Site')]);
        $this->setTo((string)($user->temp_email ?? ''));
        $this->setSubject((string)(__("{0}: Change Email", h(get_option('site_name', 'Site') ?: 'Site'))));
        $this->setViewVars([
            'username' => $user->username,
            'activation_key' => $user->activation_key
        ]);
        $this->viewBuilder()->setTemplate('change_email');
        $this->viewBuilder()->setLayout('app');
        $this->setEmailFormat('html');
    }

    public function forgotPassword($user)
    {
        $emailConfig = get_option('email_method', 'default') ?: 'default';
        
        $this->setTransport($emailConfig);
        $this->setFrom([(string)(get_option('email_from', 'no_reply@localhost') ?: 'no_reply@localhost') => (string)(get_option('site_name', 'Site') ?: 'Site')]);
        $this->setTo((string)($user->email ?? ''));
        $this->setSubject((string)(__("{0}: Password Reset", h(get_option('site_name', 'Site') ?: 'Site'))));
        $this->setViewVars([
            'username' => $user->username,
            'activation_key' => $user->activation_key
        ]);
        $this->viewBuilder()->setTemplate('reset_password');
        $this->viewBuilder()->setLayout('app');
        $this->setEmailFormat('html');
    }
}
