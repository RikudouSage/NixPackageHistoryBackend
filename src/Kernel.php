<?php

namespace App;

use App\DependencyInjection\CompilerPass\RateLimiterCacheCompilerPass;
use Bref\SymfonyBridge\BrefKernel;
use Override;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Kernel extends BrefKernel
{
    use MicroKernelTrait;

    #[Override]
    public function getBuildDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    #[Override]
    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RateLimiterCacheCompilerPass());
    }
}
