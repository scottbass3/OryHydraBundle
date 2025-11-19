# Ory Hydra Bundle

A Symfony bundle to communicate with Ory Hydra API. This bundle is a wrapper for [ory hydra client](https://github.com/ory/hydra-client-php).

## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```sh
composer require scottbass3/ory-hydra-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```sh
composer require scottbass3/ory-hydra-bundle
```

####  Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Scottbass3\OryHydraBundle\Scottbass3OryHydraBundle::class => ['all' => true],
];
```

## Configuration

This is an example configuration, feel free to modify it according to your needs.
```yaml
# config/packages/scottbass3_ory_hydra.yaml

ory_hydra_api:
    access_token: 'change_me'
    username: 'test'
    password: 'change_me'
    host: 'example.com'
    user_agent: 'OpenAPI-Generator/1.0.0/PHP'
    debug: false
    debug_file: 'php://output'
    temp_folder_path: ~
```

## Basic Usage

```php
namespace App\Controller;

use Scottbass3\OryHydraBundle\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController {
    public function index(Client $client, string $loginChallenge) {
        $loginRequest = $this->client->oauth2->getOAuth2LoginRequest($loginChallenge);
     
        return $loginRequest->getSubject();
    }
}