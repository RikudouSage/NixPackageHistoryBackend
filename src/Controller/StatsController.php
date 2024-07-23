<?php

namespace App\Controller;

use App\Enum\NamedSetting;
use App\Repository\PackageRepository;
use App\Service\Settings;
use App\Service\Storage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class StatsController extends AbstractController
{
    #[Route('/stats', name: 'app.stats')]
    public function stats(
        PackageRepository $packageRepository,
        #[Autowire('%app.storage.enable%')]
        bool $useStorage,
        Storage $storage,
        Settings $settings,
    ): Response {
        $fresh = fn () => [
            'packages' => $packageRepository->getPackageCount(),
            'versions' => $packageRepository->getVersionCount(),
        ];

        if (!$useStorage) {
            return new JsonResponse($fresh());
        }

        $key = "{$settings->getSetting(NamedSetting::LatestRevision)}/stats.json";
        if (!$storage->exists($key)) {
            $storage->createObject($key, json_encode($fresh()), 'application/json');
        }

        return $this->redirect($storage->getObjectLink($key));
    }
}
