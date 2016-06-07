<?php namespace Youplus\Foundation;

use Youplus\Contract\Mutex\Store;
use Youplus\Foundation\Mutex\RedisStore;

/**
 * 用于锁并发
 **/
class Mutex
{
    /**
     * 用来保存锁存储的对象
     *
     * @var \Youplus\Contract\Mutex\Store
     **/
    protected $store;

    /**
     * 锁保存时间
     *
     * @var integer
     **/
    protected $ttl = 300; // 5 min

    function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * 获取mutex实例
     *
     * @return $this
     **/
    public static function redisMutexInstance()
    {
        $store = new RedisStore();

        return new Mutex($store);
    }

    /**
     * 获取互斥锁
     *
     * @return boolean
     **/
    public function acquire($lockId)
    {
        return $this->store->set($lockId, $this->ttl);
    }

    /**
     * 释放互斥锁
     *
     * @return void
     **/
    public function release($lockId)
    {
        $this->store->remove($lockId);
    }

    protected function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }
}
