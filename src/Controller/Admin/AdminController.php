<?php

namespace App\Controller\Admin;

/**
 * AdminController - xử lý truy cập /admin (redirect về dashboard)
 */
class AdminController extends AppAdminController
{
    public function index()
    {
        return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
    }
}
