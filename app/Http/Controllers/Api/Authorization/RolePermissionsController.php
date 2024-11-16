<?php
namespace Aireset\Http\Controllers\Api\Authorization
{
    class RolePermissionsController extends \Aireset\Http\Controllers\Api\ApiController
    {
        private $roles = null;
        public function __construct(\Aireset\Repositories\Role\RoleRepository $roles)
        {
            $this->roles = $roles;
            $this->middleware('auth');
            $this->middleware('permission:permissions.manage');
        }
        public function show(\Aireset\Role $role)
        {
            return $this->respondWithCollection($role->cachedPermissions(), new \Aireset\Transformers\PermissionTransformer());
        }
        public function update(\Aireset\Role $role, \Aireset\Http\Requests\Role\UpdateRolePermissionsRequest $request)
        {
            $this->roles->updatePermissions($role->id, $request->permissions);
            event(new \Aireset\Events\Role\PermissionsUpdated());
            return $this->respondWithCollection($role->cachedPermissions(), new \Aireset\Transformers\PermissionTransformer());
        }
    }

}
