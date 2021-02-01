<?php
declare(strict_types=1);
namespace Cache;

/**
 * A cache wrapper, no time to implement so just a dummy class for now :(
 */
class Cache
{

    public function __construct()
    {
    }

    /**
     * @param string $key
     * @param string $value
     * @param int $ttl
     */
    public function set(string $key, string $value, int $ttl = 3600): void
    {
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return null;
    }
}
