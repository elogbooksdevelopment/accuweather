<?php

require_once 'RedisCache.php';

set_exception_handler(array('Accuweather', 'exceptionHandler'));

class Accuweather
{
    protected $cache;
    protected $config;
    protected $locationKey;
    protected $baseUrl = 'http://dataservice.accuweather.com';

    protected $path;
    protected $parameters;
    protected $httpQueryString;

    protected $timeouts = [
        '/\/locations\/v1\/postalcodes\/search/' => 604800
    ];

    public function __construct(
        string $path,
        array $parameters,
        string $password = null
    )
    {
        $this->path = $path;
        $this->parameters = $parameters;

        $this->getConfig();
        $this->authenticate($password);
        $this->setApiKey();
        $this->buildHttpQueryString();
        $this->process();
    }

    private function getConfig(): void
    {
        $this->config = json_decode(file_get_contents('config.json'));
    }

    private function setApiKey(): void
    {
        $this->parameters['apikey'] = $this->config->apiKey ?? null;
    }

    private function buildHttpQueryString(): void
    {
        $this->httpQueryString = http_build_query($this->parameters);
    }

    private function authenticate(?string $password): void
    {
        if (isset($this->config->password) && $this->config->password !== $password) {
            throw new \Exception('Not authenticated', 401);
        }
    }

    private function process()
    {
        $cacheKey = $this->getCacheKey();
        $response = $this->getCachedResponse($cacheKey);

        if ($response) {
            return $this->processResponse([
                'code' => 200,
                'response' => json_decode($response)
            ]);
        }

        $url = $this->baseUrl . $this->path . '?' . $this->httpQueryString;
        $timeout = $this->getTimeout();
        $response = file_get_contents($this->baseUrl . $this->path . '?' . $this->httpQueryString);

        $this->cache->setWithExpiration($cacheKey, $timeout, $response);

        $this->processResponse([
            'code' => 200,
            'response' => json_decode($response),
        ]);
    }

    private static function processResponse(array $response)
    {
        http_response_code($response['code'] ?: 400);

        die(json_encode($response));
    }

    private function getTimeout(): int
    {
        foreach ($this->timeouts as $pattern => $timeout) {
            if (preg_match($pattern, $this->path)) {
                return $timeout;
            }
        }

        return 3600;
    }

    private function getCache(): Cache
    {
        if ($this->cache) {
            return $this->cache;
        }

        switch ($this->config->cache) {
            case 'redis':
                $this->cache = new RedisCache(
                    $this->config->redisHost,
                    $this->config->redisPort,
                    $this->config->redisPassword
                );
                break;

            default:
                throw new \Exception('Invalid cache specified', 500);
                break;
        }

        return $this->cache;
    }

    private function getCacheKey(): string
    {
        return hash('sha256', $this->path . $this->httpQueryString);
    }

    private function getCachedResponse(string $key)
    {
        $this->getCache();

        return $this->cache->get($key);
    }

    public static function exceptionHandler(\Exception $exception = null): void
    {
        self::processResponse([
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
        ]);
    }
}
