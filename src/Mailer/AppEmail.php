<?php

namespace App\Mailer;

use Cake\Mailer\Email;

/**
 * Email với xử lý null cho PHP 8.x - tránh str_replace/mb_encode_mimeheader deprecation
 */
class AppEmail extends Email
{
    /**
     * @param string|null $text
     * @return string
     */
    protected function _encode($text)
    {
        $text = $text ?? '';
        return parent::_encode($text);
    }

    /**
     * @param array $address
     * @return array
     */
    protected function _formatAddress($address)
    {
        $sanitized = [];
        foreach ($address as $email => $alias) {
            $sanitized[$email] = ($alias !== null && $alias !== '') ? $alias : $email;
        }
        return parent::_formatAddress($sanitized);
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return (string)($this->_subject ?? '');
    }
}
