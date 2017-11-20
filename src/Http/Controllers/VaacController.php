<?php

namespace Omadonex\Vaac\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Omadonex\Support\Services\ResponseUtils;
use Omadonex\Vaac\VaacService;

class VaacController extends Controller
{
    /**
     * Checks verification token based on method and activates or approves user account or info (E-mail, Mobile)
     *
     * @param $method
     * @param Request $request
     * @return mixed
     */
    public function verify($method, Request $request)
    {
        //Verification by E-mail is GET, by Mobile is POST. Checking right methods
        if (
            ($request->isMethod('get') && ($method != VaacService::METHOD_EMAIL))
            || ($request->isMethod('post') && ($method != VaacService::METHOD_PHONE))
        ) {
            abort(404);
        }

        $user = auth()->user();
        $verify = $user->vaacUserVerifies()->byMethod($method)->byToken($request->token)->first();

        //Aborting if no verification exists with specified token
        if (!$verify) {
            abort(404);
        }

        $messageKey = "vaac::common.verify.$method.ok";
        //Activate user if not activated. Enough only one first verification (no matter E-mail of Mobile)
        if (!$user->isActivated()) {
            $user->activate();
            $messageKey = 'vaac::common.verify.ok';
        }

        //Checking if user changed E-mail or Mobile and updating it cause on this step verification passes
        $field = VaacService::getFieldName($method);
        if ($user->$field != $verify->value) {
            $user->$field = $verify->value;
            $user->save();
        }

        //Marking current verification as used
        $verify->markUsed();

        //Deleting all other verifications by this method
        $user->vaacUserVerifies()->byMethod($method)->delete();

        //return back with successful message (activation or verification)
        return ResponseUtils::actionBack(__($messageKey));
    }

    /**
     * Sends verification instructions based on method if user requested them again
     *
     * @param $method
     * @return mixed
     */
    public function resend($method)
    {
        $user = auth()->user();
        $verify = $user->vaacUserVerifies()->byMethod($method)->latest()->first();

        //When user requests resending verification we have at least one verification record in database
        //By this verification we receives a value of new (not approved) E-mail or Mobile
        //So Aborting if not
        if (!$verify) {
            abort(404);
        }

        //Checking Attempts
        $data = $this->checkAttempts($method);
        if ($data['send']) {
            $user->vaacVerify($method, $verify->value);
        }

        //Return back with message (generated in checkAttempts)
        return ResponseUtils::actionBack($data['message'], $data['status']);
    }

    /**
     * Validates data (new E-mail or Mobile) and sends verification instructions based on method
     * It applies a validation rules for data based on config file
     *
     * @param $method
     * @param Request $request
     * @return mixed
     */
    public function change($method, Request $request)
    {
        $user = auth()->user();

        //Checking Attempts
        $data = $this->checkAttempts($method);
        if ($data['send']) {
            $field = VaacService::getFieldName($method);

            //Getting specified rule for validation a new value (it must be unique on `users` and must passes validation email|mobile)
            $rule = VaacService::getFieldRule($method);
            $fieldRules = "required|unique:users";
            if ($rule != '') {
                $fieldRules .= "|$rule";
            }

            //Validate data
            $request->validate([
                $field => $fieldRules,
            ]);

            //Validation passes - generating new verification
            $user->vaacVerify($method, $request->$field);
        }

        //Return back with message (generated in checkAttempts)
        return ResponseUtils::actionBack($data['message'], $data['status']);
    }

    /**
     * Checks count of attempts of verification by method and returns array with status data
     *
     * @param $method
     * @return array
     */
    private function checkAttempts($method)
    {
        $user = auth()->user();
        $send = false;
        $freezeTime = VaacService::getFreezeTime($user, $method);
        if ($freezeTime == VaacService::VERIFY_LOCKED) {
            //Total count of today attempts equal max count attempts defined in config
            $message = __("vaac::common.verify.$method.locked", ['attempts' => config("vaac.$method.attempts")]);
            $status = ResponseUtils::STATUS_ERROR;
        } elseif ($freezeTime == 0) {
            //All ok
            $message = __("vaac::common.verify.$method.send");
            $status = ResponseUtils::STATUS_SUCCESS;
            $send = true;
        } else {
            //Have a freeze time need to wait some seconds
            $message = __("vaac::common.verify.$method.freeze", ['seconds' => $freezeTime]);
            $status = ResponseUtils::STATUS_INFO;
        }

        return [
            'send' => $send,
            'status' => $status,
            'message' => $message,
        ];
    }
}
