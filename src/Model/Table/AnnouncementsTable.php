<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class AnnouncementsTable extends Table
{

    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
        $this->addBehavior('Translate', ['fields' => ['title', 'content']]);
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator
            ->notEmpty('title')
            ->add('published', 'inList', [
                'rule' => ['inList', ['0', '1']],
                'message' => __('Choose a valid value.')
            ])
            ->allowEmpty('content');

        return $validator;
    }
}
