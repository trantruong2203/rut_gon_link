<?php

namespace App\Mailer\Transport;

use Cake\Mailer\Message;
use Cake\Mailer\Transport\MailTransport;
use Cake\Network\Exception\SocketException;

/**
 * MailTransport với xử lý null cho PHP 8.x - tránh str_replace deprecation
 */
class SafeMailTransport extends MailTransport
{
    /**
     * @param \Cake\Mailer\Message $message
     * @return array
     */
    public function send(Message $message): array
    {
        $eol = $this->_config['eol'] ?? PHP_EOL;
        
        $headers = $message->getHeaders(['from', 'sender', 'replyTo', 'readReceipt', 'returnPath', 'to', 'cc', 'bcc']);
        $to = $headers['To'] ?? '';
        unset($headers['To']);

        foreach ($headers as $key => $header) {
            $val = is_array($header) ? implode(', ', $header) : $header;
            $headers[$key] = str_replace(["\r", "\n"], '', (string)($val ?? ''));
        }
        $headers = $this->_headersToString($headers, $eol);
        $subject = str_replace(["\r", "\n"], '', (string)($message->getSubject() ?? ''));
        $to = str_replace(["\r", "\n"], '', (string)($to ?? ''));

        $messageText = implode($eol, $message->getBody());
        $params = $this->_config['additionalParameters'] ?? null;

        $this->_mail($to, $subject, $messageText, $headers, $params);

        $headers .= $eol . 'To: ' . $to;
        $headers .= $eol . 'Subject: ' . $subject;

        return ['headers' => $headers, 'message' => $messageText];
    }
}
