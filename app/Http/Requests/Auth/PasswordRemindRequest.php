<?php
namespace Aireset\Http\Requests\Auth
{
    class PasswordRemindRequest extends \Aireset\Http\Requests\Request
    {
        public function rules()
        {
            return ['email' => 'required|email|exists:users,email'];
        }
    }

}
