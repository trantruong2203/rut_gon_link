<?php

namespace App\Controller;

/**
 * AdminController - xử lý truy cập /admin (không có prefix)
 * Redirect về admin dashboard
 */
class AdminController extends AppController
{
    public function isAuthorized($user = null)
    {
        return ($user['role'] ?? '') === 'admin';
    }

    public function index()
    {
        return $this->redirect(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'dashboard']);
    }
}
