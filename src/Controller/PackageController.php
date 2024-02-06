<?php

namespace App\Controller;

use App\Enum\NamedSetting;
use App\Repository\PackageRepository;
use App\Service\Settings;
use App\Service\Storage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
        Storage $storage,
        Settings $settings,
        #[Autowire('%app.storage.enable%')]
        bool $useStorage,
    ): RedirectResponse|JsonResponse {
        $limiter = $allPackagesLimiter->create($request->getClientIp());
        $limiter->consume()->ensureAccepted();

        if (!$useStorage) {
            return new JsonResponse($packageRepository->getPackageNames());
        }

        $key = "{$settings->getSetting(NamedSetting::LatestRevision)}/packages.json";
        if (!$storage->exists($key)) {
            $storage->createObject($key, json_encode($packageRepository->getPackageNames()), 'application/json');
        }

        return $this->redirect($storage->getObjectLink($key));
    }
}
