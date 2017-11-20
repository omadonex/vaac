<?php

namespace Omadonex\Vaac\Notifications;

use Illuminate\Notifications\Messages\NexmoMessage;

class VaacPhoneNotification extends VaacNotification
{
    /**
     * @param $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['nexmo'];
    }

    /**
     * @param $notifiable
     * @return NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
            ->content($this->getSmsMessage())
            ->unicode();
    }
}
