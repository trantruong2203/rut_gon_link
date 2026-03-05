<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\I18n\I18n;

class FrontController extends AppController
{

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->setLanguage();
    }

    public function beforeRender(\Cake\Event\EventInterface $event)
    {
        parent::beforeRender($event);
        $this->viewBuilder()->setTheme(str_replace(' ', '', get_option('theme', 'ClassicTheme')));
    }

    protected function setLanguage()
    {
        if (empty(get_option('site_languages'))) {
            return true;
        }
        $langParam = $this->request->getQuery('lang');
        if ($langParam !== null &&
            in_array($langParam, get_site_languages(true))) {
            setcookie('lang', $langParam, time() + (86400 * 30 * 12), '/');
            return $this->redirect('http://' . env('SERVER_NAME') . $this->request->getRequestTarget());
        }

        if (!isset($_COOKIE['lang']) && isset($this->request->acceptLanguage()[0])) {

            $lang = substr($this->request->acceptLanguage()[0], 0, 2);

            $langs = get_site_languages(true);

            $valid_langs = [];
            foreach ($langs as $key => $value) {
                if (preg_match('/^' . $lang . '/', $value)) {
                    $valid_langs[] = $value;
                }
            }

            if (isset($valid_langs[0])) {
                setcookie('lang', $valid_langs[0], time() + (86400 * 30 * 12));
                return $this->redirect('http://' . env('SERVER_NAME') . $this->request->getRequestTarget());
            }
        }

        if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], get_site_languages(true))) {
            I18n::setLocale($_COOKIE['lang']);
        }
    }
}
