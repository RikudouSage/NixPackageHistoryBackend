<?php

namespace App\Service;

use App\Entity\Setting;
use App\Enum\NamedSetting;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class Settings
{
    public function __construct(
        private SettingRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getSetting(string|NamedSetting $name, mixed $default = null): mixed
    {
        if ($name instanceof NamedSetting) {
            $name = $name->value;
        }

        return $this->repository->findOneBy([
            'name' => $name,
        ])?->getValue() ?? $default;
    }

    public function setSetting(string|NamedSetting $name, mixed $value): void
    {
        if ($name instanceof NamedSetting) {
            $name = $name->value;
        }

        $entity = $this->repository->findOneBy([
            'name' => $name,
        ]) ?? (new Setting())->setName($name);

        $entity->setValue($value);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
