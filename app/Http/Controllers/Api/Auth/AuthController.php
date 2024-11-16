<?php
namespace Aireset\Http\Controllers\Api\Auth
{
    class AuthController extends \Aireset\Http\Controllers\Api\ApiController
    {
        public function __construct()
        {
            $this->middleware('guest')->only('login');
            $this->middleware('auth')->only('logout');
        }
        public function login(\Aireset\Http\Requests\Auth\LoginRequest $request)
        {
            $credentials = $request->getCredentials();
            if( settings('use_email') )
            {
                if( filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) )
                {
                    $credentials = [
                        'email' => $credentials['username'],
                        'password' => $credentials['password']
                    ];
                }
                else
                {
                    $credentials = [
                        'username' => $credentials['username'],
                        'password' => $credentials['password']
                    ];
                }
            }
            try
            {
                if( !($token = JWTAuth::attempt($credentials)) )
                {
                    return $this->errorUnauthorized('Invalid credentials.');
                }
            }
            catch( \Tymon\JWTAuth\Exceptions\JWTException $e )
            {
                return $this->errorInternalError('Could not create token.');
            }
        }
        private function invalidateToken($token)
        {
            JWTAuth::setToken($token);
            JWTAuth::invalidate();
        }
        public function logout()
        {
            event(new \Aireset\Events\User\LoggedOut());
            auth()->logout();
            return $this->respondWithSuccess();
        }
    }

}
