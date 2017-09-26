<?php

namespace Application\Configuration;

/**
 * Class Models
 * @package Application\Configuration
 */
class Models
{
    /**
     * @var array
     */
    private $configuration = [
        'models' => [
            'User' => [
                'collection' => 'users',
                'authenticatable' => true,
                'authStrategy' => 'password',
                'credentials' => [
                    'email',
                    'password',
                ],
                'fields' => [
                    '_id' => [
                        'label' => 'ID',
                        'type' => 'string',
                        'disabled' => true,
                    ],
                    'name' => [
                        'label' => 'Name',
                        'type' => 'string',
                    ],
                    'email' => [
                        'label' => 'Email',
                        'type' => 'string',
                    ],
                    'password' => [
                        'label' => 'Password',
                        'type' => 'password',
                    ],
                    'newPassword' => [
                        'label' => 'New Password',
                        'type' => 'password',
                    ],
                    'repeatNewPassword' => [
                        'label' => 'Repeat New Password',
                        'type' => 'password',
                    ],
                    'token' => [
                        'label' => 'Token',
                        'type' => 'string',
                        'disabled' => true,
                    ],
                    'active' => [
                        'label' => 'Is Active',
                        'type' => 'boolean',
                    ],
                    'customField' => [
                        'label' => 'Custom options field',
                        'type' => 'array',
                        'options' => [
                            'option1' => 'Option 1',
                            'option2' => 'Option 2',
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
