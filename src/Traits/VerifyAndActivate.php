<?php

namespace Omadonex\Vaac\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Omadonex\Vaac\Models\VaacUserVerify;
use Omadonex\Vaac\Notifications\VaacEmailNotification;
use Omadonex\Vaac\Notifications\VaacPhoneNotification;
use Omadonex\Vaac\VaacService;

trait VerifyAndActivate
{
    /**
     * Marks current user as activated
     *
     * @return void
     */
    public function activate()
    {
        $this->activated = true;
        $this->save();
    }

    /**
     * Checks is current user activated
     *
     * @return mixed
     */
    public function isActivated()
    {
        return $this->activated;
    }

    /**
     * Relationship - all user verifies
     *
     * @return HasMany
     */
    public function vaacUserVerifies()
    {
        return $this->hasMany(VaacUserVerify::class);
    }

    /**
     * Getting current phone for sending sms notification. Here can be two different phones:
     * First - current activated user phone.
     * Second - a new phone that user changed in his profile and want to verify him.
     * This method can be used for receiving current phone for setup in "routeNotificationFor..." method on User model
     *
     * @return mixed
     */
    public function vaacPhoneForNotification()
    {
        $phoneVerify = $this->vaacUserVerifies()->phone()->first();

        return $phoneVerify->value ?: $this->vaacGetFieldValue(VaacService::METHOD_PHONE);
    }

    /**
     * Getting actual status of verifying data. Returns array of pairs (method, [status, value])
     * Status contains current status of verification by method.
     * Value contains not approved new E-mail of Mobile depends on method or Null value if no need verification by this method.
     * This data can be uses for building "Profile Page" where user can check status of verifying and can request sending verification instructions again.
     *
     * @return array
     */
    public function vaacVerifyStatusData()
    {
        $verifies = $this->vaacUserVerifies()->latest()->get();
        $emailVerify = $verifies->where('method', VaacService::METHOD_EMAIL)->first();
        $phoneVerify = $verifies->where('method', VaacService::METHOD_PHONE)->first();

        return [
            VaacService::METHOD_EMAIL => [
                'status' => $emailVerify ? VaacService::PROCESS : VaacService::VERIFIED,
                'value' => $emailVerify ? $emailVerify->value : null,
            ],
            VaacService::METHOD_PHONE => [
                'status' => $phoneVerify ? VaacService::PROCESS : VaacService::VERIFIED,
                'value' => $phoneVerify ? $phoneVerify->value : null,
            ],
        ];
    }

    /**
     * Creates a new Verification record and sends notification for user
     *
     * @param $method
     * @param null $value
     */
    public function vaacVerify($method, $value = null)
    {
        if ($method == VaacService::METHOD_PHONE) {
            $this->vaacUserVerifies()->phone()->delete();
        }

        $verify = new VaacUserVerify();
        $verify->user_id = $this->id;
        $verify->token = VaacService::generateToken($method);
        $verify->value = $value ?: $this->vaacGetFieldValue($method);
        $verify->method = $method;
        $verify->save();

        //You can use your own notification classes for sending verification instructions
        //if notification_class is set in config and needed class exists
        $userNotificationClass = "App\\Notifications\\".config("vaac.$method.notification_class");
        if ($userNotificationClass && class_exists($userNotificationClass)) {
            $notification = new $userNotificationClass($this, $verify);
        } elseif ($method == VaacService::METHOD_EMAIL) {
            $notification = new VaacEmailNotification($this, $verify);
        } elseif ($method == VaacService::METHOD_PHONE) {
            $notification = new VaacPhoneNotification($this, $verify);
        } else {
            $notification = null;
        }

        $this->notify($notification);
    }

    /**
     * Returns field value of User model (email, phone)  by method
     *
     * @param $method
     * @return mixed
     */
    public function vaacGetFieldValue($method)
    {
        $field = VaacService::getFieldName($method);

        return $this->$field;
    }

    /**
     * Count of today attempts of verification by method
     *
     * @param $method
     * @return int
     */
    public function vaacGetCountTodayAttempts($method)
    {
        return $this->vaacUserVerifies()
            ->byMethod($method)
            ->withTrashed()
            ->today()
            ->count();
    }
}
