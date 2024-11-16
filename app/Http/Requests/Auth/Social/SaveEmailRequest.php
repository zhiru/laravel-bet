<?php
namespace Aireset\Http\Requests\Auth\Social
{
    class SaveEmailRequest extends \Aireset\Http\Requests\Request
    {
        public function rules()
        {
            return ['email' => 'required|email|unique:users,email'];
        }
    }

}
