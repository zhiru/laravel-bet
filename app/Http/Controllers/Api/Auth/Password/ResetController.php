<?php
namespace Aireset\Http\Controllers\Api\Auth\Password
{
    class ResetController extends \Aireset\Http\Controllers\Api\ApiController
    {
        public function __construct()
        {
        }
        public function index(\Aireset\Http\Requests\Auth\PasswordResetRequest $request)
        {
            $credentials = $request->only('email', 'password', 'password_confirmation', 'token');
            $response = Password::reset($credentials, function($user, $password)
            {
                $this->resetPassword($user, $password);
            });
            switch( $response )
            {
                case Password::PASSWORD_RESET:
                    return $this->respondWithSuccess();
                default:
                    return $this->setStatusCode(400)->respondWithError(trans($response));
            }
        }
        protected function resetPassword($user, $password)
        {
            $user->password = $password;
            $user->save();
            event(new \Aireset\Events\User\ResetedPasswordViaEmail($user));
        }
    }

}
