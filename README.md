# Scottbass3OryHydraBundle

Symfony bundle for [scottbass3/hydra-client-php-psr-18](https://github.com/scottbass3/hydra-client-php-psr-18),
a PSR-18 client for the [Ory Hydra](https://www.ory.sh/hydra/) API.

It registers every Hydra API (`OAuth2Api`, `OidcApi`, `JwkApi`, `MetadataApi`, `WellknownApi`) as an autowirable
service, sending requests through a Symfony HTTP client.

Requires PHP 8.1+ and Symfony 6.4, 7 or 8.

## Installation

```sh
composer require scottbass3/ory-hydra-bundle
```

Applications that do not use Symfony Flex must enable the bundle in `config/bundles.php`:

```php
return [
    // ...
    Scottbass3\OryHydraBundle\Scottbass3OryHydraBundle::class => ['all' => true],
];
```

## Configuration

```yaml
# config/packages/scottbass3_ory_hydra.yaml
scottbass3_ory_hydra:
    host: '%env(HYDRA_ADMIN_URL)%'
```

## Usage

```php
use Scottbass3\Hydra\Client\Api\OAuth2Api;
use Scottbass3\Hydra\Client\Model\OAuth2LoginRequest;
use Scottbass3\Hydra\Client\Model\OAuth2RedirectTo;

final class LoginController
{
    public function __construct(private readonly OAuth2Api $oAuth2Api)
    {
    }

    public function __invoke(string $loginChallenge): Response
    {
        $loginRequest = $this->oAuth2Api->getOAuth2LoginRequest($loginChallenge);

        if ($loginRequest instanceof OAuth2RedirectTo) {
            // The request was already handled (HTTP 410)
            return new RedirectResponse($loginRequest->getRedirectTo());
        }
        // ...
    }
}
```

See the [documentation](docs/index.md) for every option, authentication, error handling and the upgrade from 1.x.

## License

MIT, see [LICENSE](LICENSE).
