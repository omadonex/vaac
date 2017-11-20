<?php

namespace Omadonex\Vaac\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VaacNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $verify;

    /**
     * VaacNotification constructor.
     * @param $user
     * @param $verify
     */
    public function __construct($user, $verify)
    {
        $this->user = $user;
        $this->verify = $verify;
    }

    /**
     * Returns common message with code, can be used in child class
     *
     * @return string
     */
    protected function getSmsMessage()
    {
        return __('vaac::common.verify.phone.sms', ['token' => $this->verify->token]);
    }
}
