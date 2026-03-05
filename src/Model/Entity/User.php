<?php

namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;

class User extends Entity
{

    // Make all fields mass assignable for now.
    protected $_accessible = ['*' => true];

    protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }

    protected function _getTrafficSources($value)
    {
        if (is_string($value) && !empty($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($value) ? $value : [];
    }

    protected function _setTrafficSources($value)
    {
        if (is_array($value)) {
            $filtered = array_values(array_filter($value, function ($item) {
                return !empty($item['type']) && !empty(trim($item['url'] ?? ''));
            }));
            return json_encode($filtered, JSON_UNESCAPED_UNICODE);
        }
        return $value;
    }
}
