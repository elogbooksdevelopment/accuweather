<?php

require_once 'Cache.php';

class RedisCache extends Cache
{
    private $host;
    private $port;
    private $password;
    private $instance;

    public function __construct(
        string $host = '127.0.0.1',
        int $port = 6379,
        string $password = null
    )
    {
        $this->instance = new Redis;

        $this->host = $host;
        $this->port = $port;
        $this->password = $password;

        $this->connect();
    }

    public function connect(): void
    {
        $this->instance->connect(
            $this->host,
            $this->port
        );

        if ($this->password) {
            $this->instance->auth($this->password);
        }
    }

    public function get(string $key)
    {
        return $this->instance->get($key);
    }

    public function set(string $key, $value): Cache
    {
        $this->instance->set($key, $value);

        return $this;
    }

    public function setWithExpiration(string $key, int $timeout, $value): Cache
    {
        $this->instance->setEx($key, $timeout, $value);

        return $this;
    }

    public function increment(string $key): int
    {
        return $this->instance->incr($key);
    }

    public function decrement(string $key): int
    {
        return $this->instance->decr($key);
    }
}
