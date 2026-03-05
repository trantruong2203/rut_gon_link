<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CampaignItemsTable extends Table
{

    public function initialize(array $config): void
    {
        $this->belongsTo('Campaigns');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator
            ->allowEmpty('purchase')
            ->allowEmpty('publisher_price')
            ->allowEmpty('advertiser_price')
            ->naturalNumber('purchase', __('Write a valid natural number.'));

        return $validator;
    }
}
