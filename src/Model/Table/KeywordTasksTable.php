<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class KeywordTasksTable extends Table
{
    public function initialize(array $config): void
    {
        $this->belongsTo('Campaigns');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator
            ->requirePresence('keyword')
            ->notEmpty('keyword', __('Keyword is required.'))
            ->requirePresence('target_url')
            ->notEmpty('target_url', __('Target URL is required.'));

        return $validator;
    }
}
