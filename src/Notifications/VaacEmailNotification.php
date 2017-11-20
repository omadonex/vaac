<?php

namespace Omadonex\Vaac\Notifications;

use Illuminate\Mail\Mailable;
use Omadonex\Vaac\Mail\VaacVerifyMail;

class VaacEmailNotification extends VaacNotification
{
    /**
     * @param $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * @param $notifiable
     * @return Mailable
     */
    public function toMail($notifiable)
    {
        $activate = !$this->user->isActivated();

        return (new VaacVerifyMail($this->user, $this->verify->token, $activate))
            ->to($this->verify->value);
    }
}
