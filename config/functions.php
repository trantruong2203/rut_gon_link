<?php

use Cake\Core\Configure;

function database_connect()
{
    try {
        $connection = \Cake\Datasource\ConnectionManager::get('default');
        $connected = $connection->connect();
    } catch (\Exception $ex) {
        $connected = false;
        $errorMsg = $ex->getMessage();
        if (method_exists($ex, 'getAttributes')) {
            $attributes = $ex->getAttributes();
            if (isset($attributes['message'])) {
                $errorMsg .= '<br />' . $attributes['message'];
            }
        }
    }
    return $connected;
}

function is_app_installed()
{
    if (Configure::read('Adlinkfly.installed')) {
        return true;
    }

    if ((bool) get_option('installed', 0)) {
        return true;
    }

    return false;
}

function get_option($name, $default = '')
{
    if (!database_connect()) {
        return $default;
    }

    try {

        static $settings;

        if (!isset($settings)) {
            $options = \Cake\ORM\TableRegistry::getTableLocator()->get('Options');
            //$query   = $options->find()->select( ['name', 'value' ] )->cache( 'options' )->all();
            $query = $options->find()->select(['name', 'value'])->all();
            $settings = [];
            foreach ($query as $row) {
                $settings[$row->name] = (is_serialized($row->value)) ? unserialize($row->value) : $row->value;
            }
        }

        if (!array_key_exists($name, $settings)) {
            return $default;
        }

        if (is_array($settings[$name])) {
            return (!empty($settings[$name])) ? $settings[$name] : $default;
        } else {
            return (isset($settings[$name]) && strlen($settings[$name]) > 0) ? $settings[$name] : $default;
        }
    } catch (\Exception $ex) {
        return $default;
    }
}

// check this if error happened
// https://core.trac.wordpress.org/browser/tags/4.0.1/src/wp-includes/functions.php#L283
function is_serialized($data)
{
    // if it isn't a string, it isn't serialized
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' === $data) {
        return true;
    }
    if (strlen($data) < 4) {
        return false;
    }
    if (':' !== $data[1]) {
        return false;
    }
    $lastc = substr($data, -1);
    if (';' !== $lastc && '}' !== $lastc) {
        return false;
    }
    $token = $data[0];
    switch ($token) {
        case 's':
        case 'a':
        case 'O':
        case 'i':
        case 'd':
        case 'b':
            if (@unserialize($data) === false && $data !== 'b:0;') {
                return false;
            }
            return true;
        default:
            return false;
    }
}

/**
 * 
 */
function get_http_headers($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 1);

    $headers_string = curl_exec($ch);

    curl_close($ch);

    $data = [];
    //$headers = explode(PHP_EOL, $headers_string);
    $headers = explode("\n", str_replace("\r", "\n", $headers_string));
    foreach ($headers as $header) {
        $parts = explode(':', $header);
        if (count($parts) === 2) {
            $data[strtolower(trim($parts[0]))] = strtolower(trim($parts[1]));
        }
    }

    return $data;
}

/**
 * 
 */
function curlRequest($url, $method = 'GET', $data = array(), $headers = array())
{
    $ch = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($ch, CURLOPT_POST, 1);

            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            break;
        case "PUT":
            curl_setopt($ch, CURLOPT_PUT, 1);
            break;
        default:
            if ($data) {
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    if ($headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if (empty(@ini_get('open_basedir'))) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
    }
    if (null != env('HTTP_USER_AGENT')) {
        curl_setopt($ch, CURLOPT_USERAGENT, env('HTTP_USER_AGENT'));
    }

    $result = curl_exec($ch);
    //$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        \Cake\Log\Log::write('error', curl_error($ch));
    }

    curl_close($ch);

    return $result;
}

/**
 * 
 */
function curlHtmlHeadRequest($url, $method = 'GET', $data = array(), $headers = array())
{

    $obj = new \stdClass(); //create an object variable to access class functions and variables
    $obj->result = '';

    $ch = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($ch, CURLOPT_POST, 1);

            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            break;
        case "PUT":
            curl_setopt($ch, CURLOPT_PUT, 1);
            break;
        default:
            if ($data) {
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    if ($headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $str) use ($obj) {
        $obj->result .= $str;
        /*
          if (stripos($obj->result, '<body') !== false) {
          return false;
          }
         */
        return strlen($str); //return the exact length
    });
    curl_setopt($ch, CURLOPT_NOPROGRESS, false);
    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($ch, $downloadSize, $downloaded, $uploadSize, $uploaded) {
        // If $Downloaded exceeds 128KB, returning non-0 breaks the connection!
        return ($downloaded > (128 * 1024)) ? 1 : 0;
    });

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if (empty(@ini_get('open_basedir'))) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
    }
    if (null != env('HTTP_USER_AGENT')) {
        curl_setopt($ch, CURLOPT_USERAGENT, env('HTTP_USER_AGENT'));
    }

    curl_exec($ch);
    //$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        //\Cake\Log\Log::write('error', curl_error($ch));
    }

    curl_close($ch);

    return $obj->result;
}

function emptyTmp()
{
    $exclude = ['empty', 'ms_license_response_result'];
    $tmpPath = rtrim(TMP, DS) . DS;

    if (!is_dir($tmpPath)) {
        return;
    }

    try {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tmpPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $path) {
            if ($path->isFile() && !in_array($path->getFilename(), $exclude)) {
                @unlink($path->getPathname());
            }
        }
    } catch (\Exception $e) {
        \Cake\Log\Log::warning('emptyTmp error: ' . $e->getMessage());
    }
}

function isset_recaptcha()
{
    $enable_captcha = get_option('enable_captcha');
    if ('yes' != $enable_captcha) {
        return false;
    }

    $recaptcha_siteKey = get_option('reCAPTCHA_site_key');
    $recaptcha_secretKey = get_option('reCAPTCHA_secret_key');
    if (!empty($recaptcha_siteKey) && !empty($recaptcha_secretKey)) {
        return true;
    }

    return false;
}

function generate_random_string($length = 10, $special = false)
{
    $specialChars = '~!@#$%^&*(){}[],./?';
    $alphaNum = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $all_chars = $alphaNum;
    if ($special) {
        $all_chars .= $specialChars;
    }

    $string = '';
    $i = 0;
    while ($i < $length) {
        $random = mt_rand(0, strlen($all_chars) - 1);
        $string .= $all_chars[$random];
        $i = $i + 1;
    }
    return $string;
}

/**
 * Generate random IP address
 * @return string Random IP address
 */
function random_ipv4()
{
    // http://stackoverflow.com/a/10268612
    //return mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255);
    // // http://board.phpbuilder.com/showthread.php?10346623-Generating-a-random-IP-Address&p=10830872&viewfull=1#post10830872
    return long2ip(rand(0, 255 * 255) * rand(0, 255 * 255));
}

/**
 * Traffic source constants for campaigns
 * 1=All, 4=Google Search, 5=Backlink, 6=Direct
 */
function get_traffic_source_options()
{
    return [
        '1' => __('All'),
        '4' => __('Google Search'),
        '5' => __('Backlink'),
        '6' => __('Direct'),
    ];
}

/**
 * Detect visitor traffic source from HTTP_REFERER
 * Returns 4=Google Search, 5=Backlink, 6=Direct
 */
function get_traffic_source_from_referrer($defaultByDevice = null)
{
    $ref = strtolower((string) (env('HTTP_REFERER') ?: ''));
    $host = strtolower((string) (env('HTTP_HOST') ?: ''));
    $mainDomain = strtolower((string) (get_option('main_domain', '') ?: ''));
    $shortDomain = strtolower((string) (get_option('default_short_domain', '') ?: $host));

    if (empty($ref)) {
        return 6; // Direct - no referrer
    }

    $refHost = parse_url($ref, PHP_URL_HOST);
    if (empty($refHost)) {
        return 6;
    }
    $refHost = strtolower($refHost);

    // Same domain or our domains = Direct
    if ($refHost === $host || $refHost === $mainDomain || $refHost === $shortDomain) {
        return 6;
    }

    // Google Search (google.com, google.vn, www.google.com, etc.)
    if (strpos($refHost, 'google') !== false) {
        return 4;
    }

    // Other external = Backlink
    return 5;
}

/**
 * Countdown duration options (seconds) for campaign
 * Bảng giá: 60s, 120s, 150s, 200s - Version 1 đắt hơn Version 2
 */
function get_countdown_options()
{
    return [
        60 => __('60 giây'),
        120 => __('120 giây'),
        150 => __('150 giây'),
        200 => __('200 giây'),
    ];
}

/**
 * Bảng giá theo ảnh: Version 1 (2 bước) vs Version 2 (1 bước), đã giảm 40%
 * Giá / 1000 view (VNĐ). Publisher nhận 50%.
 */
function get_interstitial_price_table()
{
    return [
        1 => [60 => 420, 120 => 510, 150 => 600, 200 => 690],   // Version 1 - Tốt nhất
        2 => [60 => 360, 120 => 450, 150 => 540, 200 => 630],   // Version 2
    ];
}

/**
 * Tính giá mỗi 1000 view theo countdown + campaign_version
 * Giá trả publisher = 50% (chia đôi với nền tảng)
 */
function calc_interstitial_price_per_1000($countdown_seconds, $campaign_version = 1)
{
    $table = get_interstitial_price_table();
    $version = (int) $campaign_version;
    if (!isset($table[$version])) {
        $version = 1;
    }
    $prices = $table[$version];
    $advertiser = $prices[(int) $countdown_seconds] ?? $prices[60];
    $publisher = round($advertiser * 0.5, 0); // 50-50
    return ['advertiser' => $advertiser, 'publisher' => $publisher];
}

/**
 * Tính tổng tiền = (giá/1000) × tổng view nhập vào
 * purchase = tổng view (để weight = views/purchase*100 và dừng đúng khi đủ)
 */
function calc_interstitial_total_price($total_view_limit, $countdown_seconds, $campaign_version = 1)
{
    $per1000 = calc_interstitial_price_per_1000($countdown_seconds, $campaign_version);
    $totalViews = max(0, (int) $total_view_limit);
    $total = round($per1000['advertiser'] * $totalViews / 1000, 0);
    return [
        'total' => $total,
        'advertiser_price' => $per1000['advertiser'],
        'publisher_price' => $per1000['publisher'],
        'purchase' => max(1, $totalViews), // tổng view để weight = views/purchase*100
    ];
}

/**
 * Campaign version options
 */
function get_campaign_version_options()
{
    return [
        1 => __('Version 1 (Tốt nhất) - 2 bước: Chờ X thời gian, click link nội bộ, chờ X thời gian (25-35s)'),
        2 => __('Version 2 - 1 bước: Chờ X thời gian hết là xong'),
    ];
}

/**
 * Get client IP address
 * @return string IP address
 */
function get_ip()
{
    if (env("HTTP_CF_CONNECTING_IP")) {
        $ip = env("HTTP_CF_CONNECTING_IP");
    } elseif (env("HTTP_CLIENT_IP")) {
        $ip = env("HTTP_CLIENT_IP");
    } elseif (env("HTTP_X_FORWARDED_FOR")) {
        $ip = env("HTTP_X_FORWARDED_FOR");
        if (strstr($ip, ',')) {
            $tmp = explode(',', $ip);
            $ip = trim($tmp[0]);
        }
    } else {
        $ip = env("REMOTE_ADDR");
    }

    //$ip = random_ipv4();
    return $ip;
}

function display_price_currency($price, $options = [])
{
    $defaults = [
        'precision' => 6,
        'places' => 2,
        'locale' => locale_get_default(),
        get_option('currency_position', 'before') => get_option('currency_symbol', '$')
    ];
    $options = array_merge($defaults, $options);
    return \Cake\I18n\Number::format($price, $options);
}

function display_date_timezone($time)
{
    return (new \Cake\I18n\Time($time))->i18nFormat(null, get_option('timezone', 'UTC'), null);
}

function require_database_upgrade()
{
    if (version_compare(APP_VERSION, get_option('app_version', '1.0.0'), '>')) {
        return true;
    }
    return false;
}

function get_logo()
{
    $site_name = h(get_option('site_name'));
    $logo_url = h(get_option('logo_url'));

    $data = ['type' => 'text', 'content' => $site_name];

    if (!empty($logo_url)) {
        $data['type'] = 'image';
        $data['content'] = "<img src='{$logo_url}' alt='{$site_name}' />";
    }
    return $data;
}

function get_logo_alt()
{
    $site_name = h(get_option('site_name'));
    $logo_url = h(get_option('logo_url_alt'));

    $data = ['type' => 'text', 'content' => $site_name];

    if (!empty($logo_url)) {
        $data['type'] = 'image';
        $data['content'] = "<img src='{$logo_url}' alt='{$site_name}' />";
    }
    return $data;
}

function get_short_url($alias = '', $domain = '')
{
    if (empty($domain)) {
        $domain = get_default_short_domain();
    }
    /*
      $base_url = \Cake\Routing\Router::url([
      '_scheme' => 'http',
      '_host' => $domain,
      'controller' => null,
      'action' => null,
      'plugin' => null,
      'prefix' => false
      ], true);
     */
    $request = \Cake\Routing\Router::getRequest();
    $base_url = 'http://' . $domain . ($request ? $request->getAttribute('base') : '');
    //$base_url = str_ireplace('https://', 'http://', $base_url);
    return $base_url . '/' . $alias;
}

function get_default_short_domain()
{
    $default_short_domain = get_option('default_short_domain', '');
    if (!empty($default_short_domain)) {
        return $default_short_domain;
    }

    $main_domain = get_option('main_domain', '');
    if (!empty($main_domain)) {
        return $main_domain;
    }

    return env("HTTP_HOST", "");
}

function get_multi_domains_list()
{
    $domains = explode(',', get_option('multi_domains'));
    $domains = array_map('trim', $domains);
    $domains = array_filter($domains);
    $domains = array_unique($domains);
    $domains = array_combine($domains, $domains);

    $default_short_domain = get_option('default_short_domain', '');
    $main_domain = get_option('main_domain', '');

    unset($domains[$default_short_domain], $domains[$main_domain]);

    return $domains;
}

function get_all_multi_domains_list()
{
    $domains = get_multi_domains_list();
    $add_domains = [];

    $default_short_domain = get_option('default_short_domain', '');
    if (!empty($default_short_domain)) {
        $add_domains[$default_short_domain] = $default_short_domain;
        return $add_domains + $domains;
    }

    $main_domain = get_option('main_domain', '');
    if (!empty($main_domain)) {
        $add_domains[$main_domain] = $main_domain;
        return $add_domains + $domains;
    }

    $add_domains[env("HTTP_HOST", "")] = env("HTTP_HOST", "");

    return $add_domains + $domains;
}

function get_all_domains_list()
{
    $domains = get_multi_domains_list();
    $add_domains = [];

    $default_short_domain = get_option('default_short_domain', '');
    if (!empty($default_short_domain)) {
        $add_domains[$default_short_domain] = $default_short_domain;
        $add_domains = $add_domains + $domains;
    }

    $main_domain = get_option('main_domain', '');
    if (!empty($main_domain)) {
        $add_domains[$main_domain] = $main_domain;
        $add_domains = $add_domains + $domains;
    }

    $add_domains[env("HTTP_HOST", "")] = env("HTTP_HOST", "");

    return $add_domains + $domains;
}

function get_allowed_ads()
{
    $ads = [];

    if (get_option('enable_interstitial', 'yes') == 'yes') {
        $ads['1'] = __('Interstitial');
    }

    if (get_option('enable_banner', 'yes') == 'yes') {
        $ads['2'] = __('Banner');
    }

    if (get_option('enable_noadvert', 'yes') == 'yes') {
        $ads['0'] = __('No Advert');
    }

    return $ads;
}

function get_statistics_reasons()
{
    return [
        0 => __("---"),
        1 => __("Earn"),
        2 => __("Disabled Cookies"),
        3 => __("Anonymous User"),
        4 => __("Adblock"),
        5 => __("Proxy"),
        6 => __("IP Changed"),
        7 => __("Not Unique"),
        8 => __("Full Weight"),
        9 => __("Default Campaign"),
        10 => __("Direct")
    ];
}

function get_site_languages($all = false)
{
    $default_language = get_option('language');
    $site_languages = get_option('site_languages', []);
    $site_languages = array_combine($site_languages, $site_languages);
    unset($site_languages[$default_language]);
    if ($all === true) {
        $site_languages[$default_language] = $default_language;
    }
    ksort($site_languages);
    return $site_languages;
}

function get_countries($campaing = false)
{
    $countries = [
        "AF" => __("Afganistan"),
        "AL" => __("Albania"),
        "DZ" => __("Algeria"),
        "AS" => __("American Samoa"),
        "AD" => __("Andorra"),
        "AO" => __("Angola"),
        "AI" => __("Anguilla"),
        "AQ" => __("Antarctica"),
        "AG" => __("Antigua and Barbuda"),
        "AR" => __("Argentina"),
        "AM" => __("Armenia"),
        "AW" => __("Aruba"),
        "AU" => __("Australia"),
        "AT" => __("Austria"),
        "AX" => __("Åland Islands"),
        "AZ" => __("Azerbaijan"),
        "BS" => __("Bahamas"),
        "BH" => __("Bahrain"),
        "BD" => __("Bangladesh"),
        "BB" => __("Barbados"),
        "BY" => __("Belarus"),
        "BE" => __("Belgium"),
        "BZ" => __("Belize"),
        "BJ" => __("Benin"),
        "BM" => __("Bermuda"),
        "BT" => __("Bhutan"),
        "BO" => __("Bolivia"),
        "BA" => __("Bosnia and Herzegowina"),
        "BW" => __("Botswana"),
        "BV" => __("Bouvet Island"),
        "BR" => __("Brazil"),
        "IO" => __("British Indian Ocean Territory"),
        "BN" => __("Brunei Darussalam"),
        "BG" => __("Bulgaria"),
        "BF" => __("Burkina Faso"),
        "BI" => __("Burundi"),
        "KH" => __("Cambodia"),
        "CM" => __("Cameroon"),
        "CA" => __("Canada"),
        "CV" => __("Cape Verde"),
        "KY" => __("Cayman Islands"),
        "CF" => __("Central African Republic"),
        "TD" => __("Chad"),
        "CL" => __("Chile"),
        "CN" => __("China"),
        "CX" => __("Christmas Island"),
        "CC" => __("Cocos (Keeling) Islands"),
        "CO" => __("Colombia"),
        "KM" => __("Comoros"),
        "CG" => __("Congo"),
        "CD" => __("Congo, the Democratic Republic of the"),
        "CK" => __("Cook Islands"),
        "CR" => __("Costa Rica"),
        "CI" => __("Cote d'Ivoire"),
        "CW" => __("Curaçao"),
        "HR" => __("Croatia (Hrvatska)"),
        "CU" => __("Cuba"),
        "CY" => __("Cyprus"),
        "CZ" => __("Czech Republic"),
        "DK" => __("Denmark"),
        "DJ" => __("Djibouti"),
        "DM" => __("Dominica"),
        "DO" => __("Dominican Republic"),
        "TP" => __("East Timor"),
        "EC" => __("Ecuador"),
        "EG" => __("Egypt"),
        "SV" => __("El Salvador"),
        "GQ" => __("Equatorial Guinea"),
        "ER" => __("Eritrea"),
        "EE" => __("Estonia"),
        "ET" => __("Ethiopia"),
        "FK" => __("Falkland Islands (Malvinas)"),
        "FO" => __("Faroe Islands"),
        "FJ" => __("Fiji"),
        "FI" => __("Finland"),
        "FR" => __("France"),
        "FX" => __("France, Metropolitan"),
        "GF" => __("French Guiana"),
        "PF" => __("French Polynesia"),
        "TF" => __("French Southern Territories"),
        "GA" => __("Gabon"),
        "GM" => __("Gambia"),
        "GE" => __("Georgia"),
        "DE" => __("Germany"),
        "GH" => __("Ghana"),
        "GI" => __("Gibraltar"),
        "GR" => __("Greece"),
        "GL" => __("Greenland"),
        "GD" => __("Grenada"),
        "GP" => __("Guadeloupe"),
        "GU" => __("Guam"),
        "GT" => __("Guatemala"),
        "GN" => __("Guinea"),
        "GW" => __("Guinea-Bissau"),
        "GY" => __("Guyana"),
        "HT" => __("Haiti"),
        "HM" => __("Heard and Mc Donald Islands"),
        "VA" => __("Holy See (Vatican City State)"),
        "HN" => __("Honduras"),
        "HK" => __("Hong Kong"),
        "HU" => __("Hungary"),
        "IS" => __("Iceland"),
        "IM" => __("Isle of Man"),
        "IN" => __("India"),
        "ID" => __("Indonesia"),
        "IR" => __("Iran (Islamic Republic of)"),
        "IQ" => __("Iraq"),
        "IE" => __("Ireland"),
        "IL" => __("Israel"),
        "IT" => __("Italy"),
        "JM" => __("Jamaica"),
        "JP" => __("Japan"),
        "JO" => __("Jordan"),
        "KZ" => __("Kazakhstan"),
        "KE" => __("Kenya"),
        "KI" => __("Kiribati"),
        "KP" => __("Korea, Democratic People's Republic of"),
        "KR" => __("Korea, Republic of"),
        "KW" => __("Kuwait"),
        "KG" => __("Kyrgyzstan"),
        "LA" => __("Lao People's Democratic Republic"),
        "LV" => __("Latvia"),
        "LB" => __("Lebanon"),
        "LS" => __("Lesotho"),
        "LR" => __("Liberia"),
        "LY" => __("Libyan Arab Jamahiriya"),
        "LI" => __("Liechtenstein"),
        "LT" => __("Lithuania"),
        "LU" => __("Luxembourg"),
        "MO" => __("Macau"),
        "MK" => __("Macedonia, The Former Yugoslav Republic of"),
        "MG" => __("Madagascar"),
        "MW" => __("Malawi"),
        "MY" => __("Malaysia"),
        "MV" => __("Maldives"),
        "ML" => __("Mali"),
        "MT" => __("Malta"),
        "MH" => __("Marshall Islands"),
        "MQ" => __("Martinique"),
        "MR" => __("Mauritania"),
        "MU" => __("Mauritius"),
        "YT" => __("Mayotte"),
        "MX" => __("Mexico"),
        "FM" => __("Micronesia, Federated States of"),
        "MD" => __("Moldova, Republic of"),
        "MC" => __("Monaco"),
        "ME" => __("Montenegro"),
        "MN" => __("Mongolia"),
        "MS" => __("Montserrat"),
        "MA" => __("Morocco"),
        "MZ" => __("Mozambique"),
        "MM" => __("Myanmar"),
        "NA" => __("Namibia"),
        "NR" => __("Nauru"),
        "NP" => __("Nepal"),
        "NL" => __("Netherlands"),
        "AN" => __("Netherlands Antilles"),
        "NC" => __("New Caledonia"),
        "NZ" => __("New Zealand"),
        "NI" => __("Nicaragua"),
        "NE" => __("Niger"),
        "NG" => __("Nigeria"),
        "NU" => __("Niue"),
        "NF" => __("Norfolk Island"),
        "MP" => __("Northern Mariana Islands"),
        "NO" => __("Norway"),
        "OM" => __("Oman"),
        "PK" => __("Pakistan"),
        "PW" => __("Palau"),
        "PA" => __("Panama"),
        "PG" => __("Papua New Guinea"),
        "PY" => __("Paraguay"),
        "PE" => __("Peru"),
        "PH" => __("Philippines"),
        "PN" => __("Pitcairn"),
        "PL" => __("Poland"),
        "PT" => __("Portugal"),
        "PR" => __("Puerto Rico"),
        "PS" => __("Palestine"),
        "QA" => __("Qatar"),
        "RE" => __("Reunion"),
        "RO" => __("Romania"),
        "RS" => __("Republic of Serbia"),
        "RU" => __("Russian Federation"),
        "RW" => __("Rwanda"),
        "KN" => __("Saint Kitts and Nevis"),
        "LC" => __("Saint LUCIA"),
        "VC" => __("Saint Vincent and the Grenadines"),
        "WS" => __("Samoa"),
        "SM" => __("San Marino"),
        "ST" => __("Sao Tome and Principe"),
        "SA" => __("Saudi Arabia"),
        "SN" => __("Senegal"),
        "SC" => __("Seychelles"),
        "SL" => __("Sierra Leone"),
        "SG" => __("Singapore"),
        "SK" => __("Slovakia (Slovak Republic)"),
        "SI" => __("Slovenia"),
        "SB" => __("Solomon Islands"),
        "SO" => __("Somalia"),
        "SX" => __("Sint Maarten"),
        "ZA" => __("South Africa"),
        "GS" => __("South Georgia and the South Sandwich Islands"),
        "ES" => __("Spain"),
        "LK" => __("Sri Lanka"),
        "SH" => __("St. Helena"),
        "PM" => __("St. Pierre and Miquelon"),
        "SD" => __("Sudan"),
        "SR" => __("Suriname"),
        "SJ" => __("Svalbard and Jan Mayen Islands"),
        "SZ" => __("Swaziland"),
        "SE" => __("Sweden"),
        "CH" => __("Switzerland"),
        "SY" => __("Syrian Arab Republic"),
        "TW" => __("Taiwan, Province of China"),
        "TJ" => __("Tajikistan"),
        "TZ" => __("Tanzania, United Republic of"),
        "TH" => __("Thailand"),
        "TG" => __("Togo"),
        "TK" => __("Tokelau"),
        "TO" => __("Tonga"),
        "TT" => __("Trinidad and Tobago"),
        "TN" => __("Tunisia"),
        "TR" => __("Turkey"),
        "TM" => __("Turkmenistan"),
        "TC" => __("Turks and Caicos Islands"),
        "TV" => __("Tuvalu"),
        "UG" => __("Uganda"),
        "UA" => __("Ukraine"),
        "AE" => __("United Arab Emirates"),
        "GB" => __("United Kingdom"),
        "US" => __("United States"),
        "UM" => __("United States Minor Outlying Islands"),
        "UY" => __("Uruguay"),
        "UZ" => __("Uzbekistan"),
        "VU" => __("Vanuatu"),
        "VE" => __("Venezuela"),
        "VN" => __("Vietnam"),
        "VG" => __("Virgin Islands (British)"),
        "VI" => __("Virgin Islands (U.S.)"),
        "WF" => __("Wallis and Futuna Islands"),
        "EH" => __("Western Sahara"),
        "YE" => __("Yemen"),
        "YU" => __("Yugoslavia"),
        "ZM" => __("Zambia"),
        "ZW" => __("Zimbabwe")
    ];

    if ($campaing) {
        $countries = ['all' => __('Worldwide Deal')] + $countries;
    }

    return $countries;
}
