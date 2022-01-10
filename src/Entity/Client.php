<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ApiResource(
    formats: ['jsonld', 'jsonhal', 'json', 'xml'],
    paginationItemsPerPage: 10,
    paginationMaximumItemsPerPage: 20,
    paginationClientItemsPerPage: true, 
    denormalizationContext: [
        'groups' => ['write:Client'],
    ],
    normalizationContext: [
        'groups' => ['read:Client:collection'],
    ],
    collectionOperations: [
        'get' => [
        ],
        'post' => [
            // 'security' => 'is_granted("ROLE_SUPER_ADMIN")',
        ],
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => [
                'groups' => ['read:Client:collection', 'read:Client:item']
            ]
        ],
        'delete' => [

        ],
    ]
)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:Client:item'])]
    private $id;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: User::class)]
    #[Groups(['read:Client:item'])]
    private $users;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read:Client:collection', 'write:Client', 'read:User:item'])]
    private $name;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['read:Client:collection', 'read:User:item'])]
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setClient($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getClient() === $this) {
                $user->setClient(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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
}
