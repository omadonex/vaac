<?php

namespace Omadonex\Vaac\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Omadonex\Support\Traits\CustomizeMailable;
use Omadonex\Vaac\VaacService;

class VaacVerifyMail extends Mailable
{
    use Queueable, SerializesModels, CustomizeMailable;

    public $user;
    public $token;
    private $activate;

    /**
     * VaacVerifyMail constructor.
     * @param $user (User for sending notification)
     * @param $token (Verification token)
     * @param $activate (is User needs activation)
     */
    public function __construct($user, $token, $activate)
    {
        $this->user = $user;
        $this->token = $token;
        $this->activate = $activate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //This keys uses for determining whether or not is activation account process
        //It changes messages in notification by this key
        //If you want to use your own notification with own markdown use this key for getting translated strings for current verification of activation process
        $key = ($this->activate) ? 'activate' : 'verify';
        $fieldUsername = config('vaac.field_username');

        return $this
            ->subject(__("vaac::email.$key.subject"))
            ->useDefaultGreeting($this->user->$fieldUsername)
            ->addLine(__("vaac::email.$key.info"))
            ->addAction(VaacService::getVerifyEmailUrl($this->token), __("vaac::email.$key.button"))
            ->addLine(__("vaac::email.$key.attention"))
            ->markdown('support::emails.layout');
    }
}
