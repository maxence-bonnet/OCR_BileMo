<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BrandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;

#[ORM\Entity(repositoryClass: BrandRepository::class)]
// #[ApiResource(
//     paginationItemsPerPage: 10,
//     paginationMaximumItemsPerPage: 20,
//     paginationClientItemsPerPage: true,
//     normalizationContext: ['groups' => ['read:Brand:collection']],
//     itemOperations: [
//         'get' => [
//             'normalization_context' => ['groups' => ['read:Brand:item']]
//         ]
//     ],
//     collectionOperations: [
//         'get'
//     ]
// )]
class Brand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:Phone:item', 'read:Brand:item'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[
        Groups(['read:Phone:collection', 'write:Phone', 'read:Brand:collection', 'read:Brand:item']),
        Length(min: 2, max: 30, minMessage: 'Brand name is too short', maxMessage: 'Brand name is too long', groups: ['create:Phone'])
    ]
    private $name;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['read:Phone:item', 'read:Brand:collection', 'read:Brand:item'])]
    private $createdAt;

    #[ORM\OneToMany(mappedBy: 'brand', targetEntity: Phone::class, orphanRemoval: true)]
    private $phones;

    public function __construct()
    {
        $this->phones = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|Phone[]
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
            $phone->setBrand($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            // set the owning side to null (unless already changed)
            if ($phone->getBrand() === $this) {
                $phone->setBrand(null);
            }
        }

        return $this;
    }
}
