<?php

namespace App\Controller;

use App\Repository\PackageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class StatsController extends AbstractController
{
    #[Route('/stats', name: 'app.stats')]
    public function stats(PackageRepository $packageRepository): JsonResponse
    {
        return new JsonResponse([
            'packages' => $packageRepository->getPackageCount(),
            'versions' => $packageRepository->getVersionCount(),
        ]);
    }
}
