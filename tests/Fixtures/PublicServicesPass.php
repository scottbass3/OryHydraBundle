<?php

namespace Scottbass3\OryHydraBundle\Tests\Fixtures;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Keeps the bundle services and aliases reachable from tests, as nothing in the test app uses them.
 */
final class PublicServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if (str_starts_with($id, 'scottbass3_ory_hydra.')) {
                $definition->setPublic(true);
            }
        }

        foreach ($container->getAliases() as $id => $alias) {
            if (str_starts_with($id, 'scottbass3_ory_hydra.') || str_starts_with($id, 'Scottbass3\\')) {
                $alias->setPublic(true);
            }
        }
    }
}
