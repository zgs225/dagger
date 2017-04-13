<?php namespace Youplus\Queue;

use Webpatser\Uuid\Uuid;
use Youplus\Foundation\Redis\YouplusRedis;

class MataqClient {
    const DEFAULT_QUEUE = "mataq:default";

    protected $redisClient;

    public function __construct($host, $port)
    {
        $this->redisClient = new YouplusRedis($host, $port);
    }

    public function dispatch($event, $data = null)
    {
        $uuid = Uuid::generate(4);
        $msg = [
            "id"        => $uuid->string,
            "event"     => $event,
            "timestamp" => time(),
            "try"       => 1,
            "data"      => $data
        ];
        $this->redisClient->rpush(self::DEFAULT_QUEUE, json_encode($msg));
    }
}

?>
