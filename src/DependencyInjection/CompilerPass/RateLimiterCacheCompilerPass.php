<?php

namespace App\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final readonly class RateLimiterCacheCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $service = $_ENV['RATE_LIMIT_CACHE_SERVICE'] ?? 'cache.app';
        $container->setAlias('cache.rate_limiter', $service);
    }
}
