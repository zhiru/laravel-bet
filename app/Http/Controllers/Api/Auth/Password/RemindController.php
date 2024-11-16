<?php
namespace Aireset\Http\Controllers\Api\Auth\Password
{
    class RemindController extends \Aireset\Http\Controllers\Api\ApiController
    {
        public function __construct()
        {
        }
        public function index(\Aireset\Http\Requests\Auth\PasswordRemindRequest $request, \Aireset\Repositories\User\UserRepository $users)
        {
            $user = $users->findByEmail($request->email);
            $token = Password::getRepository()->create($user);
            $user->notify(new \Aireset\Notifications\ResetPassword($token));
            event(new \Aireset\Events\User\RequestedPasswordResetEmail($user));
            return $this->respondWithSuccess();
        }
    }

}
