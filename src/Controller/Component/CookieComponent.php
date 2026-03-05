<?php

declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Http\Cookie\Cookie;

/**
 * Cookie component replacement for CakePHP 4.
 * CakePHP 4 removed CookieComponent; this provides a compatible API using Request/Response.
 */
class CookieComponent extends Component
{
    /**
     * Stored config options per key for configKey() + write() flow.
     *
     * @var array<string, array>
     */
    protected $_keyConfig = [];

    /**
     * Read a cookie value.
     *
     * @param string $key Cookie name
     * @return mixed Cookie value, or null if not set
     */
    public function read(?string $key = null)
    {
        $request = $this->getController()->getRequest();
        if ($key === null) {
            return $request->getCookieParams();
        }
        $value = $request->getCookie($key);
        if ($value === null) {
            return null;
        }
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return $value;
    }

    /**
     * Check if a cookie exists.
     *
     * @param string $key Cookie name
     * @return bool
     */
    public function check(string $key): bool
    {
        return $this->getController()->getRequest()->getCookie($key) !== null;
    }

    /**
     * Write a cookie.
     *
     * @param string $key Cookie name
     * @param mixed $value Cookie value (string or array)
     * @param bool $encrypt Unused, kept for API compatibility
     * @return void
     */
    public function write(string $key, $value, $encrypt = true): void
    {
        $options = $this->_keyConfig[$key] ?? ['path' => '/', 'httponly' => true];
        if (isset($options['httpOnly'])) {
            $options['httponly'] = $options['httpOnly'];
            unset($options['httpOnly']);
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $cookie = Cookie::create($key, (string) $value, $options);
        $this->getController()->setResponse(
            $this->getController()->getResponse()->withCookie($cookie)
        );
        unset($this->_keyConfig[$key]);
    }

    /**
     * Delete a cookie.
     *
     * @param string $key Cookie name
     * @return void
     */
    public function delete(string $key): void
    {
        $cookie = Cookie::create($key, '');
        $this->getController()->setResponse(
            $this->getController()->getResponse()->withExpiredCookie($cookie)
        );
    }

    /**
     * Set options for a cookie key (used before write).
     *
     * @param string $key Cookie name
     * @param array $options Options: expires, path, httpOnly, secure, etc.
     * @return void
     */
    public function configKey(string $key, array $options): void
    {
        $this->_keyConfig[$key] = $options + ($this->_keyConfig[$key] ?? ['path' => '/', 'httponly' => true]);
    }
}
