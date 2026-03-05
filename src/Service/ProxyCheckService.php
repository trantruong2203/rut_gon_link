<?php

namespace App\Service;

use Cake\Cache\Cache;

/**
 * ProxyCheck.io API integration for IP/VPN/Proxy detection.
 * Uses v2 API: https://proxycheck.io/v2/{IP}?key=...
 */
class ProxyCheckService
{
    const CACHE_PREFIX = 'proxycheck_';
    const CACHE_DURATION = '+10 minutes';
    const API_URL = 'https://proxycheck.io/v2/%s?key=%s&vpn=1&asn=0';

    /**
     * Check if IP is clean (not proxy/VPN).
     *
     * @param string $ip IP address to check
     * @param string|null $apiKey API key (uses get_option if null)
     * @return bool true if IP is clean, false if proxy/VPN or check failed
     */
    public static function isIpClean($ip, $apiKey = null)
    {
        if (empty($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return true; // Invalid IP, allow (avoid blocking)
        }

        if (get_option('enable_proxycheck', 'no') !== 'yes') {
            return true; // Feature disabled
        }

        $key = get_option('proxycheck_api_key', '');
        if (!empty($apiKey)) {
            $key = $apiKey;
        }
        if (empty($key)) {
            return true; // No API key, allow
        }

        $cacheKey = self::CACHE_PREFIX . md5($ip);
        $cached = Cache::read($cacheKey);
        if ($cached !== false) {
            return (bool) $cached;
        }

        $url = sprintf(self::API_URL, urlencode($ip), urlencode($key));
        $result = true; // Default allow on error

        try {
            $response = @file_get_contents($url);
            if ($response === false) {
                Cache::write($cacheKey, $result, self::CACHE_DURATION);
                return $result;
            }

            $data = json_decode($response, true);
            if (!is_array($data) || !isset($data[$ip])) {
                Cache::write($cacheKey, $result, self::CACHE_DURATION);
                return $result;
            }

            $ipData = $data[$ip];
            $proxy = isset($ipData['proxy']) ? strtolower(trim($ipData['proxy'])) : 'no';
            $result = ($proxy !== 'yes');

            Cache::write($cacheKey, $result, self::CACHE_DURATION);
        } catch (\Exception $e) {
            \Cake\Log\Log::warning('ProxyCheck API error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Check IP using configured provider (proxycheck or ipinfo).
     *
     * @param string $ip IP address to check
     * @return bool true if IP is clean, false if proxy/VPN
     */
    public static function checkIp($ip)
    {
        $provider = get_option('proxy_check_provider', 'proxycheck');
        if ($provider === 'ipinfo') {
            return \App\Service\IpInfoService::isIpClean($ip);
        }
        return self::isIpClean($ip);
    }
}
