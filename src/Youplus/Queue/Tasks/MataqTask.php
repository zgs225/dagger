<?php namespace Youplus\Queue\Tasks;

use Youplus\Queue\MataqClient;

abstract class MataqTask {
    protected $mataqClient;

    public function __construct($host, $port)
    {
        $this->mataqClient = new MataqClient($host, $port);
    }
}

?>
