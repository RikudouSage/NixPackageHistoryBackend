<?php

namespace App\Controller;

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
            ],
        ]);
    }
}
