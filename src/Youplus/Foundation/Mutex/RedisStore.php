<?php namespace Youplus\Foundation\Mutex;

use Youplus\Contract\Mutex\Store as StoreContract;
use Youplus\Foundation\Redis\YouplusRedis;

/**
 * 用Redis保存互斥锁
 **/
class RedisStore implements StoreContract
{
    /**
     * redis client
     *
     * @var Youplus\Foundation\Redis\YouplusRedis
     **/
    protected $redis;

    function __construct()
    {
        $this->redis = new YouplusRedis();
    }

    public function set($lockId, $ttl = -1)
    {
        if ($this->redis->exists($lockId)) {
            return false;
        }
        return $this->redis->set($lockId, 1, $ttl);
    }

    public function remove($lockId)
    {
        $this->redis->del($lockId);
    }
}
