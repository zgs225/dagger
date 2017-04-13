<?php

use Youplus\Queue\MataqClient;
use Youplus\Queue\Tasks\MiPushTask;
use Youplus\Queue\Tasks\MamcHuaweiPushTask;

class MataqClientTest extends PHPUnit_Framework_TestCase
{
    const HOST = "127.0.0.1";
    const PORT = 6379;

    public function testDispatch()
    {
        $mataq = new MataqClient(self::HOST, self::PORT);
        $mataq->dispatch("hello", "yuez");
    }

    public function testMiPush()
    {
        $task = new MiPushTask(self::HOST, self::PORT);
        $task->dispatch("miui_dev_4", "你好", "你好");
    }

    public function testHuaweiPush()
    {
        $task = new MamcHuaweiPushTask(self::HOST, self::PORT);
        $task->dispatch("huawei_dev_4", "你好", "你好");
    }
}

?>
