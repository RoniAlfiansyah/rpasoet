<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Tides extends BaseConfig
{
    public string $baseUrl = 'https://srgi.big.go.id';

    public string $bootstrapPath = '/tides';

    public string $realtimePath = '/tides-data-realtime';

    public string $datedPathTemplate = '/tides_data/pasut_{station_code}';

    public string $accessTokenPath = '/tides/access-token';

    public string $sourceTimezone = 'Asia/Jakarta';

    public int $connectTimeout = 10;

    public int $timeout = 20;

    public int $maxRetries = 3;

    public int $retryDelayMs = 1000;

    public int $dataRequestDelayMs = 1200;

    public int $rateLimitMaxRetries = 0;

    public int $rateLimitBaseDelayMs = 2000;

    public int $rateLimitMaxDelayMs = 30000;

    /**
     * @var list<string>
     */
    public array $stations = [
        'ULSU',
        'PLHR',
    ];

    public ?string $loginPath = null;

    public ?string $username = null;

    public ?string $password = null;

    public string $usernameField = 'email';

    public string $passwordField = 'password';

    public ?string $loginSubmitField = null;

    public ?string $loginSubmitValue = null;

    public function __construct()
    {
        parent::__construct();

        $this->baseUrl           = (string) env('tides.baseUrl', $this->baseUrl);
        $this->bootstrapPath     = (string) env('tides.bootstrapPath', $this->bootstrapPath);
        $this->realtimePath      = (string) env('tides.realtimePath', $this->realtimePath);
        $this->datedPathTemplate = (string) env('tides.datedPathTemplate', $this->datedPathTemplate);
        $this->accessTokenPath   = (string) env('tides.accessTokenPath', $this->accessTokenPath);
        $this->sourceTimezone    = (string) env('tides.sourceTimezone', $this->sourceTimezone);
        $this->connectTimeout    = (int) env('tides.connectTimeout', $this->connectTimeout);
        $this->timeout           = (int) env('tides.timeout', $this->timeout);
        $this->maxRetries        = (int) env('tides.maxRetries', $this->maxRetries);
        $this->retryDelayMs      = (int) env('tides.retryDelayMs', $this->retryDelayMs);
        $this->dataRequestDelayMs = (int) env('tides.dataRequestDelayMs', $this->dataRequestDelayMs);
        $this->rateLimitMaxRetries = (int) env('tides.rateLimitMaxRetries', $this->rateLimitMaxRetries);
        $this->rateLimitBaseDelayMs = (int) env('tides.rateLimitBaseDelayMs', $this->rateLimitBaseDelayMs);
        $this->rateLimitMaxDelayMs = (int) env('tides.rateLimitMaxDelayMs', $this->rateLimitMaxDelayMs);
        $this->loginPath         = env('tides.loginPath') ?: $this->loginPath;
        $this->username          = env('tides.username') ?: $this->username;
        $this->password          = env('tides.password') ?: $this->password;
        $this->usernameField     = (string) env('tides.usernameField', $this->usernameField);
        $this->passwordField     = (string) env('tides.passwordField', $this->passwordField);
        $this->loginSubmitField  = env('tides.loginSubmitField') ?: $this->loginSubmitField;
        $this->loginSubmitValue  = env('tides.loginSubmitValue') ?: $this->loginSubmitValue;

        $stations = env('tides.stations');
        if (is_string($stations) && trim($stations) !== '') {
            $this->stations = array_values(array_filter(array_map(
                static fn (string $station): string => strtoupper(trim($station)),
                explode(',', $stations),
            )));
        }
    }

    /**
     * @return list<string>
     */
    public function getStations(): array
    {
        return array_values(array_unique(array_map(
            static fn (string $station): string => strtoupper(trim($station)),
            $this->stations,
        )));
    }

    public function shouldAuthenticate(): bool
    {
        return $this->loginPath !== null
            && $this->loginPath !== ''
            && $this->username !== null
            && $this->username !== ''
            && $this->password !== null
            && $this->password !== '';
    }
}
