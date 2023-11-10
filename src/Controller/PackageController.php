<?php

namespace App\Controller;

use App\Repository\PackageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PackageController extends AbstractController
{
    #[Route('/packages/{package}', name: 'app.packages.detail', methods: [Request::METHOD_GET])]
    public function getPackage(
        string            $package,
        PackageRepository $packageRepository,
    ): JsonResponse {
        return new JsonResponse($packageRepository->findBy([
            'name' => $package,
        ]));
    }

    #[Route('/packages/{package}/{version}', name: 'app.packages.version.detail', methods: [Request::METHOD_GET])]
    public function getPackageVersion(
        string $package,
        string $version,
        PackageRepository $packageRepository,
    ): JsonResponse {
        return new JsonResponse($packageRepository->findOneBy([
            'name' => $package,
            'version' => $version,
        ]));
    }

    #[Route('/packages', name: 'app.packages.list', methods: [Request::METHOD_GET])]
    public function getPackageNames(PackageRepository $packageRepository): JsonResponse
    {
        return new JsonResponse($packageRepository->getPackageNames());
    }
}
