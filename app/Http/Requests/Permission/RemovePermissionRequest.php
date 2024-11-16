<?php
namespace Aireset\Http\Requests\Permission
{
    class RemovePermissionRequest extends \Aireset\Http\Requests\Request
    {
        public function authorize()
        {
            return $this->route('permission')->removable;
        }
        public function rules()
        {
            return [];
        }
    }

}
