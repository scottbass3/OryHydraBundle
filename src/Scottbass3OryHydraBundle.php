<?php

namespace Scottbass3\OryHydraBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Symfony bundle for ory hydra client.
 *
 * @author scottbass <scottbass41@gmail.com>
 */
class Scottbass3OryHydraBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->parameters()->set('scottbass3.ory_hydra', $config);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('access_token')
                    ->info('Access token for OAuth/Bearer authentication')
                    ->defaultNull()
                ->end()
                ->scalarNode('username')
                    ->info('Username for HTTP basic authentication')
                    ->defaultNull()
                ->end()
                ->scalarNode('password')
                    ->info('Password for HTTP basic authentication')
                    ->defaultNull()
                ->end()
                ->scalarNode('host')
                    ->info('The host')
                    ->defaultNull()
                ->end()
                ->scalarNode('user_agent')
                    ->info('User agent of the HTTP request, set to "OpenAPI-Generator/{version}/PHP" by default')
                    ->defaultValue('OpenAPI-Generator/1.0.0/PHP')
                ->end()
                ->booleanNode('debug')
                    ->info('Debug switch (default set to false)')
                    ->defaultFalse()
                ->end()
                ->scalarNode('debug_file')
                    ->info('Debug file location (log to STDOUT by default)')
                    ->defaultValue('php://output')
                ->end()
                ->scalarNode('temp_folder_path')
                    ->info('Debug file location (log to STDOUT by default)')
                    ->defaultNull()
                ->end()
            ->end()
        ;
    }
}
