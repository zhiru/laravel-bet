<?php
namespace Aireset\Http\Requests\Permission
{
    class BasePermissionRequest extends \Aireset\Http\Requests\Request
    {
        public function messages()
        {
            return ['name.unique' => trans('app.permission_already_exists')];
        }
    }

}
