<?php

namespace Scottbass3\OryHydraBundle\Tests\Fixtures;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Answers every request of the Symfony HTTP clients and records them.
 */
final class MockResponseFactory
{
    /** @var list<array{method: string, url: string, headers: array<string, string>, max_redirects: mixed}> */
    public array $requests = [];

    /** @var list<MockResponse> */
    private array $responses = [];

    public function queue(MockResponse $response): void
    {
        $this->responses[] = $response;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        $headers = [];
        foreach ($options['headers'] ?? [] as $header) {
            [$name, $value] = explode(': ', $header, 2);
            $headers[strtolower($name)] = $value;
        }
        $this->requests[] = [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'max_redirects' => $options['max_redirects'] ?? null,
        ];

        return array_shift($this->responses) ?? new MockResponse('{}', ['http_code' => 200]);
    }
}
