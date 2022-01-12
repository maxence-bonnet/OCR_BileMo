<?php

namespace App\Entity;

use App\Entity\Brand;
use ApiPlatform\Core\Action\NotFoundAction;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\PhoneRepository;
use App\Controller\PhoneCountController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

#[ORM\Entity(repositoryClass: PhoneRepository::class)]
#[ApiResource(
    // security: 'is_granted("ROLE_USER")',
    formats: ['jsonld', 'jsonhal', 'json', 'xml'],
    attributes: [
        'validation_groups' => ['create:Phone'],
    ],
    paginationItemsPerPage: 10,
    paginationMaximumItemsPerPage: 20,
    paginationClientItemsPerPage: true,
    normalizationContext: [
        'groups' => ['read:Phone:collection'],
        'openapi_definition_name' => 'Collection'
    ], 
    denormalizationContext: [
        'groups' => ['write:Phone'],
        'openapi_definition_name' => 'Write'
    ],
    itemOperations: [ 
        'delete' => [
            // 'security' => 'is_granted("ROLE_SUPER_ADMIN")'
        ],  
        'get' => [
            'normalization_context' => [
                'groups' => ['read:Phone:collection', 'read:Phone:item'],
                'openapi_definition_name' => 'Item'
            ] 
        ],
        'put' => [
            // 'security' => 'is_granted("ROLE_SUPER_ADMIN")',
            'validation_groups' => ['update:Phone']
        ],
        'patch' => [
            // 'security' => 'is_granted("ROLE_SUPER_ADMIN")',
            'validation_groups' => ['update:Phone']
        ],
    ],
    collectionOperations: [
        'get',
        'post' => [
            'security' => 'is_granted("ROLE_SUPER_ADMIN")',
            'validation_groups' => ['write:Phone']
        ],
        'count' => [
            'method' => 'GET',
            'path' => '/phones/count',
            'controller' => PhoneCountController::class,
            'filters' => [],
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Returns total number of phones',
                'description' => 'Returns total number of phones',
                'parameters' => [
                    [
                        'in' => 'query',
                        'name' => 'brand',
                        'example' => 'Wiplo',
                        'description' => 'Filters depending on given brand id or name'                        
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Number of phones',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'integer',
                                    'example' => 2
                                ]
                            ]
                        ]
                    ]
                ]

            ]
        ],
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['brand' => 'exact'])] 
#[ApiFilter(OrderFilter::class, properties: ['realesedAt' => 'DESC'])]
class Phone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['read:Phone:item'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'string', length: 255)]
    #[
        Groups(['read:Phone:collection', 'read:Brand:item', 'write:Phone']),
        NotBlank(message: 'Phone model can not be null or blank', groups: ['create:Phone', 'update:Phone']),
        Length(min: 1, max: 25, maxMessage: 'Phone model is too long', groups: ['create:Phone', 'update:Phone'])
    ]
    private string $model;

    #[ORM\Column(type: 'datetime_immutable')]
    #[
        Groups(['read:Phone:collection', 'read:Phone:item', 'write:Phone']),
        ApiProperty(
            openapiContext: [
                'description' => 'Release date of the phone',
            ],
        )
    ]
    private \DateTimeImmutable $releasedAt;

    #[ORM\Column(type: 'float')]
    #[
        Groups(['read:Phone:collection', 'write:Phone']),
        GreaterThan(value: 99, message: 'Phone weight seems too low', groups: ['create:Phone', 'update:Phone']),
        LessThan(value: 1001, message: 'Phone weight seems too high', groups: ['create:Phone', 'update:Phone']),
        ApiProperty(
            openapiContext: [
                'description' => 'Weight of the phone [g]',
                'example' => 100
            ],
        )
    ]
    private float $weight;

    #[ORM\Column(type: 'float')]
    #[
        Groups(['read:Phone:collection', 'write:Phone']),
        GreaterThan(value: 15, message: 'Phone is too cheap', groups: ['create:Phone', 'update:Phone']),
        LessThan(value: 10000, message: 'Phone wis too expensive', groups: ['create:Phone', 'update:Phone']),
        ApiProperty(
            openapiContext: [
                'description' => 'Price of the phone [EUR]',
                'example' => 100
            ],
        )
    ]
    private float $price;

    #[ORM\ManyToOne(targetEntity: Brand::class, inversedBy: 'phones', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[
        Groups(['read:Phone:collection', 'write:Phone']),
        Valid(),
        ApiProperty(
            openapiContext: [
                'type' => Brand::class,
                'description' => 'Brand of the phone',
                'example' => "WoopWoop"
            ],
        )
    ]
    private Brand $brand;

    #[ORM\ManyToMany(targetEntity: Client::class, mappedBy: 'phonesList')]
    private $clients;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->clients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getReleasedAt(): ?\DateTimeImmutable
    {
        return $this->releasedAt;
    }

    public function setReleasedAt(\DateTimeImmutable $releasedAt): self
    {
        $this->releasedAt = $releasedAt;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return Collection|Client[]
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->addPhonesList($this);
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->removeElement($client)) {
            $client->removePhonesList($this);
        }

        return $this;
    }
}
