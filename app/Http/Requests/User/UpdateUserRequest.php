<?php
namespace Aireset\Http\Requests\User
{
    class UpdateUserRequest extends \Aireset\Http\Requests\Request
    {
        public function rules()
        {
            $user = $this->user();
            return [
                'username' => 'regex:/^[A-Za-z0-9_.]+$/|nullable|unique:users,username,' . $user->id,
                'password' => 'min:6|confirmed',
                'status' => \Illuminate\Validation\Rule::in(array_keys(\Aireset\Support\Enum\UserStatus::lists()))
            ];
        }
    }

}
