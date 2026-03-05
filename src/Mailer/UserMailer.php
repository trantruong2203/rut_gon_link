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
        
        $this
            ->setTransport($emailConfig)
            ->from([(string)(get_option('email_from', 'no_reply@localhost') ?: 'no_reply@localhost') => (string)(get_option('site_name', 'Site') ?: 'Site')])
            ->to((string)($user->email ?? ''))
            ->subject((string)(__("{0}: New Account", h(get_option('site_name', 'Site') ?: 'Site'))))
            ->viewVars([
                'username' => $user->username,
                'activation_key' => $user->activation_key
            ])
            ->setTemplate('register')
            ->setLayout('app')
            ->emailFormat('html');
    }

    public function changeEmail($user)
    {
        $emailConfig = get_option('email_method', 'default') ?: 'default';
        
        $this
            ->setTransport($emailConfig)
            ->from([(string)(get_option('email_from', 'no_reply@localhost') ?: 'no_reply@localhost') => (string)(get_option('site_name', 'Site') ?: 'Site')])
            ->to((string)($user->temp_email ?? ''))
            ->subject((string)(__("{0}: Change Email", h(get_option('site_name', 'Site') ?: 'Site'))))
            ->viewVars([
                'username' => $user->username,
                'activation_key' => $user->activation_key
            ])
            ->setTemplate('change_email')
            ->setLayout('app')
            ->emailFormat('html');
    }

    public function forgotPassword($user)
    {
        $emailConfig = get_option('email_method', 'default') ?: 'default';
        
        $this
            ->setTransport($emailConfig)
            ->from([(string)(get_option('email_from', 'no_reply@localhost') ?: 'no_reply@localhost') => (string)(get_option('site_name', 'Site') ?: 'Site')])
            ->to((string)($user->email ?? ''))
            ->subject((string)(__("{0}: Password Reset", h(get_option('site_name', 'Site') ?: 'Site'))))
            ->viewVars([
                'username' => $user->username,
                'activation_key' => $user->activation_key
            ])
            ->setTemplate('reset_password')
            ->setLayout('app')
            ->emailFormat('html');
    }
}
