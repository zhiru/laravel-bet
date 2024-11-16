<?php
namespace Aireset\Http\Controllers\Api\Auth
{
    class RegistrationController extends \Aireset\Http\Controllers\Api\ApiController
    {
        private $users = null;
        private $roles = null;
        public function __construct(\Aireset\Repositories\User\UserRepository $users, \Aireset\Repositories\Role\RoleRepository $roles)
        {
            $this->middleware('registration');
            $this->users = $users;
            $this->roles = $roles;
        }
        public function index(\Aireset\Http\Requests\Auth\RegisterRequest $request)
        {
            $status = (settings('use_email') ? \Aireset\Support\Enum\UserStatus::UNCONFIRMED : \Aireset\Support\Enum\UserStatus::ACTIVE);
            $user = $this->users->create(array_merge($request->only('email', 'username', 'password', 'shop_id'), [
                'status' => $status,
                'role_id' => 1
            ]));
            $role = \jeremykenedy\LaravelRoles\Models\Role::where('name', '=', 'User')->first();
            $user->attachRole($role);
            event(new \Aireset\Events\User\Registered($user));
            return $this->setStatusCode(201)->respondWithArray(['requires_email_confirmation' => settings('use_email')]);
        }
        public function verifyEmail($token)
        {
            if( !settings('use_email') )
            {
                return $this->errorNotFound();
            }
            if( $user = $this->users->findByConfirmationToken($token) )
            {
                $this->users->update($user->id, [
                    'status' => \Aireset\Support\Enum\UserStatus::ACTIVE,
                    'confirmation_token' => null
                ]);
                return $this->respondWithSuccess();
            }
            return $this->setStatusCode(400)->respondWithError('Invalid confirmation token.');
        }
    }

}
