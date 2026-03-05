<?php

namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Http\Session;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Auth\DefaultPasswordHasher;

class UsersTable extends Table
{

    public function initialize(array $config): void
    {
        $this->hasMany('Campaigns');
        $this->hasMany('Links');
        $this->hasMany('Statistics');
        $this->hasMany('Withdraws');
        $this->addBehavior('Timestamp');

        $this->hasMany('ADmad/SocialAuth.SocialProfiles');
    }

    public function findAuth(\Cake\ORM\Query $query, array $options)
    {
        $user_status = 1;
        if (version_compare(get_option('app_version'), '3.0.0', '<')) {
            $user_status = 'active';
        }
        $query->where([
            'OR' => [
                'Users.username' => $options['username'],
                'Users.email' => $options['username']
            ],
            'Users.status' => $user_status
        ]);

        return $query;
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        return $validator
                ->notEmpty('username', 'A username is required')
                ->add('username', [
                    'alphaNumeric' => [
                        'rule' => ['alphaNumeric'],
                        'message' => __('alphaNumeric Only')
                    ],
                    'minLength' => [
                        'rule' => ['minLength', 5],
                        'message' => __('Minimum Length 5')
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', 255],
                        'message' => __('Maximum Length 255')
                    ]
                ])
                ->add('username', 'checkReserved', [
                    'rule' => function ($value, $context) {

                        $reserved_domains = explode(',', get_option('reserved_usernames'));
                        $reserved_domains = array_map('trim', $reserved_domains);
                        $reserved_domains = array_filter($reserved_domains);

                        if (in_array(strtolower($value), $reserved_domains)) {
                            return false;
                        }
                        return true;
                    },
                    'message' => __('This username is a reserved word.')
                ])
                ->add('username', [
                    'unique' => [
                        'rule' => 'validateUnique',
                        'provider' => 'table',
                        'message' => __('Username already exists')
                    ]
                ])
                ->add('username_compare', [
                    'compare' => [
                        'rule' => ['compareWith', 'username'],
                        'message' => __('Not the same')
                    ]
                ])
                ->notEmpty('password', 'A password is required')
                ->add('password', [
                    'minLength' => [
                        'rule' => ['minLength', 5],
                        'message' => __('Minimum Length 5')
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', 25],
                        'message' => __('Maximum Length 25')
                    ]
                ])
                ->add('password_compare', [
                    'compare' => [
                        'rule' => ['compareWith', 'password'],
                        'message' => __('Not the same')
                    ]
                ])
                ->notEmpty('email', 'An email is required')
                ->add('email', 'validFormat', [
                    'rule' => 'email',
                    'message' => __('E-mail must be valid')
                ])
                ->add('email', [
                    'unique' => [
                        'rule' => 'validateUnique',
                        'provider' => 'table',
                        'message' => __('E-mail must be unique')
                    ]
                ])
                ->add('email_compare', [
                    'compare' => [
                        'rule' => ['compareWith', 'email'],
                        'message' => __('Not the same')
                    ]
                ])
                ->notEmpty('first_name', __('This field should not be blank.'))
                ->notEmpty('last_name', __('This field should not be blank.'))
                ->notEmpty('address1', __('This field should not be blank.'))
                ->notEmpty('city', __('This field should not be blank.'))
                ->notEmpty('state', __('This field should not be blank.'))
                ->notEmpty('zip', __('This field should not be blank.'))
                ->notEmpty('country', __('This field should not be blank.'))
                ->notEmpty('phone_number', __('This field should not be blank.'))
                ->notEmpty('withdrawal_method', __('This field should not be blank.'))
                ->add('withdrawal_method', 'inList', [
                    'rule' => ['inList', ['paypal', 'payza', 'skrill', 'coinbase',
                        'webmoney', 'banktransfer', 'wallet']],
                    'message' => __('Choose a valid value.')
                ])
                ->notEmpty('withdrawal_account', __('This field should not be blank.'), function ($context) {
                    return !($context['data']['withdrawal_method'] === 'wallet');
                });
    }

    public function validationChangeEmail(Validator $validator)
    {
        //$validator = $this->validateDefault($validator);
        return $validator
                ->notEmpty('temp_email', 'An email is required')
                ->add('temp_email', 'validFormat', [
                    'rule' => 'email',
                    'message' => __('E-mail must be valid')
                ])
                ->add('temp_email', 'custom', [
                    'rule' => function ($value, $context) {
                        $count = $this->find('all')
                            ->where(['email' => $value])
                            ->count();
                        if ($count > 0) {
                            return false;
                        } else {
                            return true;
                        }
                    },
                    'message' => __('E-mail must be unique')
                ])
                ->add('confirm_email', [
                    'compare' => [
                        'rule' => ['compareWith', 'temp_email'],
                        'message' => __('Not the same')
                    ]
        ]);
    }

    public function validationChangePassword(Validator $validator)
    {
        //$validator = $this->validateDefault($validator);
        return $validator
                ->notEmpty('current_password', 'Please enter current password.')
                ->add('current_password', 'custom', [
                    'rule' => function ($value, $context) {
                        $user = $this->findById($context['data']['id'])->first();
                        return (new DefaultPasswordHasher)->check($value, $user->password);
                    },
                    'message' => __('Please enter current password.')
                ])
                ->notEmpty('password', 'A password is required')
                ->add('password', [
                    'minLength' => [
                        'rule' => ['minLength', 5],
                        'message' => __('Minimum Length 5')
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', 25],
                        'message' => __('Maximum Length 25')
                    ]
                ])
                ->add('confirm_password', [
                    'compare' => [
                        'rule' => ['compareWith', 'password'],
                        'message' => __('Not the same')
                    ]
        ]);
    }

    public function validationForgotPassword(Validator $validator)
    {
        //$validator = $this->validateDefault($validator);
        return $validator
                ->notEmpty('email', 'An email is required')
                ->add('email', 'validFormat', [
                    'rule' => 'email',
                    'message' => __('E-mail must be valid')
                ])
                ->notEmpty('password', 'A password is required')
                ->add('password', [
                    'minLength' => [
                        'rule' => ['minLength', 5],
                        'message' => __('Minimum Length 5')
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', 25],
                        'message' => __('Maximum Length 25')
                    ]
                ])
                ->add('confirm_password', [
                    'compare' => [
                        'rule' => ['compareWith', 'password'],
                        'message' => __('Not the same')
                    ]
        ]);
    }

    /**
     * SocialAuth callback for creating/finding user from social profile.
     *
     * @param \Cake\Datasource\EntityInterface $profile Social profile entity
     * @param \Cake\Http\Session $session Session instance
     * @return \Cake\Datasource\EntityInterface User entity
     */
    public function getUser(EntityInterface $profile, Session $session): EntityInterface
    {
        if (empty($profile->email)) {
            throw new \RuntimeException(__('Could not find email in social profile.'));
        }

        // Check if user with same email exists
        $user = $this->find()->where(['email' => $profile->email])->first();
        if ($user) {
            return $user;
        }

        $referred_by_id = 0;
        if (isset($_COOKIE['ref'])) {
            $user_referred_by = $this->find()
                ->where(['username' => $_COOKIE['ref'], 'status' => 1])
                ->first();
            if ($user_referred_by) {
                $referred_by_id = $user_referred_by->id;
            }
        }

        $username = $profile->identifier ?? $profile->username ?? 'user_' . uniqid();
        $user = $this->newEntity([
            'status' => 1,
            'username' => $username,
            'password' => generate_random_string(10),
            'role' => 'member',
            'email' => $profile->email,
            'referred_by' => $referred_by_id,
            'api_token' => \Cake\Utility\Security::hash(\Cake\Utility\Text::uuid(), 'sha1', true),
            'first_name' => $profile->first_name ?? '',
            'last_name' => $profile->last_name ?? '',
        ]);

        if (!$this->save($user)) {
            throw new \RuntimeException(__('Unable to save new user'));
        }

        return $user;
    }
}
