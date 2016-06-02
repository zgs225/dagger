<?php
use Youplus\Foundation\Redis\YouplusRedis;

class YouplusRedisTest extends PHPUnit_Framework_TestCase
{
    protected $redis;

    /**
     * @before
     */
    public function setup()
    {
        $this->redis = new YouplusRedis();
    }

    public function testUsage()
    {
        $this->redis->set('youplus-redis', 1);
        $value = $this->redis->get('youplus-redis');

        $this->assertEquals(1, $value);

        $this->redis->delete('youplus-redis');

        $this->assertFalse($this->redis->get('youplus-redis'));
    }

    public function testPerformance()
    {
        $now = time();

        for ($i = 0; $i < 10000; $i++) {
            $this->redis->set('youplus-redis', $i);
        }

        $timedelta = time() - $now;

        echo "\nIt tooks {$timedelta} seconds to exec 10000 loops";

        $this->assertEquals(9999, $this->redis->get('youplus-redis'));

        $this->redis->delete('youplus-redis');
    }

    public function testPopen()
    {
        $redis = new YouplusRedis('127.0.0.1', 6379, true);

        $redis->set('youplus-redis', 1);
        $value = $redis->get('youplus-redis');

        $this->assertEquals(1, $value);

        $redis->delete('youplus-redis');

        $this->assertFalse($this->redis->get('youplus-redis'));
    }

    public function testPopenPerformance()
    {
        $redis = new YouplusRedis('127.0.0.1', 6379, true);

        $now = time();

        for ($i = 0; $i < 10000; $i++) {
            $redis->set('youplus-redis', $i);
        }

        $timedelta = time() - $now;

        echo "\nIt tooks {$timedelta} seconds to exec 10000 loops";

        $this->assertEquals(9999, $redis->get('youplus-redis'));

        $redis->delete('youplus-redis');
    }
}
