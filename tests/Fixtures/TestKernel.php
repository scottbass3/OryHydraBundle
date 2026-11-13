<?php

namespace Scottbass3\OryHydraBundle\Tests\Fixtures;

use Scottbass3\OryHydraBundle\Scottbass3OryHydraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @param array<string, mixed> $bundleConfig
     * @param array<string, mixed> $httpClientConfig
     */
    public function __construct(
        private readonly array $bundleConfig,
        private readonly array $httpClientConfig = [],
    ) {
        parent::__construct('test', false);
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new Scottbass3OryHydraBundle();
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/scottbass3_ory_hydra_bundle/'.md5(serialize([$this->bundleConfig, $this->httpClientConfig]));
    }

    public function getLogDir(): string
    {
        return $this->getCacheDir().'/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'http_method_override' => false,
            'http_client' => ['mock_response_factory' => MockResponseFactory::class] + $this->httpClientConfig,
        ]);
        $container->extension('scottbass3_ory_hydra', $this->bundleConfig);

        $container->services()->set(MockResponseFactory::class)->public();
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PublicServicesPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
