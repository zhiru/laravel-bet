<?php
namespace Aireset\Http\Controllers\Web\Frontend\Auth
{
    class AuthController extends \Aireset\Http\Controllers\Controller
    {
        private $users = null;
        protected $redirectTo = null;
        public function __construct(\Aireset\Repositories\User\UserRepository $users)
        {
            $this->middleware('guest', [
                'except' => [
                    'getLogout',
                    'apiLogin'
                ]
            ]);
            $this->middleware('auth', [
                'only' => ['getLogout']
            ]);
            $this->middleware('registration', [
                'only' => [
                    'getRegister',
                    'postRegister'
                ]
            ]);
            $this->users = $users;
        }
        public function getBasicTheme()
        {
            $frontend = settings('frontend', 'Default');
            if( \Auth::check() )
            {
                $shop = Shop::find(\Auth::user()->shop_id);
                if( $shop )
                {
                    $frontend = $shop->frontend;
                }
            }
            return $frontend;
        }
        public function getLogin()
        {
            $frontend = $this->getBasicTheme();
            $directories = [];
            foreach( glob(resource_path() . '/lang/*', GLOB_ONLYDIR) as $fileinfo )
            {
                $dirname = basename($fileinfo);
                $directories[$dirname] = $dirname;
            }
            return view('frontend.' . $frontend . '.auth.login', compact('directories'));
        }
        public function postLogin(\Aireset\Http\Requests\Auth\LoginRequest $request, \Aireset\Repositories\Session\SessionRepository $sessionRepository)
        {
            $throttles = settings('throttle_enabled');
            $to = ($request->has('to') ? '?to=' . $request->get('to') : '');
            if( $throttles && $this->hasTooManyLoginAttempts($request) )
            {
                return $this->sendLockoutResponse($request);
            }
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
            if( !\Auth::validate($credentials) )
            {
                if( $throttles )
                {
                    $this->incrementLoginAttempts($request);
                }
                return redirect()->to('login' . $to)->withErrors(trans('auth.failed'));
            }
            $user = \Auth::getProvider()->retrieveByCredentials($credentials);
            if( $user->hasRole([
                1,
                2,
                3
            ]) && (!$user->shop || $user->shop->is_blocked) )
            {
                return redirect()->to('backend/login' . $to)->withErrors('Your shop is blocked');
            }
            if( settings('use_email') && $user->isUnconfirmed() )
            {
                return redirect()->to('login' . $to)->withErrors(trans('app.please_confirm_your_email_first'));
            }
            if( $user->isBanned() )
            {
                return redirect()->to('login' . $to)->withErrors(trans('app.your_account_is_banned'));
            }
            if( $request->lang )
            {
                $user->update(['language' => $request->lang]);
            }
            \Auth::login($user, settings('remember_me') && $request->get('remember'));
            if( settings('reset_authentication') && count($sessionRepository->getUserSessions(\Auth::id())) )
            {
                foreach( $sessionRepository->getUserSessions($user->id) as $session )
                {
                    if( $session->id != session()->getId() )
                    {
                        $sessionRepository->invalidateSession($session->id);
                    }
                }
            }
            return $this->handleUserWasAuthenticated($request, $throttles, $user);
        }
        public function apiLogin($game, $token, $mode)
        {
            if( \Auth::check() )
            {
                event(new \Aireset\Events\User\LoggedOut());
                \Auth::logout();
            }
            $us = \Aireset\User::where('api_token', '=', $token)->get();
            if( isset($us[0]->id) )
            {
                \Auth::loginUsingId($us[0]->id, true);
                $ref = request()->server('HTTP_REFERER');
                if( $mode == 'desktop' )
                {
                    $gameUrl = 'game/' . $game . '?lobby_url=frame';
                }
                else
                {
                    $gameUrl = 'game/' . $game . '?lobby_url=' . $ref;
                }
                return redirect()->to($gameUrl);
            }
            else
            {
                return redirect()->to('');
            }
        }
        protected function handleUserWasAuthenticated(\Illuminate\Http\Request $request, $throttles, $user)
        {
            if( $throttles )
            {
                $this->clearLoginAttempts($request);
            }
            event(new \Aireset\Events\User\LoggedIn());
            if( $request->has('to') )
            {
                return redirect()->to($request->get('to'));
            }
            if( !$user->hasRole('user') )
            {
                if( !\Auth::user()->hasPermission('dashboard') )
                {
                    return redirect()->route('backend.user.list');
                }
                return redirect()->route('backend.dashboard');
            }
            return redirect()->intended();
        }
        public function getLogout()
        {
            event(new \Aireset\Events\User\LoggedOut());
            \Auth::logout();
            return redirect('/');
        }
        public function loginUsername()
        {
            return 'username';
        }
        protected function hasTooManyLoginAttempts(\Illuminate\Http\Request $request)
        {
            return app('Illuminate\Cache\RateLimiter')->tooManyAttempts($request->input($this->loginUsername()) . $request->ip(), $this->maxLoginAttempts());
        }
        protected function incrementLoginAttempts(\Illuminate\Http\Request $request)
        {
            app('Illuminate\Cache\RateLimiter')->hit($request->input($this->loginUsername()) . $request->ip(), $this->lockoutTime() / 60);
        }
        protected function retriesLeft(\Illuminate\Http\Request $request)
        {
            $attempts = app('Illuminate\Cache\RateLimiter')->attempts($request->input($this->loginUsername()) . $request->ip());
            return $this->maxLoginAttempts() - $attempts + 1;
        }
        protected function sendLockoutResponse(\Illuminate\Http\Request $request)
        {
            $seconds = app('Illuminate\Cache\RateLimiter')->availableIn($request->input($this->loginUsername()) . $request->ip());
            return redirect('/')->withInput($request->only($this->loginUsername(), 'remember'))->withErrors([$this->loginUsername() => $this->getLockoutErrorMessage($seconds)]);
        }
        protected function getLockoutErrorMessage($seconds)
        {
            return trans('auth.throttle', ['seconds' => $seconds]);
        }
        protected function clearLoginAttempts(\Illuminate\Http\Request $request)
        {
            app('Illuminate\Cache\RateLimiter')->clear($request->input($this->loginUsername()) . $request->ip());
        }
        protected function maxLoginAttempts()
        {
            return settings('throttle_attempts', 5);
        }
        protected function lockoutTime()
        {
            $lockout = (int)settings('throttle_lockout_time');
            if( $lockout <= 1 )
            {
                $lockout = 1;
            }
            return 60 * $lockout;
        }
        public function getRegister()
        {
            $frontend = $this->getBasicTheme();
            return view('frontend.' . $frontend . '.auth.register');
        }
        public function postRegister(\Aireset\Http\Requests\Auth\RegisterRequest $request)
        {
            $data = $request->only('email', 'username', 'password');
            $user = $this->users->create(array_merge($data, [
                'role_id' => 1,
                'status' => (settings('use_email') ? \Aireset\Support\Enum\UserStatus::UNCONFIRMED : \Aireset\Support\Enum\UserStatus::ACTIVE)
            ]));
            $role = \jeremykenedy\LaravelRoles\Models\Role::where('name', '=', 'User')->first();
            $user->attachRole($role);
            event(new \Aireset\Events\User\Registered($user));
            $message = (settings('use_email') ? trans('app.account_create_confirm_email') : trans('app.account_created_login'));
            if( !settings('use_email') )
            {
                \Auth::login($user, true);
            }
            return redirect()->route('frontend.auth.login')->with('success', $message);
        }
        public function checkUsername($username)
        {
            $generated = false;
            $key = 1;
            $logins = [];
            $generate = $username;
            $tmp = explode(',', settings('bots_login'));
            foreach( $tmp as $item )
            {
                $item = trim($item);
                if( $item )
                {
                    $logins[] = $item;
                }
            }
            while( !$generated )
            {
                $count = \Aireset\User::where('username', $generate)->count();
                if( $count || in_array($generate, $logins) )
                {
                    $generate = $username . '_' . $key;
                }
                else
                {
                    $generated = true;
                }
                $key++;
            }
            return $generate;
        }
        public function confirmEmail($token)
        {
            if( $user = $this->users->findByConfirmationToken($token) )
            {
                $this->users->update($user->id, [
                    'status' => \Aireset\Support\Enum\UserStatus::ACTIVE,
                    'confirmation_token' => null
                ]);
                return redirect()->to('/')->withSuccess(trans('app.email_confirmed_can_login'));
            }
            return redirect()->to('/')->withErrors(trans('app.wrong_confirmation_token'));
        }
    }

}
