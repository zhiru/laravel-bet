<?php
namespace Aireset\Http\Requests\Auth
{
    class PasswordResetRequest extends \Aireset\Http\Requests\Request
    {
        public function rules()
        {
            return [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|confirmed|min:6'
            ];
        }
    }

}
