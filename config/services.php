<?php

use Scottbass3\OryHydraBundle\Client;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(Client::class)
            ->args([
                service('Psr\Http\Client\ClientInterface'),
                service('Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface'),
            ])
        ->alias('scottbass3.ory_hydra_client', Client::class)
    ;
};
