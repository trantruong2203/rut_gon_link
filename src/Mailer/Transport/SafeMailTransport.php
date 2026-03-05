<?php

namespace App\Mailer\Transport;

use Cake\Mailer\Email;
use Cake\Mailer\Transport\MailTransport;
use Cake\Network\Exception\SocketException;

/**
 * MailTransport với xử lý null cho PHP 8.x - tránh str_replace deprecation
 */
class SafeMailTransport extends MailTransport
{
    /**
     * @param \Cake\Mailer\Email $email
     * @return array
     */
    public function send(Email $email)
    {
        $eol = $this->_config['eol'] ?? PHP_EOL;
        $headers = $email->getHeaders(['from', 'sender', 'replyTo', 'readReceipt', 'returnPath', 'to', 'cc', 'bcc']);
        $to = $headers['To'] ?? '';
        unset($headers['To']);

        foreach ($headers as $key => $header) {
            $val = is_array($header) ? implode(', ', $header) : $header;
            $headers[$key] = str_replace(["\r", "\n"], '', (string)($val ?? ''));
        }
        $headers = $this->_headersToString($headers, $eol);
        $subject = str_replace(["\r", "\n"], '', (string)($email->getSubject() ?? ''));
        $to = str_replace(["\r", "\n"], '', (string)($to ?? ''));

        $message = implode($eol, $email->message());
        $params = $this->_config['additionalParameters'] ?? null;

        $this->_mail($to, $subject, $message, $headers, $params);

        $headers .= $eol . 'To: ' . $to;
        $headers .= $eol . 'Subject: ' . $subject;

        return ['headers' => $headers, 'message' => $message];
    }
}
