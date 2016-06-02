<?php namespace Youplus\Foundation\Redis;

use Youplus\Exceptions\ExtensionNotInstallException;

class YouplusRedis
{
    /**
     * 实际上的redis
     *
     * @var \Redis
     **/
    protected $redis;

    /**
     * 主机
     *
     * @var string
     **/
    protected $host;

    /**
     * 端口
     *
     * @var integer
     **/
    protected $port;

    /**
     * 是否使用连接池
     *
     * @var bool
     **/
    protected $usePool;

    /**
     * 连接池大小
     *
     * @var integer
     **/
    protected $poolSize;

    function __construct($host='127.0.0.1', $port=6379, $usePool=false, $poolSize=10)
    {
        if (! class_exists('\Redis')) {
            throw ExtensionNotInstallException('phpredis扩展没有安装');
        }

        $this->redis    = new \Redis();
        $this->host     = $host;
        $this->port     = $port;
        $this->usePool  = $usePool;
        $this->poolSize = $poolSize;
    }

    /**
     * 将对于redis的调用转发给redis
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     **/
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array(array($this, $name), $arguments);
        }

        if ($this->open()) {
            return call_user_func_array(array($this->redis, $name), $arguments);
        }
    }

    /**
     * 打开Redis连接
     *
     * @return bool
     **/
    protected function open()
    {
        $connected = false;

        try {
            $this->redis->ping();
            $connected = true;
        } catch (\RedisException $e) {
            $connected = $this->_open();
        }

        return $connected;
    }

    /**
     * 打开Redis连接
     *
     * @return bool
     **/
    protected function _open()
    {
        if ($this->usePool) {
            return $this->_popen();
        } else {
            return $this->redis->connect($this->host, $this->port);
        }
    }

    /**
     * 打开永久连接的Redis连接
     *
     * @return bool
     **/
    protected function _popen()
    {
        if ($this->poolSize < 1) {
            $this->poolSize = 1;
        }

        $cid = rand(0, $this->poolSize - 1);

        return $this->redis->pconnect($this->host, $this->port, 0, $cid);
    }

    /**
     * 关闭Redis连接
     **/
    protected function close()
    {
        if (! $this->usePool) {
            try {
                $this->redis->close();
            } catch (\Exception $e) {};
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
