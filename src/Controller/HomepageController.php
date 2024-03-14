<?php

namespace App\Controller;

use App\Entity\Package;
use App\Enum\NamedSetting;
use App\Repository\PackageRepository;
use App\Service\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class HomepageController extends AbstractController
{
    #[Route('/', name: 'app.index', methods: [Request::METHOD_GET])]
    public function index(UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        return new JsonResponse([
            'endpoints' => [
                $urlGenerator->generate('app.packages.list', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
                $urlGenerator->generate('app.packages.detail', ['package' => 'php'], referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
                $urlGenerator->generate('app.packages.version.detail', ['package' => 'php', 'version' => '8.2.11'], referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
                $urlGenerator->generate('app.tags', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
            ],
        ]);
    }

    #[Route('/latest-revision', name: 'app.latest_revision', methods: [Request::METHOD_GET])]
    public function lastRevision(Settings $settings): JsonResponse
    {
        return new JsonResponse([
            'revision' => $settings->getSetting(NamedSetting::LatestRevision),
            'datetime' => $settings->getSetting(NamedSetting::LatestRevisionDatetime),
        ]);
    }
}
