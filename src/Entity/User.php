<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\UserRolesController;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    // security: 'is_granted("ROLE_CLIENT_ADMIN")',
    formats: ['jsonld', 'jsonhal', 'json', 'xml'],
    paginationItemsPerPage: 10,
    paginationMaximumItemsPerPage: 20,
    paginationClientItemsPerPage: true, 
    denormalizationContext: [
        'groups' => ['write:User'],
    ],
    normalizationContext: [
        'groups' => ['read:User:collection'],
    ],
    collectionOperations: [
        'get' => [

        ],
        'post' => [
            // 'security' => 'is_granted("ROLE_CLIENT_ADMIN")',
        ],
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => [
                'groups' => ['read:User:item']
            ]
        ],
        'upgrade' => [
            // 'security' => 'is_granted("ROLE_SUPER_ADMIN")',
            'path' => '/users/{id}/upgrade',
            'controller' => UserRolesController::class, 
            'method' => 'POST',
            'write' => false,
            'openapi_context' => [
                'security' => [
                    ['bearerAuth' => []]
                ],
                'summary' => 'Upgrade user role',
                'description' => 'Upgrade user role',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [],
                            'example' => '{}'
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Upgraded user',
                        'content' => [
                            //
                        ]
                    ]
                ]
            ]
        ],
        'downgrade' => [
            // 'security' => 'is_granted("ROLE_SUPER_ADMIN")',
            'path' => '/users/{id}/downgrade',
            'controller' => UserRolesController::class, 
            'method' => 'POST',
            'write' => false,
            'openapi_context' => [
                'security' => [
                    ['bearerAuth' => []]
                ],
                'summary' => 'Downgrade user role',
                'description' => 'Downgrade user role',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [],
                            'example' => '{}'
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Downgraded user',
                        'content' => [
                            //
                        ]
                    ]
                ]
            ]
        ],
        'delete' => [
        ],
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['client' => 'exact'])] // 
class User implements UserInterface, PasswordAuthenticatedUserInterface, JWTUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:User:item'])]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(['read:User:collection', 'write:User'])]
    private $email;

    #[ORM\Column(type: 'json')]
    #[Groups(['read:User:item'])]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    #[Groups(['write:User'])]
    private $password;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'users')]
    #[Groups(['read:User:collection', 'write:User'])]
    private $client;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    // Customize JWT
    public static function createFromPayload($username, array $payload)
    {
        return (new self())->setEmail($username)->setId($payload['id'] ?? null);
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {

    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
