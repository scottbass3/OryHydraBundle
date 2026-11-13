# Scottbass3OryHydraBundle

Symfony integration of [scottbass3/hydra-client-php-psr-18](https://github.com/scottbass3/hydra-client-php-psr-18).
Installation is described in the [README](../README.md).

## Configuration reference

```yaml
# config/packages/scottbass3_ory_hydra.yaml
scottbass3_ory_hydra:
    # Required. Base URL of the Hydra API, e.g. "http://hydra:4445" for the admin API.
    host: '%env(HYDRA_ADMIN_URL)%'

    # Symfony HTTP client service used for sync and async requests.
    http_client: http_client

    # Sent as "Authorization: Bearer" to the endpoints accepting OAuth 2.0 or bearer authentication.
    access_token: ~

    # HTTP basic authentication (OAuth 2.0 client ID and secret on the token endpoints).
    username: ~
    password: ~

    user_agent: scottbass3/ory-hydra-bundle

    # Log every request and response to "debug_file".
    debug: false
    debug_file: php://stderr
```

Keep secrets in environment variables (or [Symfony secrets](https://symfony.com/doc/current/configuration/secrets.html)):

```yaml
scottbass3_ory_hydra:
    host: '%env(HYDRA_ADMIN_URL)%'
    username: '%env(HYDRA_CLIENT_ID)%'
    password: '%env(HYDRA_CLIENT_SECRET)%'
```

When both an access token and a username/password are configured, endpoints accepting both
(`oauth2TokenExchange`, `revokeOAuth2Token`) send the access token.

## Services

| Service | Autowiring type |
|---|---|
| `scottbass3_ory_hydra.api.oauth2` | `Scottbass3\Hydra\Client\Api\OAuth2Api` |
| `scottbass3_ory_hydra.api.oidc` | `Scottbass3\Hydra\Client\Api\OidcApi` |
| `scottbass3_ory_hydra.api.jwk` | `Scottbass3\Hydra\Client\Api\JwkApi` |
| `scottbass3_ory_hydra.api.metadata` | `Scottbass3\Hydra\Client\Api\MetadataApi` |
| `scottbass3_ory_hydra.api.wellknown` | `Scottbass3\Hydra\Client\Api\WellknownApi` |
| `scottbass3_ory_hydra.client` | `Scottbass3\OryHydraBundle\Client` (exposes every API: `oAuth2()`, `oidc()`, `jwk()`, `metadata()`, `wellknown()`) |
| `scottbass3_ory_hydra.configuration` | none (`Scottbass3\Hydra\Client\Configuration` shared by the APIs) |

Prefer injecting the API you need over the `Client` facade.

## Responses and errors

Every API method returns the model the Hydra spec declares for the received status code, errors included.
For example `getOAuth2LoginRequest()` returns:

| Status | Model |
|---|---|
| 200 | `OAuth2LoginRequest` |
| 410 | `OAuth2RedirectTo` (the request was already handled) |
| other | `ErrorOAuth2` |

Check the result with `instanceof`. The per-operation tables are in the client's `docs/Api/*.md`.

`*WithHttpInfo()` methods also return the status code and the headers. An
`Scottbass3\Hydra\Client\ApiException` is only thrown on network errors and on statuses the spec does not declare.

Async methods (`*Async()`) return a `GuzzleHttp\Promise\PromiseInterface` and use the same HTTP client.

## Using a dedicated HTTP client

By default the bundle uses the application's `http_client` service. Point `http_client` to a
[scoped client](https://symfony.com/doc/current/http_client.html#scoping-client) to give Hydra its own timeouts or
headers:

```yaml
# config/packages/framework.yaml
framework:
    http_client:
        scoped_clients:
            hydra.client:
                base_uri: '%env(HYDRA_ADMIN_URL)%'
                timeout: 5

# config/packages/scottbass3_ory_hydra.yaml
scottbass3_ory_hydra:
    host: '%env(HYDRA_ADMIN_URL)%'
    http_client: hydra.client
```

Whatever the client, the bundle never follows redirects (`max_redirects: 0`): the 302 responses declared by
`oAuth2Authorize`, `performOAuth2DeviceVerificationFlow` and `revokeOidcSession` are returned as a `null` model, with
the `Location` header available through `*WithHttpInfo()`.
