<?php

namespace App\Controller;

use App\Enum\NamedSetting;
use App\Repository\PackageRepository;
use App\Repository\PackageTagRepository;
use App\Service\Settings;
use App\Service\Storage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;

final class TagController extends AbstractController
{
    #[Route('/tags', name: 'app.tags', methods: [Request::METHOD_GET])]
    public function getTags(
        PackageTagRepository $repository,
        RateLimiterFactory $tagsLimiter,
        Request $request,
        Storage $storage,
        Settings $settings,
        #[Autowire('%app.storage.enable%')]
        bool $useStorage,
    ): JsonResponse|RedirectResponse {
        $limiter = $tagsLimiter->create($request->getClientIp());
        $limiter->consume()->ensureAccepted();

        if (!$useStorage) {
            return new JsonResponse($repository->findAll());
        }

        $key = "{$settings->getSetting(NamedSetting::LatestRevision)}/tags.json";
        if (!$storage->exists($key)) {
            $storage->createObject($key, json_encode($repository->findAll()), 'application/json');
        }

        return $this->redirect($storage->getObjectLink($key));
    }

    #[Route('/tags/{tag}', name: 'app.tags.detail', methods: [Request::METHOD_GET])]
    public function getTag(
        string $tag,
        PackageTagRepository $tagRepository,
        Request $request,
        RateLimiterFactory $tagDetailLimiter,
    ): JsonResponse {
        $limiter = $tagDetailLimiter->create($request->getClientIp());
        $limiter->consume()->ensureAccepted();

        return new JsonResponse($tagRepository->findOneBy([
            'tag' => $tag,
        ]));
    }
}
