<?php namespace Youplus\Queue\Tasks;

class MiPushTask extends MataqTask
{
    public function dispatch($topic, $title, $description, $payload = null)
    {
        $package = 'cc.youplus.mamc';
        $notifyType = -1;

        $msg = [
            "restricted_package_name" => $package,
            "title"                   => $title,
            "description"             => $description,
            "topic"                   => $topic,
            "payload"                 => $payload,
            "notify_type"             => $notifyType
        ];

        $this->mataqClient->dispatch("mipush", $msg);
    }

}

?>
