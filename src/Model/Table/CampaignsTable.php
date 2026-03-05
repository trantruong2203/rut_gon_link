<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CampaignsTable extends Table
{

    public function initialize(array $config): void
    {
        $this->belongsTo('Users');
        $this->hasMany('CampaignItems', [
            'dependent' => true,
            'cascadeCallbacks' => true
        ]);
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator
            ->notEmpty('user_id', __('This value should not be blank.'))
            ->add('status', 'inList', [
                'rule' => ['inList', [1, 2, 3, 4, 5, 6, 7, 8]],
                'message' => __('Choose a valid value.')
            ])
            ->notEmpty('name', __('This value should not be blank.'))
            ->allowEmptyString('website_title')
            ->allowEmptyString('website_url')
            ->add('website_url', 'url', [
                'rule' => 'url',
                'message' => __('URL must be valid.')
            ])
            ->add('website_url', 'checkProtocol', [
                'rule' => function ($value, $context) {
                    if (empty($value)) return true;
                    $scheme = parse_url($value, PHP_URL_SCHEME);
                    return in_array($scheme, ['http', 'https']);
                },
                'message' => __('http and https urls only allowed.')
            ])
            /*
            ->add('website_url', 'checkXFrameOptions', [
                'rule' => function ($value, $context) {
                    $headers = get_http_headers( $value );
                    if ( isset( $headers[ "x-frame-options" ] ) ) {
                        return false;
                    }
                    return true;
                },
                'message' => __('This website URL refused to be used in interstitial ads.')
            ])
            */
            ->allowEmptyString('banner_name')
            ->allowEmptyString('banner_code')
            ->add('banner_size', 'inList', [
                'rule' => ['inList', ['728x90', '468x60', '336x280']],
                'message' => __('Choose a valid value.'),
                'allowEmpty' => true
            ])
            ->allowEmptyString('price')
            ->add('traffic_source', 'inList', [
                'rule' => ['inList', [1, 4, 5, 6]],
                'message' => __('Choose a valid value.'),
                'allowEmpty' => true
            ])
            ->allowEmptyString('website_terms')
            ->allowEmptyString('payment_method')
            ->add('verification_status', 'inList', [
                'rule' => ['inList', [0, 1, 2, 3]],
                'message' => __('Choose a valid value.'),
                'allowEmpty' => true
            ])
            ->allowEmptyString('countdown_seconds')
            ->allowEmptyString('daily_view_limit')
            ->allowEmptyString('total_view_limit')
            ->allowEmptyString('keyword_or_url')
            ->allowEmptyString('anchor_mode')
            ->allowEmptyString('anchor_text')
            ->allowEmptyString('anchor_link')
            ->allowEmptyString('discount_code')
            ->allowEmptyString('note')
            ->allowEmptyString('campaign_version')
            // Keyword SEO fields
            ->allowEmptyString('keyword_seo_code')
            ->allowEmptyString('keyword_seo_status')
            ->add('seo_target_views', 'numeric', [
                'rule' => 'numeric',
                'message' => __('Must be a number'),
                'allowEmpty' => true
            ])
            ->add('seo_current_views', 'numeric', [
                'rule' => 'numeric',
                'message' => __('Must be a number'),
                'allowEmpty' => true
            ])
            ->add('seo_wait_seconds', 'inList', [
                'rule' => ['inList', [60, 90, 120, 200]],
                'message' => __('Choose valid waiting time: 60, 90, 120, or 200 seconds'),
                'allowEmpty' => true
            ])
            ->allowEmptyString('seo_image_1')
            ->allowEmptyString('seo_image_2')
            ->add('seo_price_usd', 'numeric', [
                'rule' => 'numeric',
                'message' => __('Must be a valid price'),
                'allowEmpty' => true
            ]);

        return $validator;
    }

    public function isOwnedBy($id, $user_id)
    {
        return $this->exists(['id' => $id, 'user_id' => $user_id]);
    }
}
