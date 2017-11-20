<?php

namespace Omadonex\Vaac;

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class VaacService
{
    const METHOD_EMAIL = 'email';
    const METHOD_PHONE = 'phone';

    const VERIFY_LOCKED = -1;

    const VERIFIED = 1;
    const PROCESS = 2;

    /**
     * Generates all required routes.
     * This call can be placed in routes web file in Localize middleware for supporting translations
     *
     * @return void
     */
    public static function routes()
    {
        $namespace = "\\Omadonex\\Vaac\\Http\\Controllers\\";
        Route::group(['middleware' => 'auth'], function () use ($namespace) {
            Route::any('/vaac/verify/{method}', ['as' => 'vaac.verify', 'uses' => $namespace.'VaacController@verify']);
            Route::get('/vaac/resend/{method}', ['as' => 'vaac.resend', 'uses' => $namespace.'VaacController@resend']);
            Route::post('/vaac/change/{method}', ['as' => 'vaac.change', 'uses' => $namespace.'VaacController@change']);
        });
    }

    /**
     * Helper for generating verify url for email
     *
     * @param $token
     * @return string
     */
    public static function getVerifyEmailUrl($token)
    {
        return route('vaac.verify', self::METHOD_EMAIL).'?token='.$token;
    }

    /**
     * Helper for generating verify url for phone
     *
     * @return string
     */
    public static function getVerifyPhoneUrl()
    {
        return route('vaac.verify', self::METHOD_PHONE);
    }

    /**
     * Generates token based on token_length described in config file
     *
     * @param $method
     * @return int|null|string
     */
    public static function generateToken($method)
    {
        $length = config("vaac.$method.token_length");
        switch ($method) {
            case self::METHOD_EMAIL:
                return str_random($length);
            case self::METHOD_PHONE:
                return mt_rand(10 ** ($length - 1), 10 ** $length - 1);
            default:
                return null;
        }
    }

    /**
     * Initializes activation process for a new registered account
     * It checks all available methods in config file and generates verifications
     * It also checks if required field is filled in database
     *
     * @return void
     */
    public static function initActivation($user)
    {
        foreach (config('vaac.methods') as $method) {
            //This check is optional cause it assumes that in config file listed only right methods and required fields exists on User model
            if ($user->vaacGetFieldValue($method) != null) {
                $user->vaacVerify($method);
            }
        }
    }

    /**
     * Returns field name of User model (email, phone) by method
     * Name of the field must be described in config
     *
     * @param $method
     * @return string
     */
    public static function getFieldName($method)
    {
        return config("vaac.$method.field");
    }

    /**
     * Returns a validation rule for field by method
     * It needs where user want to change (email or phone) and inputs a new data
     *
     * @param $method
     * @return mixed
     */
    public static function getFieldRule($method)
    {
        return config("vaac.$method.rule");
    }

    /**
     * This method checks total count of today attempts and current attempts and return diff in seconds for a new attempt
     *
     * @param $method
     * @return int
     */
    public static function getFreezeTime($user, $method)
    {
        //count of today attempts more than max than status LOCKED
        if ($user->vaacGetCountTodayAttempts($method) >= config("vaac.$method.attempts")) {
            return VaacService::VERIFY_LOCKED;
        }

        //no verification found then can generate new one so 0
        $vaacVerify = $user->vaacUserVerifies()->byMethod($method)->latest()->first();
        if (!$vaacVerify) {
            return 0;
        }

        $diff = config("vaac.$method.freeze") - Carbon::now()->diffInSeconds($vaacVerify->created_at);

        return ($diff > 0) ? $diff : 0;
    }
}