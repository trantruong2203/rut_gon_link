<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Cache\Cache;

class ActivationTable extends Table
{

    public function initialize(array $config): void
    {
        $this->_table = false;
    }

    public function checkLicense()
    {
        return true;
    }

    public function validateLicense()
    {

        if (($result = Cache::read('license_response_result', '1week')) === false) {

            $personal_token = get_option('personal_token');
            $purchase_code = get_option('purchase_code');

            $result = $this->licenseCurlRequest([
                'personal_token' => $personal_token,
                'purchase_code' => $purchase_code
            ]);

            Cache::write('license_response_result', $result, '1week');
        }

        if (isset($result['item']['id']) && $result['item']['id'] == 16887109) {
            return true;
        }

        return false;
    }

    public function licenseCurlRequest($data = [])
    {
        $reponse = curlRequest('https://api.envato.com/v3/market/buyer/purchase', 'GET', [
            'code' => trim($data['purchase_code'])
        ], ['Authorization: Bearer ' . trim($data['personal_token'])]);

        return json_decode($reponse, true);
    }
}
