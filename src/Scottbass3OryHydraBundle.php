<?php

namespace Scottbass3\OryHydraBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * Symfony bundle for the Ory Hydra PSR-18 API client.
 *
 * @author scottbass <scottbass41@gmail.com>
 */
final class Scottbass3OryHydraBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('host')
                    ->info('Base URL of the Hydra API, e.g. "http://hydra:4445" for the admin API.')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('http_client')
                    ->info('Symfony HTTP client service used for sync and async requests, e.g. a scoped client. Redirects are never followed, so that the 302 responses declared by Hydra are returned.')
                    ->defaultValue('http_client')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('access_token')
                    ->info('Access token sent as "Authorization: Bearer" to the endpoints accepting OAuth 2.0 or bearer authentication.')
                    ->defaultNull()
                ->end()
                ->scalarNode('username')
                    ->info('Username for HTTP basic authentication (OAuth 2.0 client ID on the token endpoints).')
                    ->defaultNull()
                ->end()
                ->scalarNode('password')
                    ->info('Password for HTTP basic authentication (OAuth 2.0 client secret on the token endpoints).')
                    ->defaultNull()
                ->end()
                ->scalarNode('user_agent')
                    ->info('User agent of the HTTP requests.')
                    ->defaultValue('scottbass3/ory-hydra-bundle')
                ->end()
                ->booleanNode('debug')
                    ->info('Log every request and response to "debug_file".')
                    ->defaultFalse()
                ->end()
                ->scalarNode('debug_file')
                    ->info('Where debug logs are written when "debug" is enabled.')
                    ->defaultValue('php://stderr')
                ->end()
            ->end()
        ;
    }

    /**
     * @param array{host: string, http_client: string, access_token: ?string, username: ?string, password: ?string, user_agent: string, debug: bool, debug_file: string} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $services = $container->services();

        $services->get('scottbass3_ory_hydra.symfony_http_client')
            ->factory([service($config['http_client']), 'withOptions']);

        $configuration = $services->get('scottbass3_ory_hydra.configuration')
            ->call('setHost', [$config['host']])
            ->call('setUserAgent', [$config['user_agent']])
            ->call('setDebug', [$config['debug']])
            ->call('setDebugFile', [$config['debug_file']]);

        $credentials = [
            'setAccessToken' => $config['access_token'],
            'setUsername' => $config['username'],
            'setPassword' => $config['password'],
        ];
        foreach ($credentials as $setter => $value) {
            if (null !== $value) {
                $configuration->call($setter, [$value]);
            }
        }
    }
}
