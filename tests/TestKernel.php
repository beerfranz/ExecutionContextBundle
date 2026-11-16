<?php

namespace Beerfranz\ExecutionContext\Tests;

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Beerfranz\ExecutionContext\BeerfranzExecutionContextBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Zenstruck\Messenger\Test\ZenstruckMessengerTestBundle;

class TestKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new SecurityBundle(),
            new MonologBundle(),
            new ZenstruckMessengerTestBundle(),
            new BeerfranzExecutionContextBundle(),
            new ApiPlatformBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config.yml');
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/var/cache';
    }

    public function getLogDir(): string
    {
        return __DIR__.'/var/log';
    }
}
