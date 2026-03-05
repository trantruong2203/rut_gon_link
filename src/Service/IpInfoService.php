<?php

namespace App\Service;

use Cake\Cache\Cache;

/**
 * IPinfo.io API integration for IP/VPN/Proxy detection.
 * Uses https://ipinfo.io/{ip}?token=...
 * Privacy Detection (vpn/proxy/hosting) requires IPinfo paid plan.
 * Fallback: checks "org" field for datacenter/VPN keywords when privacy not available.
 */
class IpInfoService
{
    const CACHE_PREFIX = 'ipinfo_';
    const CACHE_DURATION = '+10 minutes';
    const API_URL = 'https://ipinfo.io/%s?token=%s';

    /** Keywords in org field suggesting VPN/proxy/hosting */
    const DIRTY_ORG_KEYWORDS = [
        'vpn', 'proxy', 'hosting', 'datacenter', 'server', 'cloud',
        'digitalocean', 'amazon', 'google cloud', 'microsoft azure',
        'linode', 'vultr', 'ovh', 'hetzner', 'tor exit', 'tor relay',
    ];

    /**
     * Check if IP is clean (not proxy/VPN/hosting).
     *
     * @param string $ip IP address to check
     * @param string|null $token API token (uses get_option if null)
     * @return bool true if IP is clean, false if proxy/VPN/hosting or check failed
     */
    public static function isIpClean($ip, $token = null)
    {
        if (empty($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return true; // Invalid IP, allow
        }

        if (get_option('enable_proxycheck', 'no') !== 'yes') {
            return true; // Feature disabled (shared with ProxyCheck)
        }

        $apiToken = $token ?: get_option('ipinfo_token', '');
        if (empty($apiToken)) {
            return true; // No API key, allow
        }

        $cacheKey = self::CACHE_PREFIX . md5($ip);
        $cached = Cache::read($cacheKey);
        if ($cached !== false) {
            return (bool) $cached;
        }

        $url = sprintf(self::API_URL, urlencode($ip), urlencode($apiToken));
        $result = true; // Default allow on error

        try {
            $response = @file_get_contents($url);
            if ($response === false) {
                Cache::write($cacheKey, $result, self::CACHE_DURATION);
                return $result;
            }

            $data = json_decode($response, true);
            if (!is_array($data)) {
                Cache::write($cacheKey, $result, self::CACHE_DURATION);
                return $result;
            }

            // Privacy Detection (paid plan): vpn, proxy, hosting, tor, relay
            if (isset($data['privacy']) && is_array($data['privacy'])) {
                $privacy = $data['privacy'];
                if (!empty($privacy['vpn']) || !empty($privacy['proxy']) || !empty($privacy['hosting']) ||
                    !empty($privacy['tor']) || !empty($privacy['relay'])) {
                    $result = false;
                    Cache::write($cacheKey, $result, self::CACHE_DURATION);
                    return $result;
                }
            }

            // Fallback: check org for datacenter/VPN keywords
            $org = isset($data['org']) ? strtolower($data['org']) : '';
            foreach (self::DIRTY_ORG_KEYWORDS as $keyword) {
                if (strpos($org, $keyword) !== false) {
                    $result = false;
                    break;
                }
            }

            Cache::write($cacheKey, $result, self::CACHE_DURATION);
        } catch (\Exception $e) {
            \Cake\Log\Log::warning('IpInfo API error: ' . $e->getMessage());
        }

        return $result;
    }
}
