<?php

namespace Scottbass3\OryHydraBundle;

use Scottbass3\Hydra\Client\Api\JwkApi;
use Scottbass3\Hydra\Client\Api\MetadataApi;
use Scottbass3\Hydra\Client\Api\OAuth2Api;
use Scottbass3\Hydra\Client\Api\OidcApi;
use Scottbass3\Hydra\Client\Api\WellknownApi;

/**
 * Entry point to every Ory Hydra API.
 *
 * Each API is also a service of its own and can be injected directly (e.g. OAuth2Api).
 */
final class Client
{
    public function __construct(
        private readonly JwkApi $jwkApi,
        private readonly MetadataApi $metadataApi,
        private readonly OAuth2Api $oAuth2Api,
        private readonly OidcApi $oidcApi,
        private readonly WellknownApi $wellknownApi,
    ) {
    }

    public function jwk(): JwkApi
    {
        return $this->jwkApi;
    }

    public function metadata(): MetadataApi
    {
        return $this->metadataApi;
    }

    public function oAuth2(): OAuth2Api
    {
        return $this->oAuth2Api;
    }

    public function oidc(): OidcApi
    {
        return $this->oidcApi;
    }

    public function wellknown(): WellknownApi
    {
        return $this->wellknownApi;
    }
}
