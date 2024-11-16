<?php
namespace Aireset\Http\Requests\Role
{
    class RemoveRoleRequest extends \Aireset\Http\Requests\Request
    {
        public function authorize()
        {
            return $this->route('role')->removable;
        }
        public function rules()
        {
            return [];
        }
    }

}
