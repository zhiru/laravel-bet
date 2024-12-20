<?php
namespace Aireset\Http\Controllers\Web\Backend\Auth
{
    class AuthController extends \Aireset\Http\Controllers\Controller
    {
        private $users = null;
        public function __construct(\Aireset\Repositories\User\UserRepository $users)
        {
            $this->middleware('guest', [
                'except' => ['getLogout']
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
        public function getLogin()
        {
            $directories = [];
            foreach( glob(resource_path() . '/lang/*', GLOB_ONLYDIR) as $fileinfo )
            {
                $dirname = basename($fileinfo);
                $directories[$dirname] = $dirname;
            }
            return view('backend.auth.login', compact('directories'));
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
            if( !\Auth::validate($credentials) )
            {
                if( $throttles )
                {
                    $this->incrementLoginAttempts($request);
                }
                return redirect()->to('backend/login' . $to)->withErrors(trans('auth.failed'));
            }
            $user = \Auth::getProvider()->retrieveByCredentials($credentials);
            if( $request->lang )
            {
                $user->update(['language' => $request->lang]);
            }
            if( !$user->hasRole('admin') && setting('siteisclosed') )
            {
                \Auth::logout();
                return redirect()->route('backend.auth.login')->withErrors(trans('app.site_is_turned_off'));
            }
            if( $user->hasRole([
                1,
                2,
                3
            ]) && (!$user->shop || $user->shop->is_blocked) )
            {
                return redirect()->to('backend/login' . $to)->withErrors(trans('app.your_shop_is_blocked'));
            }
            if( $user->isBanned() )
            {
                return redirect()->to('backend/login' . $to)->withErrors(trans('app.your_account_is_banned'));
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
            if( !\Auth::user()->hasPermission('dashboard') )
            {
                return redirect()->route('backend.user.list');
            }
            return redirect()->route('backend.dashboard');
        }
        public function getLogout()
        {
            event(new \Aireset\Events\User\LoggedOut());
            \Auth::logout();
            return redirect('/backend/login');
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
            return redirect('/backend/login')->withInput($request->only($this->loginUsername(), 'remember'))->withErrors([$this->loginUsername() => $this->getLockoutErrorMessage($seconds)]);
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
            return view('backend.auth.register');
        }
        public function postRegister(\Aireset\Http\Requests\Auth\RegisterRequest $request, \Aireset\Repositories\Role\RoleRepository $roles)
        {
            $user = $this->users->create(array_merge($request->only('username', 'password'), ['status' => \Aireset\Support\Enum\UserStatus::ACTIVE]));
            $role = \jeremykenedy\LaravelRoles\Models\Role::where('name', '=', 'User')->first();
            $user->attachRole($role);
            event(new \Aireset\Events\User\Registered($user));
            return redirect('/backend/login')->with('success', trans('app.account_created_login'));
        }
    }

}
