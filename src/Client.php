<?php

namespace Scottbass3\OryHydraBundle;

use Psr\Http\Client\ClientInterface;
use Scottbass3\Hydra\Client\Api\JwkApi;
use Scottbass3\Hydra\Client\Api\MetadataApi;
use Scottbass3\Hydra\Client\Api\OAuth2Api;
use Scottbass3\Hydra\Client\Api\OidcApi;
use Scottbass3\Hydra\Client\Api\WellknownApi;
use Scottbass3\Hydra\Client\Configuration as HydraConfiguration;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Client
{
    private ?JwkApi $jwkApi = null;
    private ?MetadataApi $metadataApi = null;
    private ?OAuth2Api $oAuth2Api = null;
    private ?OidcApi $oidcApi = null;
    private ?WellknownApi $wellknownApi = null;

    private ClientInterface $httpClient;
    private HydraConfiguration $hydraConfiguration;

    public function __construct(ClientInterface $httpClient, ParameterBagInterface $parameterBag)
    {
        $this->httpClient = $httpClient;

        $parameters = $parameterBag->get('scottbass3.ory_hydra');

        $hydraConfig = (new HydraConfiguration())
            ->setAccessToken($parameters['access_token'])
            ->setUsername($parameters['username'])
            ->setPassword($parameters['password'])
            ->setHost($parameters['host'])
            ->setUserAgent($parameters['user_agent'])
            ->setDebug($parameters['debug'])
            ->setDebugFile($parameters['debug_file'])
            ->setTempFolderPath($parameters['temp_folder_path'])
        ;

        foreach ($parameterBag->get('scottbass3.ory_hydra') as $key => $item) {
            if (null !== $item) {
                // Snake case to camel case
                $keyC = ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
                $setter = 'set'.$keyC;

                $hydraConfig->$setter($item);
            }
        }

        $this->hydraConfiguration = $hydraConfig;
    }

    public function jwk(): JwkApi
    {
        if (null === $this->jwkApi) {
            $this->jwkApi = new JwkApi(httpClient: $this->httpClient, config: $this->hydraConfiguration);
        }

        return $this->jwkApi;
    }

    public function metadata(): MetadataApi
    {
        if (null === $this->metadataApi) {
            $this->metadataApi = new MetadataApi(httpClient: $this->httpClient, config: $this->hydraConfiguration);
        }

        return $this->metadataApi;
    }

    public function oAuth2(): OAuth2Api
    {
        if (null === $this->oAuth2Api) {
            $this->oAuth2Api = new OAuth2Api(httpClient: $this->httpClient, config: $this->hydraConfiguration);
        }

        return $this->oAuth2Api;
    }

    public function oidc(): OidcApi
    {
        if (null === $this->oidcApi) {
            $this->oidcApi = new OidcApi(httpClient: $this->httpClient, config: $this->hydraConfiguration);
        }

        return $this->oidcApi;
    }

    public function wellknown(): WellknownApi
    {
        if (null === $this->wellknownApi) {
            $this->wellknownApi = new WellknownApi(httpClient: $this->httpClient, config: $this->hydraConfiguration);
        }

        return $this->wellknownApi;
    }
}
