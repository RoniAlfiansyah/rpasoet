<?php

use App\Services\TideFetcher;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Tides;

/**
 * @internal
 */
final class TideFetcherTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        $suffix = sha1('https://example.test');
        foreach ([
            WRITEPATH . 'cache/tides-access-token-' . $suffix . '.json',
            WRITEPATH . 'cache/tides-request-throttle-' . $suffix . '.txt',
            WRITEPATH . 'cache/tides-cookie-' . $suffix . '.txt',
        ] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    public function testFetchUsesShortLivedSrgiAccessToken(): void
    {
        $fetcher = new FakeTideFetcher($this->config(), [
            $this->response(200, '<meta name="csrf-token" content="csrf-value">'),
            $this->response(200, '{"access_token":"token-one","expires_in":180}'),
            $this->measurementResponse(),
        ]);

        $result = $fetcher->fetchStationRows('BBLN', '2026-08-20', 5);

        $this->assertCount(1, $result['rows']);
        $this->assertSame('BBLN', $result['rows'][0]['station_code']);
        $this->assertSame('1.234', $result['rows'][0]['water_level']);
        $this->assertSame('1.111', $result['rows'][0]['prs1']);
        $this->assertSame('1.222', $result['rows'][0]['enc1']);
        $this->assertSame('1.234', $result['rows'][0]['rad1']);
        $this->assertSame('RAD1', $result['rows'][0]['water_level_source']);
        $this->assertSame('POST', $fetcher->requests[1]['method']);
        $this->assertSame('https://example.test/tides/access-token', $fetcher->requests[1]['url']);
        $this->assertContains('X-CSRF-TOKEN: csrf-value', $fetcher->requests[1]['headers']);
        $this->assertContains('Authorization: Bearer token-one', $fetcher->requests[2]['headers']);
        $this->assertSame('1787270399', $fetcher->requests[2]['query']['timestamp']);
    }

    public function testFetchRefreshesAccessTokenOnceAfterUnauthorizedResponse(): void
    {
        $fetcher = new FakeTideFetcher($this->config(), [
            $this->response(200, '<meta name="csrf-token" content="csrf-value">'),
            $this->response(200, '{"access_token":"expired-token","expires_in":180}'),
            $this->response(401, '{"status":"error","code":"TIDES_TOKEN_INVALID","message":"Token invalid"}'),
            $this->response(200, '{"access_token":"fresh-token","expires_in":180}'),
            $this->measurementResponse(),
        ]);

        $result = $fetcher->fetchStationRows('BBLN', '2026-08-20', 5);

        $this->assertCount(1, $result['rows']);
        $this->assertCount(5, $fetcher->requests);
        $this->assertContains('Authorization: Bearer expired-token', $fetcher->requests[2]['headers']);
        $this->assertContains('Authorization: Bearer fresh-token', $fetcher->requests[4]['headers']);
    }

    public function testAccessTokenRequestRetriesAfterRateLimit(): void
    {
        $config = $this->config();
        $config->rateLimitMaxRetries = 1;

        $fetcher = new FakeTideFetcher($config, [
            $this->response(200, '<meta name="csrf-token" content="csrf-value">'),
            [
                'status'  => 429,
                'body'    => '{"message":"Too Many Requests"}',
                'headers' => ['retry-after' => '0'],
            ],
            $this->response(200, '{"access_token":"token-after-wait","expires_in":180}'),
            $this->measurementResponse(),
        ]);

        $result = $fetcher->fetchStationRows('BBLN', '2026-08-20', 5);

        $this->assertCount(1, $result['rows']);
        $this->assertCount(4, $fetcher->requests);
        $this->assertSame('POST', $fetcher->requests[1]['method']);
        $this->assertSame('POST', $fetcher->requests[2]['method']);
        $this->assertContains('Authorization: Bearer token-after-wait', $fetcher->requests[3]['headers']);
    }

    public function testFreshCachedTokenIsReusedByNextFetcherInstance(): void
    {
        $config = $this->config();
        $firstFetcher = new FakeTideFetcher($config, [
            $this->response(200, '<meta name="csrf-token" content="csrf-value">'),
            $this->response(200, '{"access_token":"shared-token","expires_in":180}'),
            $this->measurementResponse(),
        ]);
        $firstFetcher->fetchStationRows('BBLN', '2026-08-20', 5);

        $nextFetcher = new FakeTideFetcher($config, [
            $this->measurementResponse(),
        ]);
        $result = $nextFetcher->fetchStationRows('BBLN', '2026-08-20', 5);

        $this->assertCount(1, $result['rows']);
        $this->assertCount(1, $nextFetcher->requests);
        $this->assertSame('GET', $nextFetcher->requests[0]['method']);
        $this->assertContains('Authorization: Bearer shared-token', $nextFetcher->requests[0]['headers']);
    }

    private function config(): Tides
    {
        $config                    = new Tides();
        $config->baseUrl           = 'https://example.test';
        $config->bootstrapPath     = '/tides';
        $config->accessTokenPath   = '/tides/access-token';
        $config->datedPathTemplate = '/tides_data/pasut_{station_code}';
        $config->loginPath         = null;
        $config->username          = null;
        $config->password          = null;
        $config->maxRetries        = 1;
        $config->dataRequestDelayMs = 0;
        $config->rateLimitBaseDelayMs = 0;
        $config->rateLimitMaxDelayMs = 0;

        $tokenCache = WRITEPATH . 'cache/tides-access-token-' . sha1(rtrim($config->baseUrl, '/')) . '.json';
        if (is_file($tokenCache)) {
            unlink($tokenCache);
        }

        return $config;
    }

    /**
     * @return array{status: int, body: string, headers?: array<string, string>}
     */
    private function response(int $status, string $body): array
    {
        return ['status' => $status, 'body' => $body];
    }

    /**
     * @return array{status: int, body: string}
     */
    private function measurementResponse(): array
    {
        return $this->response(200, json_encode([
            'results' => [
                [
                    'station' => 'BBLN',
                    'ts2'     => 1787184000000,
                    'PRS1'    => '1.111',
                    'ENC1'    => '1.222',
                    'RAD1'    => '1.234',
                ],
                [
                    'station' => 'BBLN',
                    'ts2'     => 1787097600000,
                    'RAD1'    => '9.999',
                ],
            ],
        ], JSON_THROW_ON_ERROR));
    }
}

final class FakeTideFetcher extends TideFetcher
{
    /**
     * @var list<array{status: int, body: string, headers?: array<string, string>}>
     */
    private array $responses;

    /**
     * @var list<array{method: string, url: string, query: array<string, string>, formParams: array<string, string>, headers: list<string>}>
     */
    public array $requests = [];

    /**
     * @param list<array{status: int, body: string, headers?: array<string, string>}> $responses
     */
    public function __construct(Tides $config, array $responses)
    {
        parent::__construct($config);
        $this->responses = $responses;
    }

    protected function performRequest(
        string $method,
        string $url,
        array $query = [],
        array $formParams = [],
        array $headers = [],
    ): array {
        $this->requests[] = compact('method', 'url', 'query', 'formParams', 'headers');

        if ($this->responses === []) {
            throw new RuntimeException('No fake response was queued.');
        }

        return array_shift($this->responses);
    }
}
