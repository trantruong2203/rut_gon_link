<?php

namespace App\Service;

use Cake\I18n\FrozenTime;
use Cake\Log\Log;

class CampaignVerificationService
{
    const STATUS_UNVERIFIED = 0;
    const STATUS_PENDING = 1;
    const STATUS_VERIFIED = 2;
    const STATUS_FAILED = 3;

    public static function generateToken()
    {
        return bin2hex(random_bytes(16));
    }

    public static function generateCode($token, ?FrozenTime $date = null)
    {
        if (empty($date)) {
            $date = FrozenTime::now();
        }

        $seed = hash('sha256', $token . '|' . $date->format('Y-m-d'));
        $number = hexdec(substr($seed, 0, 8)) % 1000000;

        return str_pad((string)$number, 6, '0', STR_PAD_LEFT);
    }

    public static function getVerificationStatuses()
    {
        return [
            self::STATUS_UNVERIFIED => __('Unverified'),
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_VERIFIED => __('Verified'),
            self::STATUS_FAILED => __('Failed'),
        ];
    }

    public static function getAllowedCodes($token)
    {
        $today = FrozenTime::now();
        $yesterday = $today->subDay(1);

        return [
            self::generateCode($token, $today),
            self::generateCode($token, $yesterday),
        ];
    }

    public static function verifyWebsite($websiteUrl, $token, array $options = [])
    {
        $websiteUrl = trim((string)$websiteUrl);
        if (empty($websiteUrl) || empty($token)) {
            return [
                'verified' => false,
                'note' => __('Missing campaign website URL or verification token.'),
                'details' => [
                    'reason' => 'missing_input',
                ],
            ];
        }

        $maxRetries = max(1, (int)($options['retries'] ?? get_option('campaign_verify_retries', 3)));
        $connectTimeout = max(1, (int)($options['connect_timeout'] ?? get_option('campaign_verify_connect_timeout', 5)));
        $requestTimeout = max(2, (int)($options['request_timeout'] ?? get_option('campaign_verify_request_timeout', 12)));
        $retryDelayMs = max(0, (int)($options['retry_delay_ms'] ?? get_option('campaign_verify_retry_delay_ms', 700)));

        $fetchResult = self::fetchWebsiteWithRetry($websiteUrl, [
            'retries' => $maxRetries,
            'connect_timeout' => $connectTimeout,
            'request_timeout' => $requestTimeout,
            'retry_delay_ms' => $retryDelayMs,
        ]);

        if (!$fetchResult['ok']) {
            return [
                'verified' => false,
                'note' => __('Unable to fetch website content after retries.'),
                'details' => [
                    'reason' => 'fetch_failed',
                    'attempts' => $fetchResult['attempts'],
                    'http_code' => $fetchResult['http_code'],
                    'curl_error' => $fetchResult['error'],
                ],
            ];
        }

        $response = $fetchResult['body'];

        // Passes when tokenized script/link is present on page source.
        if (strpos($response, $token) !== false) {
            return [
                'verified' => true,
                'note' => __('Verification token detected on website.'),
                'details' => [
                    'reason' => 'token_found',
                    'attempts' => $fetchResult['attempts'],
                    'http_code' => $fetchResult['http_code'],
                ],
            ];
        }

        // Fallback: accept today/yesterday dynamic 6-digit code in page content.
        foreach (self::getAllowedCodes($token) as $code) {
            if (preg_match('/\b' . preg_quote($code, '/') . '\b/', $response)) {
                return [
                    'verified' => true,
                    'note' => __('Verification code detected on website.'),
                    'details' => [
                        'reason' => 'code_found',
                        'matched_code' => $code,
                        'attempts' => $fetchResult['attempts'],
                        'http_code' => $fetchResult['http_code'],
                    ],
                ];
            }
        }

        return [
            'verified' => false,
            'note' => __('Verification token/code was not found on website.'),
            'details' => [
                'reason' => 'not_found_in_content',
                'attempts' => $fetchResult['attempts'],
                'http_code' => $fetchResult['http_code'],
            ],
        ];
    }

    public static function verifyAndApply($campaign, $context = 'manual')
    {
        $result = self::verifyWebsite($campaign->website_url, $campaign->verification_token);
        $now = FrozenTime::now();

        $campaign->verification_checked_at = $now;
        $campaign->verification_note = $result['note'];
        $campaign->verification_status = $result['verified']
            ? self::STATUS_VERIFIED
            : self::STATUS_FAILED;

        $details = $result['details'] ?? [];
        $logPayload = [
            'campaign_id' => $campaign->id ?? null,
            'context' => $context,
            'verified' => $result['verified'],
            'note' => $result['note'],
            'reason' => $details['reason'] ?? null,
            'attempts' => $details['attempts'] ?? null,
            'http_code' => $details['http_code'] ?? null,
            'checked_at' => $now->toDateTimeString(),
            'url' => $campaign->website_url ?? null,
        ];

        if ($result['verified']) {
            Log::info('Campaign verification passed: ' . json_encode($logPayload));
        } else {
            Log::warning('Campaign verification failed: ' . json_encode($logPayload));
        }

        return $result;
    }

    protected static function fetchWebsiteWithRetry($url, array $options)
    {
        $attempts = 0;
        $maxRetries = (int)$options['retries'];
        $connectTimeout = (int)$options['connect_timeout'];
        $requestTimeout = (int)$options['request_timeout'];
        $retryDelayMs = (int)$options['retry_delay_ms'];

        $lastError = '';
        $lastHttpCode = 0;
        $body = '';

        while ($attempts < $maxRetries) {
            $attempts++;
            $result = self::fetchWebsiteOnce($url, $connectTimeout, $requestTimeout);

            if ($result['ok']) {
                return [
                    'ok' => true,
                    'body' => $result['body'],
                    'http_code' => $result['http_code'],
                    'error' => '',
                    'attempts' => $attempts,
                ];
            }

            $lastError = (string)$result['error'];
            $lastHttpCode = (int)$result['http_code'];
            $body = (string)$result['body'];

            if ($attempts < $maxRetries && $retryDelayMs > 0) {
                usleep($retryDelayMs * 1000);
            }
        }

        return [
            'ok' => false,
            'body' => $body,
            'http_code' => $lastHttpCode,
            'error' => $lastError,
            'attempts' => $attempts,
        ];
    }

    protected static function fetchWebsiteOnce($url, $connectTimeout, $requestTimeout)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $requestTimeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AdLinkFlyVerificationBot/1.0');

        $body = curl_exec($ch);
        $error = curl_errno($ch) ? curl_error($ch) : '';
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $ok = is_string($body) && $body !== '' && $httpCode >= 200 && $httpCode < 400 && empty($error);

        return [
            'ok' => $ok,
            'body' => is_string($body) ? $body : '',
            'http_code' => $httpCode,
            'error' => $error,
        ];
    }
}
