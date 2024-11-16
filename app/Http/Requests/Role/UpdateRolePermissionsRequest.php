<?php
namespace Aireset\Http\Requests\Role
{
    class UpdateRolePermissionsRequest extends \Aireset\Http\Requests\Request
    {
        public function rules()
        {
            $permissions = \Aireset\Permission::pluck('id')->toArray();
            return [
                'permissions' => 'required|array',
                'permissions.*' => \Illuminate\Validation\Rule::in($permissions)
            ];
        }
        public function messages()
        {
            return ['permissions.*' => 'Provided permission does not exist.'];
        }
    }

}
