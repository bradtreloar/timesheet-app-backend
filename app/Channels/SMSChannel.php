<?php

namespace App\Channels;

use App\Contracts\SMSNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class SMSChannel
{
    public function __construct()
    {
        $this->username = Config::get('sms.username');
        $this->password = Config::get('sms.password');
        $this->from = Config::get('sms.from');
    }

    public function send($notifiable, SMSNotification $notification)
    {
        $message = $notification->toSMS($notifiable);
        $to = $notifiable->routeNotificationForSMS($notification);
        if ($to) {
            Http::post("https://api.smsbroadcast.com.au/api-adv.php", [
                'query' => [
                    'username' => $this->username,
                    'password' => $this->password,
                    'to' => $to,
                    'from' => $this->from,
                    'message' => $message,
                ],
            ]);
        }
    }
}
