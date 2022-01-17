<?php

namespace App\Entity;

use App\Entity\Brand;
use ApiPlatform\Core\Action\NotFoundAction;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\PhoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PhoneRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['brand' => 'exact'])] 
#[ApiFilter(OrderFilter::class, properties: ['realesedAt' => 'DESC'])]
#[UniqueEntity('model', message: 'Le modèle {{ value }} existe déjà', groups: ['write:Phone'])]
class Phone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'string', length: 255)]
    #[
        NotBlank(message: 'Phone model can not be null or blank', groups: ['replace:Phone', 'write:Phone']),
        Length(min: 1, max: 25, maxMessage: 'Phone model is too long', groups: ['replace:Phone', 'write:Phone'])
    ]
    private string $model;

    #[ORM\Column(type: 'datetime_immutable')]
    #[
        ApiProperty(
            openapiContext: [
                'description' => 'Release date of the phone',
            ],
        )
    ]
    private \DateTimeImmutable $releasedAt;

    #[ORM\Column(type: 'float')]
    #[
        GreaterThan(value: 99, message: 'Phone weight seems too low', groups: ['replace:Phone', 'write:Phone']),
        LessThan(value: 1001, message: 'Phone weight seems too high', groups: ['replace:Phone', 'write:Phone']),
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
        GreaterThan(value: 15, message: 'Phone is too cheap', groups: ['update:Phone', 'replace:Phone', 'write:Phone']),
        LessThan(value: 10000, message: 'Phone is too expensive', groups: ['update:Phone', 'replace:Phone', 'write:Phone']),
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
        Valid(groups: ['replace:Phone', 'write:Phone']),
    ]
    private Brand $brand;

    #[ORM\ManyToMany(targetEntity: Client::class, mappedBy: 'phonesList')]
    #[
        ApiProperty(
            openapiContext: [
                'type' => Client::class,
                'description' => 'List of Clients having the phone saved in their list',
                'example' => ['/api/clients/1']
            ],
        )
    ]
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
