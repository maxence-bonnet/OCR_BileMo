<?php 

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\Model\RequestBody;
use ApiPlatform\Core\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private OpenApiFactoryInterface $decorated) {}

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        # Custom Schemas

        $schemas = $openApi->getComponents()->getSchemas();
        $schemas['Credentials'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'username' => [
                    'type' => 'string',
                    'example' => 'max.test@mail.com'
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'azerty'
                ]
            ]
        ]);

        $schemas['Token'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true
                ],
                'refresh_token' => [
                    'type' => 'string',
                ],
            ]
        ]);
       
        $schemas['Refresh_Token'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'refresh_token' => [
                    'type' => 'string'
                ],
            ]
        ]);

        # Custom Paths

        $loginPathItem = new PathItem(
            post: new Operation(
                operationId: 'postCredentials',
                tags: ['Authentication'],
                requestBody: new RequestBody(
                    description: 'Your Credentials',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials'
                            ]
                        ]
                    ])
                ),
                summary: 'Retrieves JSON Web Token & Refresh Token from Credentials.',
                responses: [
                    '200' => [
                        'description' => 'JSON Web Token & Refresh Token.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token'
                                ]
                            ]
                        ]
                    ],
                    '401' => [
                        'description' => 'Invalid Credentials',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'code' => [
                                            'type' => 'int',
                                            'example' => 401
                                        ],
                                        'message' => [
                                            'type' => 'string',
                                            'example' => 'Invalid credentials.'
                                        ],                                        
                                    ]
                                ]
                            ]
                        ]                        
                    ],
                ],
                security: []
            )
        );
        $openApi->getPaths()->addPath('/api/authenticate', $loginPathItem);

        $refreshTokenPathItem = new PathItem(
            post: new Operation(
                operationId: 'postRefreshToken',
                tags: ['Authentication'],
                requestBody: new RequestBody(
                    description: 'Your Refresh Token',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Refresh_Token'
                            ]
                        ]
                    ])
                ),
                summary: 'Retrieves new JSON Web Token & new Refresh Token from previous Refresh Token.',
                responses: [
                    '200' => [
                        'description' => 'new JSON Web Token & new Refresh Token.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token'
                                ]
                            ]
                        ]
                    ],
                    '401' => [
                        'description' => 'No "refresh_token" field specified / Invalid or Expired refresh_token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'code' => [
                                            'type' => 'int',
                                            'example' => 401
                                        ],
                                        'message' => [
                                            'type' => 'string',
                                            'example' => 'Invalid JWT Refresh Token'
                                        ],                                        
                                    ]
                                ]
                            ]
                        ]                        
                    ],
                ],
                security: []
            )
        );
        $openApi->getPaths()->addPath('/api/token/refresh', $refreshTokenPathItem);


        # Path editing (requiring useless id by default)
        $meOperation = $openApi->getPaths()->getPath('/api/me')->getGet()->withParameters([]);
        $mePathItem = $openApi->getPaths()->getPath('/api/me')->withGet($meOperation);
        $openApi->getPaths()->addPath('/api/me', $mePathItem);

        return $openApi;
    }
}
