<?php

namespace Application\Configuration;

/**
 * Class Acl
 * @package Application\Configuration
 */
class Acl
{
    /**
     * @var array
     */
    private $configuration = [
        'acl' => [
            'routes' => [
                'public' => [
                    "GET" => [
                        [
                            "route" => "/login",
                            "allows" => [
                                "admin",
                                "standard",
                                "guest",
                            ],
                        ],
                    ],
                    "POST" => [],
                    "PUT" => [],
                    "PATCH" => [],
                    "DELETE" => [],
                ],
                "private" => [
                    "GET" => [
                        [
                            "route" => "/{resourceName}",
                            "allows" => [
                                "admin",
                                "standard",
                            ],
                        ],
                        [
                            "route" => "/{resourceName}/{identifier}",
                            "allows" => [
                                "admin",
                                "standard",
                            ],
                        ],
                    ],
                    "POST" => [
                        [
                            "route" => "/{resourceName}",
                            "allows" => [
                                "admin",
                                "standard",
                            ],
                        ],
                    ],
                    "PUT" => [
                        [
                            "route" => "/{resourceName}",
                            "allows" => [
                                "admin",
                                "standard",
                            ],
                        ],
                        [
                            "route" => "/{resourceName}/{identifier}",
                            "allows" => [
                                "admin",
                                "standard",
                            ],
                        ],
                    ],
                    "PATCH" => [
                        [
                            "route" => "/{resourceName}/{identifier}",
                            "allows" => [
                                "admin",
                                "standard",
                            ],
                        ],
                    ],
                    "DELETE" => [
                        [
                            "route" => "/{resourceName}/{identifier}",
                            "allows" => [
                                "admin",
                                "standard",
                            ],
                        ],
                    ],
                ],
            ],
            "roles" => [
                "admin" => [
                    "permissions" => [],
                ],
                "standard" => [
                    "permissions" => [],
                ],
                "guest" => [
                    "permissions" => [],
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
