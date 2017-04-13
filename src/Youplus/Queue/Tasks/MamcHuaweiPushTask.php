<?php namespace Youplus\Queue\Tasks;

class MamcHuaweiPushTask extends MataqTask
{
    public function dispatch($topic, $title, $description, $payload = null)
    {
        $msg = [
            "push_type" => 3,
            "android" => [
                "notification_title" => $title,
                "notification_content" => $description,
                "extra" => $payload,
                "doings" => 1
            ],
            "tags" => [
                "tags" => [
                    [
                        "default" => [$topic]
                    ]
                ]
            ]
        ];

        $this->mataqClient->dispatch("mamc_huawei_push", $msg);
    }

}

?>
