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

        // /** @var PathItem $path */
        // foreach ($openApi->getPaths()->getPaths() as $key => $path) {
        //     if ($path->getPatch() && $path->getPatch()->getSummary() === 'hidden') {
        //         $openApi->getPaths()->addPath($key, $path->withPatch(null)); // hidding in documentation paths with summary = "hidden"
        //     }
        //     if ($path->getGet() && $path->getGet()->getSummary() === 'hidden') {
        //         $openApi->getPaths()->addPath($key, $path->withGet(null));
        //     }
        // }


        // Building Login JWT
        // $schemas = $openApi->getComponents()->getSecuritySchemes();
        // $schemas['bearerAuth'] = new \ArrayObject([ // Stateless login
        //     'type' => 'http',
        //     'scheme' => 'bearer',
        //     'bearerFormat' => 'JWT'
        // ]);

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
                summary: 'Retrieves JSON Web Token & refresh token from Credentials.',
                responses: [
                    '200' => [
                        'description' => 'JSON Web Token & refresh token.',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token'
                                ]
                            ]
                        ]
                    ]
                ],
                security: []
            )
        );
        $openApi->getPaths()->addPath('/api/authenticate', $loginPathItem);

        // // Another example for Who Am I (requiring useless id)
        // $whoAmIOperation = $openApi->getPaths()->getPath('/api/whoami')->getGet()->withParameters([]);
        // $whoAmIPathItem = $openApi->getPaths()->getPath('/api/whoami')->withGet($whoAmIOperation);
        // $openApi->getPaths()->addPath('/api/whoami', $whoAmIPathItem);

        return $openApi;
    }
}
