<?php
namespace Aireset\Http\Controllers\Api\Authorization
{
    class PermissionsController extends \Aireset\Http\Controllers\Api\ApiController
    {
        private $permissions = null;
        public function __construct(\Aireset\Repositories\Permission\PermissionRepository $permissions)
        {
            $this->permissions = $permissions;
            $this->middleware('auth');
            $this->middleware('permission:permissions.manage');
        }
        public function index()
        {
            return $this->respondWithCollection($this->permissions->all(), new \Aireset\Transformers\PermissionTransformer());
        }
        public function store(\Aireset\Http\Requests\Permission\CreatePermissionRequest $request)
        {
            $permission = $this->permissions->create($request->only([
                'name',
                'display_name',
                'description'
            ]));
            return $this->respondWithItem($permission, new \Aireset\Transformers\PermissionTransformer());
        }
        public function show(\Aireset\Permission $permission)
        {
            return $this->respondWithItem($permission, new \Aireset\Transformers\PermissionTransformer());
        }
        public function update(\Aireset\Permission $permission, \Aireset\Http\Requests\Permission\UpdatePermissionRequest $request)
        {
            $input = collect($request->all());
            $permission = $this->permissions->update($permission->id, $input->only([
                'name',
                'display_name',
                'description'
            ])->toArray());
            return $this->respondWithItem($permission, new \Aireset\Transformers\PermissionTransformer());
        }
        public function destroy(\Aireset\Permission $permission, \Aireset\Http\Requests\Permission\RemovePermissionRequest $request)
        {
            $this->permissions->delete($permission->id);
            return $this->respondWithSuccess();
        }
    }

}
