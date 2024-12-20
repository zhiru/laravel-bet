<?php
namespace Aireset\Http\Controllers\Web\Frontend\Auth
{
    class PasswordController extends \Aireset\Http\Controllers\Controller
    {
        public function __construct()
        {
            $this->middleware('guest');
        }
        public function getBasicTheme()
        {
            $frontend = 'Default';
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
        public function forgotPassword()
        {
            $frontend = $this->getBasicTheme();
            return view('frontend.' . $frontend . '.auth.password.remind');
        }
        public function sendPasswordReminder(\Aireset\Http\Requests\Auth\PasswordRemindRequest $request, \Aireset\Repositories\User\UserRepository $users)
        {
            $user = $users->findByEmail($request->email);
            $token = Password::getRepository()->create($user);
            $user->notify(new \Aireset\Notifications\ResetPassword($token));
            event(new \Aireset\Events\User\RequestedPasswordResetEmail($user));
            return redirect()->to('password/remind')->with('success', trans('app.password_reset_email_sent'));
        }
        public function getReset($token = null)
        {
            if( is_null($token) )
            {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            }
            $frontend = $this->getBasicTheme();
            return view('frontend.' . $frontend . '.auth.password.reset')->with('token', $token);
        }
        public function postReset(\Aireset\Http\Requests\Auth\PasswordResetRequest $request, \Aireset\Repositories\User\UserRepository $users)
        {
            $credentials = $request->only('email', 'password', 'password_confirmation', 'token');
            $response = Password::reset($credentials, function($user, $password)
            {
                $this->resetPassword($user, $password);
            });
            switch( $response )
            {
                case Password::PASSWORD_RESET:
                    $user = $users->findByEmail($request->email);
                    \Auth::login($user);
                    return redirect('');
                default:
                    return redirect()->back()->withInput($request->only('email'))->withErrors(['email' => trans($response)]);
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
