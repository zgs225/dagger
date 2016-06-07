<?php

use Youplus\Foundation\Mutex;

class MutexTest extends PHPUnit_Framework_TestCase
{
    public function testAcquireLock()
    {
        $metux  = Mutex::redisMutexInstance();
        $lockId = 'test-lock-1';

        $metux->release($lockId);

        $locked = $metux->acquire($lockId);
        $this->assertTrue($locked);

        $locked = $metux->acquire($lockId);
        $this->assertFalse($locked);

        $metux->release($lockId);
        $locked = $metux->acquire($lockId);
        $this->assertTrue($locked);

        $metux->release($lockId);
    }
}
