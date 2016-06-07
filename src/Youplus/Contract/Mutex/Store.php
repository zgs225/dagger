<?php namespace Youplus\Contract\Mutex;

interface Store
{
    /**
     * 保存锁
     *
     * @param string $lockId
     * @param integer $ttl
     * @return boolean
     **/
    public function set($lockId, $ttl = -1);

    /**
     * 删除锁
     *
     * @param string $lockId
     * @return boolean
     **/
    public function remove($lockId);
}
