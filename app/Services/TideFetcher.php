<?php

namespace App\Services;

use Config\Tides;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use RuntimeException;
use Throwable;

class TideFetcher
{
    private Tides $config;

    private string $cookieFile;

    private string $accessTokenCacheFile;

    private string $requestThrottleFile;

    private bool $sessionBootstrapped = false;

    /**
     * @var array{name: string, value: string}|null
     */
    private ?array $csrfToken = null;

    private ?string $accessToken = null;

    private int $accessTokenExpiresAt = 0;

    public function __construct(?Tides $config = null)
    {
        $this->config = $config ?? config(Tides::class);

        $cacheIdentity = rtrim($this->config->baseUrl, '/');
        if ($this->config->shouldAuthenticate()) {
            $cacheIdentity .= '|account:' . strtolower(trim((string) $this->config->username));
        }

        $cacheSuffix = sha1($cacheIdentity);
        $this->cookieFile = WRITEPATH . 'cache/tides-cookie-' . $cacheSuffix . '.txt';
        $this->accessTokenCacheFile = WRITEPATH . 'cache/tides-access-token-' . $cacheSuffix . '.json';
        $this->requestThrottleFile  = WRITEPATH . 'cache/tides-request-throttle-' . $cacheSuffix . '.txt';
    }

    /**
     * @param list<string>|null $stations
     * @return array<string, mixed>
     */
    public function fetch(?array $stations = null, ?string $date = null, int $resolutionMinutes = 10): array
    {
        $stations ??= $this->config->getStations();
        $stations = array_values(array_unique(array_map(
            fn (string $station): string => $this->normalizeStationCode($station),
            $stations,
        )));

        if ($stations === []) {
            throw new RuntimeException('No station codes configured for tide fetching.');
        }

        $normalizedDate = $date !== null ? $this->normalizeInputDate($date) : null;

        $summary = [
            'stations_total'     => count($stations),
            'stations_succeeded' => 0,
            'stations_failed'    => 0,
            'rows_parsed'        => 0,
            'rows_written'       => 0,
            'results'            => [],
        ];

        foreach ($stations as $station) {
            $result = $this->fetchStation($station, $normalizedDate, $resolutionMinutes);
            $summary['results'][] = $result;
            $summary['rows_parsed'] += $result['rows_parsed'];
            $summary['rows_written'] += $result['rows_written'];

            if ($result['success']) {
                $summary['stations_succeeded']++;
            } else {
                $summary['stations_failed']++;
            }
        }

        return $summary;
    }

    /**
     * @return list<array{code: string, label: string}>
     */
    public function getAvailableStations(): array
    {
        $response = $this->requestWithRetry('GET', $this->makeUrl($this->config->bootstrapPath));
        $html     = trim($response['body']);

        if ($html === '') {
            throw new RuntimeException('Halaman daftar stasiun SRGI kosong.');
        }

        preg_match_all('/<option\s+value="([^"]+)">\s*([^<]+?)\s*<\/option>/i', $html, $matches, PREG_SET_ORDER);

        $stations = [];
        foreach ($matches as $match) {
            $code  = strtoupper(trim((string) ($match[1] ?? '')));
            $label = trim(preg_replace('/\s+/', ' ', (string) ($match[2] ?? '')) ?? '');

            if ($code === '' || $label === '' || $code === 'PILIH STASIUN') {
                continue;
            }

            $stations[$code] = [
                'code'  => $code,
                'label' => $label,
            ];
        }

        if ($stations === []) {
            throw new RuntimeException('Daftar stasiun SRGI tidak berhasil diparsing.');
        }

        ksort($stations);

        return array_values($stations);
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchStation(string $stationCode, ?string $date = null, int $resolutionMinutes = 10): array
    {
        try {
            $fetched = $this->fetchStationRows($stationCode, $date, $resolutionMinutes);

            return [
                'station_code' => $fetched['station_code'],
                'date'         => $fetched['date'],
                'success'      => true,
                'endpoint'     => $fetched['endpoint'],
                'rows_parsed'  => count($fetched['rows']),
                'rows_written' => 0,
                'error'        => null,
                'rows'         => $fetched['rows'],
            ];
        } catch (Throwable $e) {
            $stationCode    = $this->normalizeStationCode($stationCode);
            $normalizedDate = $date !== null ? $this->normalizeInputDate($date) : null;
            log_message(
                'error',
                'Tide fetch failed for station {station} on {date}: {message}',
                [
                    'station' => $stationCode,
                    'date'    => $normalizedDate ?? 'realtime',
                    'message' => $e->getMessage(),
                ],
            );

            return [
                'station_code' => $stationCode,
                'date'         => $normalizedDate,
                'success'      => false,
                'endpoint'     => null,
                'rows_parsed'  => 0,
                'rows_written' => 0,
                'error'        => $e->getMessage(),
                'rows'         => [],
            ];
        }
    }

    /**
     * @return array{station_code: string, date: string|null, endpoint: string, resolution_minutes: int, rows: list<array<string, mixed>>}
     */
    public function fetchStationRows(string $stationCode, ?string $date = null, int $resolutionMinutes = 10): array
    {
        $stationCode    = $this->normalizeStationCode($stationCode);
        $normalizedDate = $date !== null ? $this->normalizeInputDate($date) : null;
        $resolutionMinutes = $this->normalizeResolutionMinutes($resolutionMinutes);

        $this->bootstrapSession();

        $request       = $this->buildEndpoint($stationCode, $normalizedDate);
        $resolvedFetch = $this->fetchPayloadWithFallback($stationCode, $request, $normalizedDate);
        $rows          = $this->parseMeasurements($resolvedFetch['payload'], $stationCode, $resolvedFetch['body'], $resolutionMinutes);

        if ($normalizedDate !== null) {
            $rows = $this->filterRowsForUtcDate($rows, $normalizedDate);
        }

        return [
            'station_code' => $stationCode,
            'date'         => $normalizedDate,
            'endpoint'     => $resolvedFetch['url'],
            'resolution_minutes' => $resolutionMinutes,
            'rows'         => $rows,
        ];
    }

    /**
     * @param array{url: string, query: array<string, string>} $request
     * @return array{url: string, body: string, payload: array<mixed>|array<string, mixed>}
     */
    private function fetchPayloadWithFallback(string $stationCode, array $request, ?string $date): array
    {
        try {
            return $this->fetchPayload($stationCode, $request);
        } catch (Throwable $e) {
            if ($date !== null) {
                throw $e;
            }

            $fallbackDate    = $this->getCurrentSourceDate();
            $fallbackRequest = $this->buildEndpoint($stationCode, $fallbackDate);

            log_message(
                'warning',
                'Realtime SRGI fetch failed for station {station}, falling back to dated endpoint for {date}: {message}',
                [
                    'station' => $stationCode,
                    'date'    => $fallbackDate,
                    'message' => $e->getMessage(),
                ],
            );

            return $this->fetchPayload($stationCode, $fallbackRequest);
        }
    }

    /**
     * @param array{url: string, query: array<string, string>} $request
     * @return array{url: string, body: string, payload: array<mixed>|array<string, mixed>}
     */
    private function fetchPayload(string $stationCode, array $request): array
    {
        $response = $this->requestWithAccessToken($request['url'], $request['query']);
        $body     = trim($response['body']);

        if ($response['status'] === 429) {
            throw new RuntimeException(
                'SRGI sedang membatasi permintaan (HTTP 429). Tunggu beberapa menit, lalu coba kembali.',
            );
        }

        if ($response['status'] >= 400) {
            throw new RuntimeException('SRGI gagal memberikan data. HTTP ' . $response['status']);
        }

        if ($body === '') {
            throw new RuntimeException('Empty response body received from SRGI.');
        }

        if ($this->looksLikeHtmlResponse($body)) {
            throw new RuntimeException('SRGI returned HTML instead of JSON. Session/login may be required.');
        }

        $payload = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response: ' . json_last_error_msg());
        }

        if ($this->looksLikeErrorPayload($payload)) {
            $message = is_array($payload) && isset($payload['message']) ? (string) $payload['message'] : 'Unknown upstream API error.';
            log_message(
                'error',
                'SRGI returned an error payload for station {station}: {message}',
                [
                    'station' => $stationCode,
                    'message' => $message,
                ],
            );

            throw new RuntimeException('SRGI error payload: ' . $message);
        }

        return [
            'url'     => $this->buildRequestUrl($request['url'], $request['query']),
            'body'    => $body,
            'payload' => $payload,
        ];
    }

    private function bootstrapSession(): void
    {
        if ($this->sessionBootstrapped) {
            return;
        }

        $this->ensureCookieStorage();

        if ($this->loadCachedAccessToken()) {
            $this->sessionBootstrapped = true;

            return;
        }

        $response = $this->requestWithRetry('GET', $this->makeUrl($this->config->bootstrapPath));

        if ($response['status'] >= 400) {
            throw new RuntimeException('Failed to bootstrap SRGI session. HTTP ' . $response['status']);
        }

        $this->csrfToken = $this->extractCsrfToken($response['body']);

        if ($this->config->shouldAuthenticate()) {
            $this->authenticate();
        }

        $this->sessionBootstrapped = true;
    }

    private function authenticate(): void
    {
        $payload = [
            $this->config->usernameField => (string) $this->config->username,
            $this->config->passwordField => (string) $this->config->password,
        ];

        if ($this->csrfToken !== null) {
            $payload[$this->csrfToken['name']] = $this->csrfToken['value'];
        }

        if ($this->config->loginSubmitField !== null && $this->config->loginSubmitField !== '') {
            $payload[$this->config->loginSubmitField] = (string) $this->config->loginSubmitValue;
        }

        $response = $this->requestWithRetry('POST', $this->makeUrl((string) $this->config->loginPath), [], $payload);

        if ($response['status'] >= 400) {
            throw new RuntimeException('SRGI authentication failed. HTTP ' . $response['status']);
        }
    }

    /**
     * @param array<string, string> $query
     * @return array{status: int, body: string, headers?: array<string, string>}
     */
    private function requestWithAccessToken(string $url, array $query): array
    {
        $token = $this->getAccessToken();
        $this->throttleDataRequest();
        $response = $this->requestWithRetry('GET', $url, $query, [], [
            'Authorization: Bearer ' . $token,
        ]);

        if ($response['status'] !== 401) {
            return $response;
        }

        $this->invalidateAccessToken();
        if ($this->csrfToken === null) {
            $this->sessionBootstrapped = false;
            $this->bootstrapSession();
        }
        $token = $this->getAccessToken(true);
        $this->throttleDataRequest();

        return $this->requestWithRetry('GET', $url, $query, [], [
            'Authorization: Bearer ' . $token,
        ]);
    }

    private function getAccessToken(bool $forceRefresh = false): string
    {
        if (
            ! $forceRefresh
            && $this->accessToken !== null
            && time() < ($this->accessTokenExpiresAt - 15)
        ) {
            return $this->accessToken;
        }

        if ($this->csrfToken === null || trim($this->csrfToken['value']) === '') {
            throw new RuntimeException('Token CSRF SRGI tidak ditemukan saat meminta token akses pasut.');
        }

        $response = $this->requestWithRetry(
            'POST',
            $this->makeUrl($this->config->accessTokenPath),
            [],
            [],
            [
                'X-Requested-With: XMLHttpRequest',
                'X-CSRF-TOKEN: ' . $this->csrfToken['value'],
            ],
        );

        if ($response['status'] >= 400) {
            throw new RuntimeException('SRGI gagal memberikan token akses pasut. HTTP ' . $response['status']);
        }

        $payload = json_decode(trim($response['body']), true);
        $token   = is_array($payload) ? trim((string) ($payload['access_token'] ?? '')) : '';
        $expires = is_array($payload) ? (int) ($payload['expires_in'] ?? 0) : 0;

        if ($token === '' || $expires <= 0) {
            throw new RuntimeException('SRGI tidak memberikan token akses pasut yang valid.');
        }

        $this->accessToken          = $token;
        $this->accessTokenExpiresAt = time() + $expires;
        $this->persistAccessToken();

        return $token;
    }

    private function invalidateAccessToken(): void
    {
        $this->accessToken          = null;
        $this->accessTokenExpiresAt = 0;

        if (is_file($this->accessTokenCacheFile)) {
            @unlink($this->accessTokenCacheFile);
        }
    }

    private function loadCachedAccessToken(): bool
    {
        if (! is_file($this->accessTokenCacheFile)) {
            return false;
        }

        $contents = file_get_contents($this->accessTokenCacheFile);
        $payload  = is_string($contents) ? json_decode($contents, true) : null;
        $token    = is_array($payload) ? trim((string) ($payload['access_token'] ?? '')) : '';
        $expires  = is_array($payload) ? (int) ($payload['expires_at'] ?? 0) : 0;

        if ($token === '' || time() >= ($expires - 15)) {
            $this->invalidateAccessToken();

            return false;
        }

        $this->accessToken          = $token;
        $this->accessTokenExpiresAt = $expires;

        return true;
    }

    private function persistAccessToken(): void
    {
        $directory = dirname($this->accessTokenCacheFile);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($this->accessTokenCacheFile, json_encode([
            'access_token' => $this->accessToken,
            'expires_at'   => $this->accessTokenExpiresAt,
        ], JSON_THROW_ON_ERROR), LOCK_EX);
    }

    private function throttleDataRequest(): void
    {
        $minimumDelayMs = max(0, $this->config->dataRequestDelayMs);
        if ($minimumDelayMs === 0) {
            return;
        }

        $handle = fopen($this->requestThrottleFile, 'c+');
        if ($handle === false) {
            usleep($minimumDelayMs * 1000);

            return;
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                usleep($minimumDelayMs * 1000);

                return;
            }

            rewind($handle);
            $lastRequestAt = (float) trim((string) stream_get_contents($handle));
            $elapsedMs     = (microtime(true) - $lastRequestAt) * 1000;
            $remainingMs   = $minimumDelayMs - $elapsedMs;

            if ($lastRequestAt > 0 && $remainingMs > 0) {
                usleep((int) ceil($remainingMs * 1000));
            }

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, (string) microtime(true));
            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return array{url: string, query: array<string, string>}
     */
    private function buildEndpoint(string $stationCode, ?string $date): array
    {
        if ($date === null) {
            return [
                'url'   => $this->makeUrl($this->config->realtimePath),
                'query' => ['stasiun' => $stationCode],
            ];
        }

        $dateObject = new DateTimeImmutable($date, new DateTimeZone($this->config->sourceTimezone));
        $path       = str_replace('{station_code}', $stationCode, $this->config->datedPathTemplate);
        $requestTimestamp = (new DateTimeImmutable(
            $dateObject->format('Y-m-d') . ' 23:59:59',
            new DateTimeZone('UTC'),
        ))->getTimestamp();

        return [
            'url'   => $this->makeUrl($path),
            'query' => [
                'new'       => 'true',
                'date'      => $dateObject->format('Y/n/j'),
                'timestamp' => (string) $requestTimestamp,
            ],
        ];
    }

    private function makeUrl(string $path): string
    {
        return rtrim($this->config->baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * @param array<string, string> $query
     */
    private function buildRequestUrl(string $url, array $query = []): string
    {
        if ($query === []) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($query);
    }

    /**
     * @param array<string, string> $query
     * @param array<string, string> $formParams
     * @return array{status: int, body: string}
     */
    private function requestWithRetry(
        string $method,
        string $url,
        array $query = [],
        array $formParams = [],
        array $headers = [],
    ): array
    {
        $maxStandardAttempts = max(1, $this->config->maxRetries);
        $maxRateLimitRetries = max(0, $this->config->rateLimitMaxRetries);
        $standardFailures    = 0;
        $rateLimitRetries    = 0;

        while (true) {
            try {
                $response = $this->performRequest($method, $url, $query, $formParams, $headers);
            } catch (Throwable $e) {
                $standardFailures++;

                log_message(
                    'warning',
                    'Tide request attempt {attempt}/{max} failed for {method} {url}: {message}',
                    [
                        'attempt' => $standardFailures,
                        'max'     => $maxStandardAttempts,
                        'method'  => $method,
                        'url'     => $url,
                        'message' => $e->getMessage(),
                    ],
                );

                if ($standardFailures >= $maxStandardAttempts) {
                    throw new RuntimeException($e->getMessage(), 0, $e);
                }

                usleep(max(0, $this->config->retryDelayMs) * 1000);

                continue;
            }

            if ($response['status'] === 429 && $rateLimitRetries < $maxRateLimitRetries) {
                $rateLimitRetries++;
                $delayMs = $this->resolveRateLimitDelayMs($response['headers'] ?? [], $rateLimitRetries);

                log_message(
                    'warning',
                    'SRGI rate limit for {method} {url}. Retry {attempt}/{max} after {delay} ms.',
                    [
                        'method'  => $method,
                        'url'     => $url,
                        'attempt' => $rateLimitRetries,
                        'max'     => $maxRateLimitRetries,
                        'delay'   => $delayMs,
                    ],
                );

                if ($delayMs > 0) {
                    usleep($delayMs * 1000);
                }

                continue;
            }

            if ($response['status'] >= 500 && $standardFailures < ($maxStandardAttempts - 1)) {
                $standardFailures++;
                usleep(max(0, $this->config->retryDelayMs) * 1000);

                continue;
            }

            return $response;
        }
    }

    /**
     * @param array<string, string> $headers
     */
    private function resolveRateLimitDelayMs(array $headers, int $retryNumber): int
    {
        $maximumDelayMs = max(0, $this->config->rateLimitMaxDelayMs);
        $retryAfter     = trim((string) ($headers['retry-after'] ?? ''));

        if ($retryAfter !== '') {
            if (is_numeric($retryAfter)) {
                return min($maximumDelayMs, max(0, (int) ceil((float) $retryAfter * 1000)));
            }

            $retryAt = strtotime($retryAfter);
            if ($retryAt !== false) {
                return min($maximumDelayMs, max(0, ($retryAt - time()) * 1000));
            }
        }

        $baseDelayMs = max(0, $this->config->rateLimitBaseDelayMs);
        $backoffMs   = $baseDelayMs * (2 ** max(0, $retryNumber - 1));
        $jitterMs    = $baseDelayMs > 0 ? random_int(0, max(1, (int) floor($baseDelayMs / 4))) : 0;

        return min($maximumDelayMs, $backoffMs + $jitterMs);
    }

    /**
     * @param array<string, string> $query
     * @param array<string, string> $formParams
     * @return array{status: int, body: string, headers: array<string, string>}
     */
    protected function performRequest(
        string $method,
        string $url,
        array $query = [],
        array $formParams = [],
        array $headers = [],
    ): array
    {
        if (! extension_loaded('curl')) {
            throw new RuntimeException('The cURL PHP extension is required for tide fetching.');
        }

        $fullUrl = $url;
        if ($query !== []) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $fullUrl  .= $separator . http_build_query($query);
        }

        $ch = curl_init($fullUrl);
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize cURL request.');
        }

        $responseHeaders = [];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => $this->config->connectTimeout,
            CURLOPT_TIMEOUT        => $this->config->timeout,
            CURLOPT_HTTPHEADER     => array_merge([
                'Accept: application/json, text/plain, */*',
                'User-Agent: CodeIgniter4 TideFetcher/1.0',
            ], $headers),
            CURLOPT_COOKIEFILE     => $this->cookieFile,
            CURLOPT_COOKIEJAR      => $this->cookieFile,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $headerLine) use (&$responseHeaders): int {
                $length = strlen($headerLine);
                $parts  = explode(':', $headerLine, 2);

                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }

                return $length;
            },
        ]);

        if (strtoupper($method) === 'POST') {
            curl_setopt_array($ch, [
                CURLOPT_POST       => true,
                CURLOPT_POSTFIELDS => http_build_query($formParams),
            ]);
        }

        $body   = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $error !== '') {
            throw new RuntimeException('cURL request failed: ' . ($error !== '' ? $error : 'unknown error'));
        }

        return [
            'status' => $status,
            'body'   => (string) $body,
            'headers' => $responseHeaders,
        ];
    }

    private function ensureCookieStorage(): void
    {
        $directory = dirname($this->cookieFile);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        if (! is_file($this->cookieFile)) {
            file_put_contents($this->cookieFile, '');
        }
    }

    /**
     * @param mixed $payload
     * @return list<array<string, mixed>>
     */
    private function parseMeasurements($payload, string $requestedStationCode, string $rawJson, int $resolutionMinutes): array
    {
        $records = $this->collectCandidateRecords($payload);
        $rows    = [];

        foreach ($records as $record) {
            $measuredAtUtc = $this->extractMeasuredAtUtc($record);
            if ($measuredAtUtc === null) {
                continue;
            }

            if (! $this->shouldStoreMeasurementAt($measuredAtUtc, $resolutionMinutes)) {
                continue;
            }

            $prs1 = $this->extractNumericValue($record, ['prs1']);
            $enc1 = $this->extractNumericValue($record, ['enc1']);
            $rad1 = $this->extractNumericValue($record, ['rad1']);

            $fallbackWaterLevel = $this->extractNumericValue($record, [
                'rad2',
                'prs2',
                'rad3',
                'prs3',
                'water_level',
                'waterlevel',
                'observed_level',
                'observed',
                'elevation',
                'height',
                'tinggi_air',
                'tinggi',
            ]);

            $waterLevel = $rad1 ?? $prs1 ?? $enc1 ?? $fallbackWaterLevel;

            if ($waterLevel === null) {
                continue;
            }

            $waterLevelSource = match (true) {
                $rad1 !== null => 'RAD1',
                $prs1 !== null => 'PRS1',
                $enc1 !== null => 'ENC1',
                default        => 'fallback',
            };

            $stationCode = $this->extractStringValue($record, ['station_code', 'station', 'stasiun', 'kode_stasiun']) ?? $requestedStationCode;
            $stationCode = $this->normalizeStationCode($stationCode);

            $row = [
                'station_code'    => $stationCode,
                'measured_at_utc' => $measuredAtUtc,
                'prs1'            => $prs1,
                'enc1'            => $enc1,
                'rad1'            => $rad1,
                'water_level'     => $waterLevel,
                'water_level_source' => $waterLevelSource,
            ];

            $rows[$stationCode . '|' . $measuredAtUtc] = $row;
        }

        if ($rows === []) {
            log_message(
                'error',
                'Tide parsing failed for station {station}. Raw response snippet: {snippet}',
                [
                    'station' => $requestedStationCode,
                    'snippet' => substr($rawJson, 0, 1000),
                ],
            );

            throw new RuntimeException('No tide measurements could be parsed from the SRGI response.');
        }

        return array_values($rows);
    }

    private function shouldStoreMeasurementAt(string $measuredAtUtc, int $resolutionMinutes): bool
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $measuredAtUtc, new DateTimeZone('UTC'));

        if (! $date instanceof DateTimeImmutable) {
            return false;
        }

        $minute = (int) $date->format('i');
        $second = (int) $date->format('s');

        return $second === 0 && $minute % $resolutionMinutes === 0;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function filterRowsForUtcDate(array $rows, string $requestedDate): array
    {
        $utcTimezone = new DateTimeZone('UTC');

        return array_values(array_filter($rows, static function (array $row) use ($requestedDate, $utcTimezone): bool {
            $measuredAtUtc = $row['measured_at_utc'] ?? null;
            if (! is_string($measuredAtUtc) || trim($measuredAtUtc) === '') {
                return false;
            }

            $measuredAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $measuredAtUtc, $utcTimezone);

            return $measuredAt instanceof DateTimeImmutable
                && $measuredAt->format('Y-m-d') === $requestedDate;
        }));
    }

    private function normalizeResolutionMinutes(int $resolutionMinutes): int
    {
        $allowed = [5, 10, 15, 30, 60];

        if (! in_array($resolutionMinutes, $allowed, true)) {
            throw new RuntimeException('Resolusi harus salah satu dari 5, 10, 15, 30, atau 60 menit.');
        }

        return $resolutionMinutes;
    }

    /**
     * @param mixed $node
     * @return list<array<string, mixed>>
     */
    private function collectCandidateRecords($node): array
    {
        if (is_array($node) && isset($node['results']) && is_array($node['results'])) {
            return array_values(array_filter($node['results'], static fn ($item): bool => is_array($item)));
        }

        $records = [];
        $this->walkRecords($node, $records);

        if ($records === [] && is_array($node) && $this->looksLikeMeasurementRecord($node)) {
            $records[] = $node;
        }

        return $records;
    }

    /**
     * @param mixed $node
     * @param list<array<string, mixed>> $records
     */
    private function walkRecords($node, array &$records): void
    {
        if (! is_array($node)) {
            return;
        }

        if ($this->isListOfArrays($node)) {
            foreach ($node as $item) {
                if (is_array($item) && $this->looksLikeMeasurementRecord($item)) {
                    $records[] = $item;
                }
            }
        }

        foreach ($node as $value) {
            if (is_array($value)) {
                $this->walkRecords($value, $records);
            }
        }
    }

    /**
     * @param array<string, mixed> $record
     */
    private function looksLikeMeasurementRecord(array $record): bool
    {
        return $this->extractMeasuredAtUtc($record) !== null;
    }

    /**
     * @param array<mixed> $value
     */
    private function isListOfArrays(array $value): bool
    {
        if ($value === [] || array_keys($value) !== range(0, count($value) - 1)) {
            return false;
        }

        foreach ($value as $item) {
            if (! is_array($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $record
     */
    private function extractMeasuredAtUtc(array $record): ?string
    {
        $timestamp = $this->extractValueByKeys($record, ['ts2', 'timestamp', 'unix_timestamp', 'epoch', 'time_unix']);
        if ($timestamp !== null) {
            return $this->normalizeDateTimeValue($timestamp);
        }

        $dateTime = $this->extractValueByKeys($record, [
            'measured_at_utc',
            'measured_at',
            'datetime_utc',
            'datetime',
            'date_time',
            'waktu',
            'tanggal_waktu',
        ]);

        if ($dateTime !== null) {
            return $this->normalizeDateTimeValue($dateTime);
        }

        $date = $this->extractValueByKeys($record, ['date', 'tanggal', 'tgl']);
        $time = $this->extractValueByKeys($record, ['jam', 'hour', 'time']);

        if ($date !== null && $time !== null) {
            return $this->normalizeDateTimeValue(trim((string) $date . ' ' . (string) $time));
        }

        return null;
    }

    /**
     * @param array<string, mixed> $record
     * @param list<string> $candidateKeys
     */
    private function extractNumericValue(array $record, array $candidateKeys): ?string
    {
        $value = $this->extractValueByKeys($record, $candidateKeys);
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = trim((string) $value);
        $normalized = str_replace(',', '.', $normalized);

        if (! is_numeric($normalized)) {
            return null;
        }

        return number_format((float) $normalized, 3, '.', '');
    }

    /**
     * @param array<string, mixed> $record
     * @param list<string> $candidateKeys
     */
    private function extractStringValue(array $record, array $candidateKeys): ?string
    {
        $value = $this->extractValueByKeys($record, $candidateKeys);
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }

    /**
     * @param array<string, mixed> $record
     * @param list<string> $candidateKeys
     * @return mixed
     */
    private function extractValueByKeys(array $record, array $candidateKeys)
    {
        $flattened     = $this->flattenRecord($record);
        $candidateKeys = array_map([$this, 'normalizeKey'], $candidateKeys);

        foreach ($candidateKeys as $candidate) {
            foreach ($flattened as $key => $value) {
                if ($key === $candidate || str_ends_with($key, '.' . $candidate)) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private function normalizeDateTimeValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $timestamp = (int) $value;
            if ($timestamp > 9999999999) {
                $timestamp = (int) floor($timestamp / 1000);
            }

            return gmdate('Y-m-d H:i:s', $timestamp);
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $sourceTimezone = new DateTimeZone($this->config->sourceTimezone);
        $utcTimezone    = new DateTimeZone('UTC');

        $formats = [
            DateTimeInterface::ATOM,
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y/m/d H:i:s',
            'Y/m/d H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'Y-m-d',
            'Y/m/d',
            'd-m-Y',
            'd/m/Y',
        ];

        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $raw, $sourceTimezone);
            if ($date instanceof DateTimeImmutable) {
                return $date->setTimezone($utcTimezone)->format('Y-m-d H:i:s');
            }
        }

        try {
            return (new DateTimeImmutable($raw, $sourceTimezone))
                ->setTimezone($utcTimezone)
                ->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private function flattenRecord(array $record, string $prefix = ''): array
    {
        $flat = [];

        foreach ($record as $key => $value) {
            $normalizedKey = is_string($key) ? $this->normalizeKey($key) : (string) $key;
            $currentKey    = $prefix !== '' ? $prefix . '.' . $normalizedKey : $normalizedKey;

            if (is_array($value)) {
                $flat += $this->flattenRecord($value, $currentKey);
                continue;
            }

            $flat[$currentKey] = $value;
        }

        return $flat;
    }

    private function normalizeKey(string $key): string
    {
        $normalized = strtolower(trim($key));

        return preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? $normalized;
    }

    private function normalizeStationCode(string $stationCode): string
    {
        return strtoupper(trim($stationCode));
    }

    private function normalizeInputDate(string $date): string
    {
        $dateObject = DateTimeImmutable::createFromFormat('Y-m-d', $date, new DateTimeZone($this->config->sourceTimezone));
        $errors     = DateTimeImmutable::getLastErrors();

        if (
            ! $dateObject instanceof DateTimeImmutable
            || ($errors !== false && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0))
        ) {
            throw new RuntimeException('Invalid date format. Use YYYY-MM-DD.');
        }

        return $dateObject->format('Y-m-d');
    }

    private function getCurrentSourceDate(): string
    {
        return (new DateTimeImmutable('now', new DateTimeZone($this->config->sourceTimezone)))->format('Y-m-d');
    }

    private function looksLikeHtmlResponse(string $body): bool
    {
        $trimmed = strtolower(ltrim($body));

        return str_starts_with($trimmed, '<!doctype html')
            || str_starts_with($trimmed, '<html')
            || stripos($body, 'harap login') !== false
            || stripos($body, '<body') !== false;
    }

    /**
     * @param mixed $payload
     */
    private function looksLikeErrorPayload($payload): bool
    {
        return is_array($payload)
            && isset($payload['message'])
            && is_string($payload['message'])
            && (
                isset($payload['exception'])
                || isset($payload['code'])
                || strtolower((string) ($payload['status'] ?? '')) === 'error'
            );
    }

    /**
     * @return array{name: string, value: string}|null
     */
    private function extractCsrfToken(string $html): ?array
    {
        if (preg_match('/<input[^>]+name=["\']([^"\']*(?:csrf|token)[^"\']*)["\'][^>]+value=["\']([^"\']+)["\']/i', $html, $matches) === 1) {
            return [
                'name'  => $matches[1],
                'value' => $matches[2],
            ];
        }

        if (preg_match('/<meta[^>]+name=["\']csrf-token["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches) === 1) {
            return [
                'name'  => '_token',
                'value' => $matches[1],
            ];
        }

        return null;
    }
}
