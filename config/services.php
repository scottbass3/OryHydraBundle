<?php

use Scottbass3\Hydra\Client\Api\JwkApi;
use Scottbass3\Hydra\Client\Api\MetadataApi;
use Scottbass3\Hydra\Client\Api\OAuth2Api;
use Scottbass3\Hydra\Client\Api\OidcApi;
use Scottbass3\Hydra\Client\Api\WellknownApi;
use Scottbass3\Hydra\Client\Configuration;
use Scottbass3\OryHydraBundle\Client;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpClient\HttplugClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
 * scottbass3_ory_hydra.symfony_http_client gets its factory service from the "http_client" option.
 */
return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('scottbass3_ory_hydra.configuration', Configuration::class);

    // Redirects are never followed, so that the 302 responses declared by Hydra reach the caller.
    $services->set('scottbass3_ory_hydra.symfony_http_client', HttpClientInterface::class)
        ->args([['max_redirects' => 0]]);
    $services->set('scottbass3_ory_hydra.http_client', Psr18Client::class)
        ->args([service('scottbass3_ory_hydra.symfony_http_client')]);
    $services->set('scottbass3_ory_hydra.http_async_client', HttplugClient::class)
        ->args([service('scottbass3_ory_hydra.symfony_http_client')]);

    $apis = [
        'jwk' => JwkApi::class,
        'metadata' => MetadataApi::class,
        'oauth2' => OAuth2Api::class,
        'oidc' => OidcApi::class,
        'wellknown' => WellknownApi::class,
    ];

    foreach ($apis as $name => $class) {
        $services->set('scottbass3_ory_hydra.api.'.$name, $class)
            ->args([
                '$httpClient' => service('scottbass3_ory_hydra.http_client'),
                '$config' => service('scottbass3_ory_hydra.configuration'),
                '$httpAsyncClient' => service('scottbass3_ory_hydra.http_async_client'),
            ]);
        $services->alias($class, 'scottbass3_ory_hydra.api.'.$name);
    }

    $services->set('scottbass3_ory_hydra.client', Client::class)
        ->args([
            service('scottbass3_ory_hydra.api.jwk'),
            service('scottbass3_ory_hydra.api.metadata'),
            service('scottbass3_ory_hydra.api.oauth2'),
            service('scottbass3_ory_hydra.api.oidc'),
            service('scottbass3_ory_hydra.api.wellknown'),
        ]);
    $services->alias(Client::class, 'scottbass3_ory_hydra.client');
};
