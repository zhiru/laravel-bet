<?php
namespace Aireset\Http\Requests\User
{
    class CreateUserRequest extends \Aireset\Http\Requests\Request
    {
        public function rules()
        {
            $rules = [
                'username' => 'required|regex:/^[A-Za-z0-9]+$/|unique:users,username',
                'password' => 'required|min:6|confirmed'
            ];
            return $rules;
        }
    }

}
