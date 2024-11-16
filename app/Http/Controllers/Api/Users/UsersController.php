<?php
namespace Aireset\Http\Controllers\Api\Users
{
    class UsersController extends \Aireset\Http\Controllers\Api\ApiController
    {
        private $users = null;
        private $max_users = 10000;
        public function __construct(\Aireset\Repositories\User\UserRepository $users)
        {
            $this->middleware('auth');
            $this->middleware('permission:users.manage');
            $this->users = $users;
        }
        public function index(\Illuminate\Http\Request $request)
        {
            $users = \Aireset\User::orderBy('created_at', 'DESC');
            $users = $users->where('role_id', '<=', auth()->user()->role_id);
            $users = $users->where('id', '!=', auth()->user()->id);
            if( $request->search != '' )
            {
                $users = $users->where('title', 'like', '%' . $request->search . '%');
            }
            if( $request->status != '' )
            {
                $users = $users->where('status', $request->status);
            }
            $users = $users->paginate(20000);
            return $this->respondWithPagination($users, new \Aireset\Transformers\UserTransformer());
        }
        public function store(\Aireset\Http\Requests\User\CreateUserRequest $request)
        {
            $count = \Aireset\User::where([
                'shop_id' => auth()->user()->shop_id,
                'role_id' => 1
            ])->count();
            if( $this->max_users <= $count )
            {
                return $this->setStatusCode(403)->respondWithError('Max users in shop is ' . $this->max_users);
            }
            $data = $request->only([
                'password',
                'username',
                'first_name',
                'last_name',
                'phone',
                'address',
                'birthday'
            ]);
            $data += ['status' => \Aireset\Support\Enum\UserStatus::ACTIVE];
            $data += ['parent_id' => auth()->user()->id];
            $data += ['role_id' => 1];
            $data += ['shop_id' => auth()->user()->shop_id];
            $user = $this->users->create($data);
            $role = \jeremykenedy\LaravelRoles\Models\Role::find(1);
            $user->detachAllRoles();
            $user->attachRole($role);
            \Aireset\ShopUser::create([
                'shop_id' => $request->shop_id,
                'user_id' => $user->id
            ]);
            return $this->setStatusCode(201)->respondWithItem($user, new \Aireset\Transformers\UserTransformer());
        }
        public function show(\Aireset\User $user)
        {
            if( auth()->user()->role_id <= $user->role_id && $user->id != auth()->id() )
            {
                return $this->errorForbidden('Access denied');
            }
            return $this->respondWithItem($user, new \Aireset\Transformers\UserTransformer());
        }
        public function update(\Aireset\User $user, \Aireset\Http\Requests\User\UpdateUserRequest $request)
        {
            if( auth()->user()->role_id <= $user->role_id && $user->id != auth()->id() )
            {
                return $this->errorForbidden('Access denied');
            }
            $data = $request->only([
                'password',
                'username',
                'first_name',
                'last_name',
                'phone',
                'address',
                'birthday',
                'status'
            ])->toArray();
            $user = $this->users->update($user->id, $data);
            event(new \Aireset\Events\User\UpdatedByAdmin($user));
            if( $this->userIsBanned($user, $request) )
            {
                event(new \Aireset\Events\User\Banned($user));
            }
            return $this->respondWithItem($user, new \Aireset\Transformers\UserTransformer());
        }
        private function userIsBanned(\Aireset\User $user, \Illuminate\Http\Request $request)
        {
            return $user->status != $request->status && $request->status == \Aireset\Support\Enum\UserStatus::BANNED;
        }
        public function destroy(\Aireset\User $user)
        {
            if( $user->id == auth()->id() )
            {
                return $this->errorForbidden('You cannot delete yourself.');
            }
            if( $user->balance > 0 )
            {
                return $this->errorForbidden('Balance > 0');
            }
            if( auth()->user()->role_id <= $user->role_id )
            {
                return $this->errorForbidden('You can\'t delete this user');
            }
            event(new \Aireset\Events\User\Deleted($user));
            \Aireset\Transaction::where('user_id', $user->id)->delete();
            \Aireset\ShopUser::where('user_id', $user->id)->delete();
            \Aireset\StatGame::where('user_id', $user->id)->delete();
            \Aireset\GameLog::where('user_id', $user->id)->delete();
            \Aireset\UserActivity::where('user_id', $user->id)->delete();
            \Aireset\Session::where('user_id', $user->id)->delete();
            $this->users->delete($user->id);
            return $this->respondWithSuccess();
        }
    }

}
