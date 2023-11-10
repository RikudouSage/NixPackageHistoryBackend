<?php

namespace App\Controller;

use App\Repository\PackageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;

final class PackageController extends AbstractController
{
    #[Route('/packages/{package}', name: 'app.packages.detail', methods: [Request::METHOD_GET])]
    public function getPackage(
        string            $package,
        PackageRepository $packageRepository,
        RateLimiterFactory $packageVersionsLimiter,
        Request $request,
    ): JsonResponse {
        $limiter = $packageVersionsLimiter->create($request->getClientIp());
        $limiter->consume()->ensureAccepted();

        return new JsonResponse($packageRepository->findBy([
            'name' => $package,
        ]));
    }

    #[Route('/packages/{package}/{version}', name: 'app.packages.version.detail', methods: [Request::METHOD_GET])]
    public function getPackageVersion(
        string $package,
        string $version,
        PackageRepository $packageRepository,
        Request $request,
        RateLimiterFactory $packageVersionDetailLimiter,
    ): JsonResponse {
        $limiter = $packageVersionDetailLimiter->create($request->getClientIp());
        $limiter->consume()->ensureAccepted();

        return new JsonResponse($packageRepository->findOneBy([
            'name' => $package,
            'version' => $version,
        ]));
    }

    #[Route('/packages', name: 'app.packages.list', methods: [Request::METHOD_GET])]
    public function getPackageNames(
        PackageRepository $packageRepository,
        RateLimiterFactory $allPackagesLimiter,
        Request $request,
    ): JsonResponse {
        $limiter = $allPackagesLimiter->create($request->getClientIp());
        $limiter->consume()->ensureAccepted();

        return new JsonResponse($packageRepository->getPackageNames());
    }
}
