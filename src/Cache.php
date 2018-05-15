<?php

abstract class Cache
{
    abstract public function connect(): void;
    abstract public function get(string $key);
    abstract public function set(string $key, $value): Cache;
    abstract public function setWithExpiration(string $key, int $timeout, $value): Cache;
    abstract public function increment(string $key): int;
    abstract public function decrement(string $key): int;
}
