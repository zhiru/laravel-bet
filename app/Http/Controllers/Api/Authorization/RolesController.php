<?php
namespace Aireset\Http\Controllers\Api\Authorization
{
    class RolesController extends \Aireset\Http\Controllers\Api\ApiController
    {
        private $roles = null;
        public function __construct(\Aireset\Repositories\Role\RoleRepository $roles)
        {
            $this->roles = $roles;
            $this->middleware('auth');
            $this->middleware('permission:roles.manage');
        }
        public function index()
        {
            return $this->respondWithCollection($this->roles->getAllWithUsersCount(), new \Aireset\Transformers\RoleTransformer());
        }
        public function store(\Aireset\Http\Requests\Role\CreateRoleRequest $request)
        {
            $role = $this->roles->create($request->only([
                'name',
                'display_name',
                'description'
            ]));
            return $this->respondWithItem($role, new \Aireset\Transformers\RoleTransformer());
        }
        public function show(\Aireset\Role $role)
        {
            return $this->respondWithItem($role, new \Aireset\Transformers\RoleTransformer());
        }
        public function update(\Aireset\Role $role, \Aireset\Http\Requests\Role\UpdateRoleRequest $request)
        {
            $input = collect($request->all());
            $role = $this->roles->update($role->id, $input->only([
                'name',
                'display_name',
                'description'
            ])->toArray());
            return $this->respondWithItem($role, new \Aireset\Transformers\RoleTransformer());
        }
        public function destroy(\Aireset\Role $role, \Aireset\Repositories\User\UserRepository $users, \Aireset\Http\Requests\Role\RemoveRoleRequest $request)
        {
            $userRole = $this->roles->findByName('User');
            $users->switchRolesForUsers($role->id, $userRole->id);
            $this->roles->delete($role->id);
            Cache::flush();
            return $this->respondWithSuccess();
        }
    }

}
