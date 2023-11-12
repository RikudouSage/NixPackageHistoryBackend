<?php

namespace App\Entity;

use App\Repository\PackageRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\UniqueConstraint(fields: ['name', 'version'])]
#[ORM\Entity(repositoryClass: PackageRepository::class)]
#[ORM\Table(name: 'packages')]
class Package implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?DateTimeImmutable $datetime = null;

    public function __construct(
        #[ORM\Column(length: 180)]
        private string $name,
        #[ORM\Column(length: 180)]
        private string $version,
        #[ORM\Column(length: 180)]
        private string $revision,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getRevision(): string
    {
        return $this->revision;
    }

    public function setRevision(string $revision): static
    {
        $this->revision = $revision;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->getName(),
            'revision' => $this->getRevision(),
            'version' => $this->getVersion(),
        ];
    }

    public function getDatetime(): ?DateTimeImmutable
    {
        return $this->datetime;
    }

    public function setDatetime(DateTimeImmutable $datetime): static
    {
        $this->datetime = $datetime;

        return $this;
    }
}
