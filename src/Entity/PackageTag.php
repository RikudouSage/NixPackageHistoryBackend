<?php

namespace App\Entity;

use App\Repository\PackageTagRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackageTagRepository::class)]
#[ORM\Table(name: 'tags')]
class PackageTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $tag = null;

    #[ORM\Column]
    private array $packageNames = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getPackageNames(): array
    {
        return $this->packageNames;
    }

    public function setPackageNames(array $packageNames): static
    {
        $this->packageNames = $packageNames;

        return $this;
    }
}
