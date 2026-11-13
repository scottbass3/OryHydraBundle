<?php

namespace Scottbass3\OryHydraBundle\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Scottbass3\Hydra\Client\Api\JwkApi;
use Scottbass3\Hydra\Client\Api\MetadataApi;
use Scottbass3\Hydra\Client\Api\OAuth2Api;
use Scottbass3\Hydra\Client\Api\OidcApi;
use Scottbass3\Hydra\Client\Api\WellknownApi;
use Scottbass3\Hydra\Client\Configuration;
use Scottbass3\Hydra\Client\Model\OAuth2RedirectTo;
use Scottbass3\OryHydraBundle\Client;
use Scottbass3\OryHydraBundle\Tests\Fixtures\MockResponseFactory;
use Scottbass3\OryHydraBundle\Tests\Fixtures\TestKernel;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\Response\MockResponse;

final class BundleTest extends TestCase
{
    private ?TestKernel $kernel = null;

    protected function tearDown(): void
    {
        if ($this->kernel) {
            (new Filesystem())->remove($this->kernel->getCacheDir());
            $this->kernel->shutdown();
        }
    }

    /**
     * @param array<string, mixed> $bundleConfig
     * @param array<string, mixed> $httpClientConfig
     */
    private function boot(array $bundleConfig, array $httpClientConfig = []): ContainerInterface
    {
        $this->kernel = new TestKernel($bundleConfig, $httpClientConfig);
        $this->kernel->boot();

        return $this->kernel->getContainer();
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private static function get(ContainerInterface $container, string $class, ?string $id = null): object
    {
        $service = $container->get($id ?? $class);
        self::assertInstanceOf($class, $service);

        return $service;
    }

    private function mock(ContainerInterface $container): MockResponseFactory
    {
        return self::get($container, MockResponseFactory::class);
    }

    public function testHostIsRequired(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/"host".*must be configured/');

        $this->boot([]);
    }

    public function testHostCannotBeEmpty(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->boot(['host' => '']);
    }

    public function testConfigurationIsApplied(): void
    {
        $container = $this->boot([
            'host' => 'http://hydra:4445',
            'access_token' => 'token',
            'username' => 'client-id',
            'password' => 'client-secret',
            'user_agent' => 'my-app',
        ]);

        $configuration = self::get($container, Configuration::class, 'scottbass3_ory_hydra.configuration');
        self::assertSame('http://hydra:4445', $configuration->getHost());
        self::assertSame('token', $configuration->getAccessToken());
        self::assertSame('client-id', $configuration->getUsername());
        self::assertSame('client-secret', $configuration->getPassword());
        self::assertSame('my-app', $configuration->getUserAgent());
        self::assertFalse($configuration->getDebug());
    }

    public function testDefaults(): void
    {
        $container = $this->boot(['host' => 'http://hydra:4445']);
        $configuration = self::get($container, Configuration::class, 'scottbass3_ory_hydra.configuration');

        self::assertSame('scottbass3/ory-hydra-bundle', $configuration->getUserAgent());
        self::assertSame('php://stderr', $configuration->getDebugFile());
        self::assertSame('', $configuration->getAccessToken());
        self::assertSame('', $configuration->getUsername());
        self::assertSame('', $configuration->getPassword());
    }

    /**
     * @return iterable<string, array{class-string<JwkApi|MetadataApi|OAuth2Api|OidcApi|WellknownApi>, string}>
     */
    public static function apis(): iterable
    {
        yield 'jwk' => [JwkApi::class, 'jwk'];
        yield 'metadata' => [MetadataApi::class, 'metadata'];
        yield 'oauth2' => [OAuth2Api::class, 'oAuth2'];
        yield 'oidc' => [OidcApi::class, 'oidc'];
        yield 'wellknown' => [WellknownApi::class, 'wellknown'];
    }

    /**
     * @param class-string<JwkApi|MetadataApi|OAuth2Api|OidcApi|WellknownApi> $class
     */
    #[DataProvider('apis')]
    public function testEachApiIsAutowirableAndExposedByTheClient(string $class, string $accessor): void
    {
        $container = $this->boot(['host' => 'http://hydra:4445']);

        $api = self::get($container, $class);
        $client = self::get($container, Client::class);

        self::assertSame($api, $client->{$accessor}());
        self::assertSame($container->get('scottbass3_ory_hydra.configuration'), $api->getConfig());
    }

    public function testRequestsGoThroughTheFrameworkHttpClient(): void
    {
        $container = $this->boot(['host' => 'http://hydra:4445']);
        $this->mock($container)->queue(new MockResponse('{"version":"v26.2.0"}', ['http_code' => 200]));

        $version = self::get($container, MetadataApi::class)->getVersion();

        self::assertSame('v26.2.0', $version->getVersion());
        $request = $this->mock($container)->requests[0];
        self::assertSame('GET', $request['method']);
        self::assertSame('http://hydra:4445/version', $request['url']);
        self::assertSame('scottbass3/ory-hydra-bundle', $request['headers']['user-agent']);
        self::assertSame(0, $request['max_redirects']);
    }

    public function testSyncAndAsyncRequestsUseTheConfiguredClient(): void
    {
        $container = $this->boot(
            ['host' => 'http://hydra:4445', 'http_client' => 'hydra.client'],
            ['scoped_clients' => ['hydra.client' => ['base_uri' => 'http://hydra:4445', 'headers' => ['X-Scoped' => 'yes']]]]
        );
        $mock = $this->mock($container);
        $mock->queue(new MockResponse('{"redirect_to":"https://app.test/cb"}', ['http_code' => 410]));
        $mock->queue(new MockResponse('{"redirect_to":"https://app.test/cb"}', ['http_code' => 410]));
        $api = self::get($container, OAuth2Api::class);

        $sync = $api->getOAuth2LoginRequest('challenge');
        $async = $api->getOAuth2LoginRequestAsync('challenge')->wait();

        self::assertInstanceOf(OAuth2RedirectTo::class, $sync);
        self::assertInstanceOf(OAuth2RedirectTo::class, $async);
        self::assertCount(2, $mock->requests);
        foreach ($mock->requests as $request) {
            self::assertSame('yes', $request['headers']['x-scoped']);
            self::assertSame('http://hydra:4445/admin/oauth2/auth/requests/login?login_challenge=challenge', $request['url']);
            self::assertSame(0, $request['max_redirects']);
        }
    }

    public function testCredentialsAreNotExposedAsContainerParameters(): void
    {
        $container = $this->boot(['host' => 'http://hydra:4445', 'password' => 'client-secret']);

        self::assertFalse($container->hasParameter('scottbass3.ory_hydra'));
    }
}
