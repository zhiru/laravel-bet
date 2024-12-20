<?php
namespace Aireset\Http\Requests\Role
{
    class CreateRoleRequest extends \Aireset\Http\Requests\Request
    {
        public function rules()
        {
            return ['slug' => 'required|regex:/^[a-zA-Z0-9\-_\.]+$/|unique:roles,slug'];
        }
    }

}
