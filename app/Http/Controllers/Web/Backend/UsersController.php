<?php
namespace Aireset\Http\Controllers\Web\Backend
{
    class UsersController extends \Aireset\Http\Controllers\Controller
    {
        private $users = null;
        private $max_users = 10000000;
        public function __construct(\Aireset\Repositories\User\UserRepository $users)
        {
            $this->middleware('auth');
            $this->middleware('permission:access.admin.panel');
            $this->users = $users;
        }
        public function index(\Illuminate\Http\Request $request)
        {
/*            $checked = new \Aireset\Lib\LicenseDK();
            $license_notifications_array = $checked->aplVerifyLicenseDK(null, 0);
            if( $license_notifications_array['notification_case'] != 'notification_license_ok' )
            {
                return redirect()->route('frontend.page.error_license');
            }
            if( !$this->security() )
            {
                return redirect()->route('frontend.page.error_license');
            }*/
            $statuses = ['' => trans('app.all')] + \Aireset\Support\Enum\UserStatus::lists();
            $roles = \jeremykenedy\LaravelRoles\Models\Role::where('level', '<', \Illuminate\Support\Facades\Auth::user()->level())->pluck('name', 'id');
            $roles->prepend(trans('app.all'), '0');
            $users = \Aireset\User::orderBy('created_at', 'DESC');
            if( !auth()->user()->shop_id )
            {
                if( auth()->user()->hasRole('admin') )
                {
                    $users = $users->whereIn('role_id', [
                        4,
                        5
                    ]);
                }
                if( auth()->user()->hasRole('agent') )
                {
                    $distributors = auth()->user()->availableUsersByRole('distributor');
                    if( $distributors )
                    {
                        $users = $users->whereIn('id', $distributors);
                    }
                    else
                    {
                        $users = $users->where('id', 0);
                    }
                }
                if( auth()->user()->hasRole('distributor') )
                {
                    $managers = auth()->user()->availableUsersByRole('manager');
                    if( $managers )
                    {
                        $users = $users->whereIn('id', $managers);
                    }
                    else
                    {
                        $users = $users->where('id', 0);
                    }
                }
            }
            else
            {
                $users = $users->whereIn('id', auth()->user()->availableUsers())->whereHas('rel_shops', function($query)
                {
                    $query->where('shop_id', auth()->user()->shop_id);
                });
            }
            $users = $users->where('id', '!=', \Illuminate\Support\Facades\Auth::user()->id);
            if( $request->search != '' )
            {
                $users = $users->where('username', 'like', '%' . $request->search . '%');
            }
            if( $request->status != '' )
            {
                $users = $users->where('status', $request->status);
            }
            if( $request->role )
            {
                $users = $users->where('role_id', $request->role);
            }
            $users = $users->paginate(20);
            $happyhour = \Aireset\HappyHour::where([
                'shop_id' => auth()->user()->shop_id,
                'time' => date('G')
            ])->first();
            return view('backend.user.list', compact('users', 'statuses', 'roles', 'role_id', 'happyhour'));
        }
        public function tree(\Illuminate\Http\Request $request)
        {
            $users = \Aireset\User::where('id', auth()->user()->id)->get();
            if( auth()->user()->hasRole('admin') )
            {
                $users = \Aireset\User::where('role_id', 5)->get();
            }
            $role = \jeremykenedy\LaravelRoles\Models\Role::where('id', auth()->user()->role_id - 1)->first();
            return view('backend.user.tree', compact('users', 'role'));
        }
        public function view(\Aireset\User $user, \Aireset\Repositories\Activity\ActivityRepository $activities)
        {
            $userActivities = $activities->getLatestActivitiesForUser($user->id, 10);
            if( \Illuminate\Support\Facades\Auth::user()->role_id < $user->role_id )
            {
                return redirect()->route('backend.user.list');
            }
            return view('backend.user.view', compact('user', 'userActivities'));
        }
        public function create()
        {
            $happyhour = \Aireset\HappyHour::where([
                'shop_id' => auth()->user()->shop_id,
                'time' => date('G')
            ])->first();
            $roles = \jeremykenedy\LaravelRoles\Models\Role::where('level', '<', \Illuminate\Support\Facades\Auth::user()->level())->pluck('name', 'id');
            $statuses = \Aireset\Support\Enum\UserStatus::lists();
            $shops = auth()->user()->shops();
            $availibleUsers = [];
            if( auth()->user()->hasRole('admin') )
            {
                $availibleUsers = \Aireset\User::get();
            }
            if( auth()->user()->hasRole('agent') )
            {
                $me = \Aireset\User::where('id', \Illuminate\Support\Facades\Auth::id())->get();
                $distributors = \Aireset\User::where([
                    'parent_id' => auth()->user()->id,
                    'role_id' => 4
                ])->get();
                if( $shopsIds = \Illuminate\Support\Facades\Auth::user()->shops(true) )
                {
                    $users = \Aireset\ShopUser::whereIn('shop_id', $shopsIds)->pluck('user_id');
                    if( $users )
                    {
                        $availibleUsers = \Aireset\User::whereIn('id', $users)->whereIn('role_id', [
                            2,
                            3
                        ])->get();
                    }
                }
                $me = $me->merge($distributors);
                $availibleUsers = $me->merge($availibleUsers);
            }
            if( auth()->user()->hasRole([
                'distributor',
                'manager',
                'cashier'
            ]) )
            {
                $me = \Aireset\User::where('id', \Illuminate\Support\Facades\Auth::id())->get();
                if( $shopsIds = \Illuminate\Support\Facades\Auth::user()->shops(true) )
                {
                    $users = \Aireset\ShopUser::whereIn('shop_id', $shopsIds)->pluck('user_id');
                    if( $users )
                    {
                        $availibleUsers = \Aireset\User::whereIn('id', $users)->whereIn('role_id', [
                            2,
                            3
                        ])->get();
                    }
                }
                $availibleUsers = $me->merge($availibleUsers);
            }
            return view('backend.user.add', compact('roles', 'statuses', 'shops', 'availibleUsers', 'happyhour'));
        }
        public function store(\Aireset\Http\Requests\User\CreateUserRequest $request)
        {
            $count = \Aireset\User::where([
                'shop_id' => \Illuminate\Support\Facades\Auth::user()->shop_id,
                'role_id' => 1
            ])->count();
            if( $request->role_id <= 3 && !$request->shop_id )
            {
                return redirect()->route('backend.user.list')->withErrors([trans('app.choose_shop')]);
            }
            $data = $request->all() + ['status' => \Aireset\Support\Enum\UserStatus::ACTIVE];
            if( trim($data['username']) == '' )
            {
                $data['username'] = null;
            }
            if( $this->max_users <= $count && $data['role_id'] == 1 )
            {
                return redirect()->route('backend.user.list')->withErrors([trans('app.max_users', ['max' => $this->max_users])]);
            }
            if( !$request->parent_id )
            {
                $data['parent_id'] = \Illuminate\Support\Facades\Auth::user()->id;
            }
            if( $request->balance && $request->balance > 0 )
            {
                $shop = \Aireset\Shop::find(\Illuminate\Support\Facades\Auth::user()->shop_id);
                $sum = floatval($request->balance);
                if( $shop->balance < $sum )
                {
                    return redirect()->back()->withErrors([trans('app.not_enough_money_in_the_shop', [
                        'name' => $shop->name,
                        'balance' => $shop->balance
                    ])]);
                }
                $open_shift = \Aireset\OpenShift::where([
                    'shop_id' => \Illuminate\Support\Facades\Auth::user()->shop_id,
                    'end_date' => null
                ])->first();
                if( !$open_shift )
                {
                    return redirect()->back()->withErrors([trans('app.shift_not_opened')]);
                }
            }
            $role_id = (isset($data['role_id']) && $data['role_id'] < auth()->user()->role_id ? $data['role_id'] : auth()->user()->role_id - 1);
            $data['role_id'] = $role_id;
            $role = \jeremykenedy\LaravelRoles\Models\Role::find($role_id);
            if( (auth()->user()->hasRole('distributor') && $role->slug == 'manager' || auth()->user()->hasRole('manager') && $role->slug == 'cashier') && \Aireset\User::where([
                'role_id' => $role->id,
                'shop_id' => $request->shop_id
            ])->count() )
            {
                return redirect()->route('backend.user.list')->withErrors([trans('app.only_1', ['type' => $role->slug])]);
            }
            $user = $this->users->create($data);
            $user->detachAllRoles();
            $user->attachRole($role);
            if( $request->shop_id && $request->shop_id > 0 && !empty($request->shop_id) )
            {
                \Aireset\ShopUser::create([
                    'shop_id' => $request->shop_id,
                    'user_id' => $user->id
                ]);
            }
            if( $request->balance && $request->balance > 0 )
            {
                $happyhour = \Aireset\HappyHour::where([
                    'shop_id' => auth()->user()->shop_id,
                    'time' => date('G')
                ])->first();
                $balance = $sum;
                if( $happyhour )
                {
                    $transactionSum = $sum * intval(str_replace('x', '', $happyhour->multiplier));
                    $bonus = $transactionSum - $sum;
                    $wager = $bonus * intval(str_replace('x', '', $happyhour->wager));
                    \Aireset\Transaction::create([
                        'user_id' => $user->id,
                        'system' => 'HH ' . $happyhour->multiplier,
                        'summ' => $transactionSum,
                        'shop_id' => ($user->hasRole('user') ? $user->shop_id : 0)
                    ]);
                    $user->increment('wager', $wager);
                    $user->increment('bonus', $bonus);
                    $user->increment('count_bonus', $bonus);
                    $balance = $transactionSum;
                }
                else
                {
                    \Aireset\Transaction::create([
                        'user_id' => $user->id,
                        'payeer_id' => \Illuminate\Support\Facades\Auth::id(),
                        'summ' => $sum,
                        'shop_id' => auth()->user()->shop_id
                    ]);
                }
                $user->update([
                    'balance' => $balance,
                    'count_balance' => $sum,
                    'total_in' => $sum,
                    'count_return' => \Aireset\Lib\Functions::count_return($sum, $user->shop_id)
                ]);
                $shop->update(['balance' => $shop->balance - $sum]);
                $open_shift->increment('balance_out', abs($sum));
                $open_shift->increment('money_in', abs($sum));
            }
            if( !$user->shop_id && $user->hasRole([
                'cashier',
                'user'
            ]) )
            {
                $shops = $user->shops(true);
                if( count($shops) )
                {
                    $shop_id = $shops->first();
                    $user->update(['shop_id' => $shop_id]);
                }
            }
            return redirect()->route('backend.user.list')->withSuccess(trans('app.user_created'));
        }
        public function massadd(\Illuminate\Http\Request $request)
        {
            $shop = \Aireset\Shop::find(\Illuminate\Support\Facades\Auth::user()->shop_id);
            $count = \Aireset\User::where([
                'shop_id' => \Illuminate\Support\Facades\Auth::user()->shop_id,
                'role_id' => 1
            ])->count();
            if( isset($request->count) && is_numeric($request->count) && isset($request->balance) && is_numeric($request->balance) )
            {
                if( $this->max_users < ($count + $request->count) )
                {
                    return redirect()->route('backend.user.list')->withErrors([trans('max_users', ['max' => $this->max_users])]);
                }
                if( $request->balance > 0 )
                {
                    if( $shop->balance < ($request->count * $request->balance) )
                    {
                        return redirect()->back()->withErrors([trans('app.not_enough_money_in_the_shop', [
                            'name' => $shop->name,
                            'balance' => $shop->balance
                        ])]);
                    }
                    $open_shift = \Aireset\OpenShift::where([
                        'shop_id' => \Illuminate\Support\Facades\Auth::user()->shop_id,
                        'end_date' => null
                    ])->first();
                    if( !$open_shift )
                    {
                        return redirect()->back()->withErrors([trans('app.shift_not_opened')]);
                    }
                }
                if( \Illuminate\Support\Facades\Auth::user()->hasRole('cashier') )
                {
                    $role = \jeremykenedy\LaravelRoles\Models\Role::find(1);
                    for( $i = 0; $i < $request->count; $i++ )
                    {
                        $number = rand(111111111, 999999999);
                        $data = [
                            'username' => $number,
                            'password' => $number,
                            'role_id' => $role->id,
                            'status' => \Aireset\Support\Enum\UserStatus::ACTIVE,
                            'parent_id' => \Illuminate\Support\Facades\Auth::user()->id,
                            'shop_id' => \Illuminate\Support\Facades\Auth::user()->shop_id
                        ];
                        $newUser = $this->users->create($data);
                        $newUser->attachRole($role);
                        \Aireset\ShopUser::create([
                            'shop_id' => \Illuminate\Support\Facades\Auth::user()->shop_id,
                            'user_id' => $newUser->id
                        ]);
                        if( $request->balance > 0 )
                        {
                            $happyhour = \Aireset\HappyHour::where([
                                'shop_id' => auth()->user()->shop_id,
                                'time' => date('G')
                            ])->first();
                            $balance = $sum = $request->balance;
                            if( $happyhour )
                            {
                                $transactionSum = $sum * intval(str_replace('x', '', $happyhour->multiplier));
                                $bonus = $transactionSum - $sum;
                                $wager = $bonus * intval(str_replace('x', '', $happyhour->wager));
                                \Aireset\Transaction::create([
                                    'user_id' => $newUser->id,
                                    'system' => 'HH ' . $happyhour->multiplier,
                                    'summ' => $transactionSum,
                                    'shop_id' => $newUser->shop_id
                                ]);
                                $newUser->increment('wager', $wager);
                                $newUser->increment('bonus', $bonus);
                                $newUser->increment('count_bonus', $bonus);
                                $balance = $transactionSum;
                            }
                            else
                            {
                                \Aireset\Transaction::create([
                                    'user_id' => $newUser->id,
                                    'payeer_id' => \Illuminate\Support\Facades\Auth::id(),
                                    'summ' => $request->balance,
                                    'shop_id' => $newUser->shop_id
                                ]);
                            }
                            $newUser->update([
                                'balance' => $balance,
                                'count_balance' => $sum,
                                'total_in' => $sum,
                                'count_return' => \Aireset\Lib\Functions::count_return($sum, $newUser->shop_id)
                            ]);
                            $shop->decrement('balance', $request->balance);
                            $open_shift->increment('balance_out', $request->balance);
                            $open_shift->increment('money_in', $request->balance);
                            $newUser->refresh();
                        }
                    }
                }
            }
            return redirect()->route('backend.user.list')->withSuccess(trans('app.user_created'));
        }
        public function edit(\Illuminate\Http\Request $request, \Aireset\Repositories\Activity\ActivityRepository $activitiesRepo, \Aireset\User $user)
        {
            $edit = true;
            $roles = \jeremykenedy\LaravelRoles\Models\Role::where('level', '<=', \Illuminate\Support\Facades\Auth::user()->level())->pluck('name', 'id');
            $statuses = \Aireset\Support\Enum\UserStatus::lists();
            $shops = $user->shops();
            $userActivities = $activitiesRepo->getLatestActivitiesForUser($user->id, 50);
            $users = auth()->user()->availableUsers();
            if( count($users) && !in_array($user->id, $users) )
            {
                return redirect()->route('backend.user.list')->withErrors([trans('app.wrong_shop')]);
            }
            if( \Illuminate\Support\Facades\Auth::user()->role_id < $user->role_id )
            {
                return redirect()->route('backend.user.list');
            }
            if( $shopIds = $user->shops(true) )
            {
                $allShops = \Aireset\Shop::whereIn('id', $shopIds)->get();
            }
            else
            {
                $allShops = \Aireset\Shop::where('id', 0)->get();
            }
            $free_shops = [];
            foreach( $allShops as $shop )
            {
                if( !$shop->distributors_count() )
                {
                    $free_shops[$shop->id] = $shop->name;
                }
            }
            $hasActivities = $this->hasActivities($user);
            $langs = [];
            foreach( glob(resource_path() . '/lang/*', GLOB_ONLYDIR) as $fileinfo )
            {
                $dirname = basename($fileinfo);
                $langs[$dirname] = $dirname;
            }
            $date = ($request->date ?: \Carbon\Carbon::now()->format('Y-m-d'));
            $numbers = \DB::select("SELECT \r\n                                        `game`, SUM(number) AS summ \r\n                                    FROM \r\n                                        `w_games_activity` \r\n                                    WHERE \r\n                                        `user_id` =:userID AND \r\n                                        `number` != \"\" AND \r\n                                        `created_at` LIKE :mydate \r\n                                    GROUP BY game \r\n                                    ORDER BY SUM(number) DESC \r\n                                    LIMIT 5", [
                'userID' => $user->id,
                'mydate' => $date . '%'
            ]);
            $max_wins = \Aireset\GameActivity::where('user_id', $user->id)->where('created_at', 'LIKE', $date . '%')->where('max_win', '!=', '')->groupBy('game')->orderBy('max_win', 'DESC')->take(5)->get();
            $max_bets = \Aireset\GameActivity::where('user_id', $user->id)->where('created_at', 'LIKE', $date . '%')->where('max_bet', '!=', '')->groupBy('game')->orderBy('max_bet', 'DESC')->take(5)->get();
            return view('backend.user.edit', compact('edit', 'user', 'roles', 'statuses', 'shops', 'free_shops', 'userActivities', 'hasActivities', 'langs', 'max_wins', 'max_bets', 'numbers'));
        }
        public function updateDetails(\Aireset\User $user, \Aireset\Http\Requests\User\UpdateDetailsRequest $request)
        {
            $users = auth()->user()->availableUsers();
            if( count($users) && !in_array($user->id, $users) )
            {
                return redirect()->route('backend.user.list')->withErrors([trans('app.wrong_shop')]);
            }
            if( \Illuminate\Support\Facades\Auth::user()->role_id < $user->role_id )
            {
                return redirect()->route('backend.user.list');
            }
            $request->validate([
                'username' => 'required|unique:users,username,' . $user->id,
                'email' => 'nullable|unique:users,email,' . $user->id
            ]);
            $count = \Aireset\User::where([
                'shop_id' => \Illuminate\Support\Facades\Auth::user()->shop_id,
                'role_id' => 1
            ])->count();
            $data = $request->all();
            if( empty($data['password']) )
            {
                unset($data['password']);
            }
            if( empty($data['password_confirmation']) )
            {
                unset($data['password_confirmation']);
            }
            if( isset($data['role_id']) && $user->role_id != $data['role_id'] && $data['role_id'] == 1 && $this->max_users <= ($count + 1) )
            {
                return redirect()->route('backend.user.list')->withErrors([trans('max_users', ['max' => $this->max_users])]);
            }
            unset($data['role_id']);
            $this->users->update($user->id, $data);
            if( $user->hasRole([
                'distributor',
                'cashier',
                'user'
            ]) && $request->shops && count($request->shops) )
            {
                foreach( $request->shops as $shop )
                {
                    \Aireset\ShopUser::create([
                        'shop_id' => $shop,
                        'user_id' => $user->id
                    ]);
                }
            }
            if( $user->hasRole([
                'agent',
                'distributor'
            ]) && $request->free_shops && count($request->free_shops) )
            {
                foreach( $request->free_shops as $shop )
                {
                    \Aireset\ShopUser::create([
                        'shop_id' => $shop,
                        'user_id' => $user->id
                    ]);
                }
            }
            event(new \Aireset\Events\User\UpdatedByAdmin($user));
            if( $this->userIsBanned($user, $request) )
            {
                event(new \Aireset\Events\User\Banned($user));
            }
            return redirect()->back()->withSuccess(trans('app.user_updated'));
        }
        public function updateBalance(\Illuminate\Http\Request $request)
        {
            $data = $request->all();
            if( !array_get($data, 'type') )
            {
                $data['type'] = 'add';
            }
            $user = \Aireset\User::find($request->user_id);
            $request->summ = floatval($request->summ);
            if( $request->all && $request->all == '1' )
            {
                $request->summ = $user->balance;
            }
            $result = $user->addBalance($data['type'], $request->summ);
            $result = json_decode($result, true);
            if( $result['status'] == 'error' )
            {
                return redirect()->back()->withErrors([$result['message']]);
            }
            return redirect()->back()->withSuccess($result['message']);
        }
        public function statistics(\Aireset\User $user, \Illuminate\Http\Request $request)
        {
            $statistics = \Aireset\Transaction::where('user_id', $user->id)->orderBy('created_at', 'DESC')->paginate(20);
            return view('backend.stat.pay_stat', compact('user', 'statistics'));
        }
        private function userIsBanned(\Aireset\User $user, \Illuminate\Http\Request $request)
        {
            return $user->status != $request->status && $request->status == \Aireset\Support\Enum\UserStatus::BANNED;
        }
        public function updateAvatar(\Aireset\User $user, \Aireset\Services\Upload\UserAvatarManager $avatarManager, \Illuminate\Http\Request $request)
        {
            $this->validate($request, ['avatar' => 'image']);
            $name = $avatarManager->uploadAndCropAvatar($user, $request->file('avatar'), $request->get('points'));
            if( $name )
            {
                $this->users->update($user->id, ['avatar' => $name]);
                event(new \Aireset\Events\User\UpdatedByAdmin($user));
                return redirect()->route('backend.user.edit', $user->id)->withSuccess(trans('app.avatar_changed'));
            }
            return redirect()->route('backend.user.edit', $user->id)->withErrors(trans('app.avatar_not_changed'));
        }
        public function updateAvatarExternal(\Aireset\User $user, \Illuminate\Http\Request $request, \Aireset\Services\Upload\UserAvatarManager $avatarManager)
        {
            $avatarManager->deleteAvatarIfUploaded($user);
            $this->users->update($user->id, ['avatar' => $request->get('url')]);
            event(new \Aireset\Events\User\UpdatedByAdmin($user));
            return redirect()->route('backend.user.edit', $user->id)->withSuccess(trans('app.avatar_changed'));
        }
        public function updateLoginDetails(\Aireset\User $user, \Aireset\Http\Requests\User\UpdateLoginDetailsRequest $request)
        {
            $data = $request->all();
            if( trim($data['password']) == '' )
            {
                unset($data['password']);
                unset($data['password_confirmation']);
            }
            $this->users->update($user->id, $data);
            event(new \Aireset\Events\User\UpdatedByAdmin($user));
            return redirect()->route('backend.user.edit', $user->id)->withSuccess(trans('app.login_updated'));
        }
        public function delete(\Aireset\User $user)
        {
            if( $user->id == \Illuminate\Support\Facades\Auth::id() )
            {
                return redirect()->route('backend.user.list')->withErrors(trans('app.you_cannot_delete_yourself'));
            }
            if( $user->balance > 0 )
            {
                return redirect()->route('backend.user.list')->withErrors([trans('app.balance_not_zero')]);
            }
            if( (auth()->user()->hasRole('admin') && $user->hasRole('agent') || auth()->user()->hasRole('agent') && $user->hasRole('distributor') || auth()->user()->hasRole('distributor') && $user->hasRole('manager')) && ($count = \Aireset\User::where('parent_id', $user->id)->count()) )
            {
                return redirect()->route('backend.user.list')->withErrors([trans('app.has_users', ['name' => $user->username])]);
            }
            if( (auth()->user()->hasRole('admin') && $user->hasRole('agent') || auth()->user()->hasRole('agent') && $user->hasRole('distributor') || auth()->user()->hasRole('distributor') && $user->hasRole('manager') || auth()->user()->hasRole('manager') && $user->hasRole('cashier')) && $this->hasActivities($user) )
            {
                return redirect()->route('backend.user.list')->withErrors([trans('app.has_stats', ['name' => $user->username])]);
            }
            $user->detachAllRoles();
            \Aireset\Transaction::where('user_id', $user->id)->delete();
            \Aireset\ShopUser::where('user_id', $user->id)->delete();
            \Aireset\StatGame::where('user_id', $user->id)->delete();
            \Aireset\GameLog::where('user_id', $user->id)->delete();
            \Aireset\UserActivity::where('user_id', $user->id)->delete();
            \Aireset\Session::where('user_id', $user->id)->delete();
            \Aireset\Info::where('user_id', $user->id)->delete();
            event(new \Aireset\Events\User\Deleted($user));
            $user->delete();
            return redirect()->route('backend.user.list')->withSuccess(trans('app.user_deleted'));
        }
        public function hard_delete(\Aireset\User $user)
        {
            if( $user->id == \Illuminate\Support\Facades\Auth::id() )
            {
                return redirect()->route('backend.user.list')->withErrors(trans('app.you_cannot_delete_yourself'));
            }
            if( !(auth()->user()->hasRole('admin') && $user->hasRole([
                'agent',
                'distributor'
            ])) )
            {
                return redirect()->route('backend.user.list')->withErrors([trans('app.no_permission')]);
            }
            if( $user->hasRole('agent') )
            {
                $distributors = \Aireset\User::where([
                    'parent_id' => $user->id,
                    'role_id' => 4
                ])->get();
            }
            if( $user->hasRole('distributor') )
            {
                $distributors = \Aireset\User::where(['id' => $user->id])->get();
            }
            if( $distributors )
            {
                foreach( $distributors as $distributor )
                {
                    if( $distributor->rel_shops )
                    {
                        foreach( $distributor->rel_shops as $shop )
                        {
                            \Aireset\ShopUser::where('shop_id', $shop->shop_id)->delete();
                            \Aireset\Shop::where('id', $shop->shop_id)->delete();
                            \Aireset\Task::create([
                                'category' => 'shop',
                                'action' => 'delete',
                                'item_id' => $shop->shop_id,
                                'user_id' => auth()->user()->id
                            ]);
                            $usersToDelete = \Aireset\User::whereIn('role_id', [
                                1,
                                2,
                                3
                            ])->where('shop_id', $shop->shop_id)->get();
                            if( $usersToDelete )
                            {
                                foreach( $usersToDelete as $userDelete )
                                {
                                    event(new \Aireset\Events\User\Deleted($userDelete));
                                    $userDelete->delete();
                                }
                            }
                        }
                    }
                    event(new \Aireset\Events\User\Deleted($distributor));
                    $distributor->delete();
                }
            }
            if( $user->hasRole('agent') )
            {
                event(new \Aireset\Events\User\Deleted($user));
                $user->delete();
            }
            return redirect()->route('backend.user.list')->withSuccess(trans('app.user_deleted'));
        }
        public function hasActivities($user)
        {
            if( $user->hasRole([
                'distributor',
                'manager',
                'cashier'
            ]) )
            {
                $transactions = \Aireset\Transaction::where('payeer_id', $user->id)->count();
                if( $transactions )
                {
                    return true;
                }
                $stats = \Aireset\BankStat::where('user_id', $user->id)->count();
                if( $stats )
                {
                    return true;
                }
                $stats = \Aireset\StatGame::where('user_id', $user->id)->count();
                if( $stats )
                {
                    return true;
                }
                $stats = \Aireset\ShopStat::where('user_id', $user->id)->count();
                if( $stats )
                {
                    return true;
                }
                $open_shifts = \Aireset\OpenShift::where('user_id', $user->id)->count();
                if( $open_shifts )
                {
                    return true;
                }
            }
            return false;
        }
        public function sessions(\Aireset\User $user, \Aireset\Repositories\Session\SessionRepository $sessionRepository)
        {
            $adminView = true;
            $sessions = $sessionRepository->getUserSessions($user->id);
            return view('backend.user.sessions', compact('sessions', 'user', 'adminView'));
        }
        public function invalidateSession(\Aireset\User $user, $session, \Aireset\Repositories\Session\SessionRepository $sessionRepository)
        {
            $sessionRepository->invalidateSession($session->id);
            return redirect()->route('backend.user.sessions', $user->id)->withSuccess(trans('app.session_invalidated'));
        }
        public function action($action)
        {
            $open_shift = \Aireset\OpenShift::where([
                'shop_id' => \Illuminate\Support\Facades\Auth::user()->shop_id,
                'end_date' => null
            ])->first();
            if( !$open_shift )
            {
                return redirect()->back()->withErrors([trans('app.shift_not_opened')]);
            }
            $shop = \Aireset\Shop::find(\Illuminate\Support\Facades\Auth::user()->shop_id);
            if( $action && in_array($action, [
                'users_out',
                'pin_out'
            ]) )
            {
                switch( $action )
                {
                    case 'users_out':
                        $users = \Aireset\User::where('shop_id', $shop->id)->get();
                        foreach( $users as $user )
                        {
                            $sum = $user->balance;
                            if( $sum <= 0 )
                            {
                                continue;
                            }
                            $transaction = new \Aireset\Transaction();
                            $transaction->user_id = $user->id;
                            $transaction->payeer_id = \Illuminate\Support\Facades\Auth::id();
                            $transaction->type = 'out';
                            $transaction->summ = abs($sum);
                            $transaction->shop_id = $user->shop_id;
                            $transaction->save();
                            $user->update([
                                'balance' => 0,
                                'count_balance' => 0,
                                'count_return' => 0
                            ]);
                            if( \Illuminate\Support\Facades\Auth::user()->hasRole('cashier') && $user->hasRole('user') )
                            {
                                $shop->update(['balance' => $shop->balance + $sum]);
                                $open_shift->increment('balance_in', abs($sum));
                                $open_shift->increment('money_out', abs($sum));
                                $user->increment('total_out', abs($sum));
                            }
                            $user->fresh();
                            if( $user->balance == 0 )
                            {
                                $user->update([
                                    'wager' => 0,
                                    'bonus' => 0
                                ]);
                            }
                            if( $user->count_balance < 0 )
                            {
                                $user->update([
                                    'count_balance' => 0,
                                    'count_return' => 0
                                ]);
                            }
                            if( $user->count_return < 0 )
                            {
                                $user->update(['count_return' => 0]);
                            }
                        }
                        return redirect()->back()->withSuccess(trans('app.balance_updated'));
                        break;
                    case 'pin_out':
                        $pincodes = \Aireset\Pincode::where('shop_id', $shop->id)->get();
                        foreach( $pincodes as $pincode )
                        {
                            $sum = $pincode->nominal;
                            if( $sum <= 0 )
                            {
                                continue;
                            }
                            if( \Illuminate\Support\Facades\Auth::user()->hasRole('cashier') )
                            {
                                $shop->update(['balance' => $shop->balance + $pincode->nominal]);
                                $open_shift->increment('balance_in', $pincode->nominal);
                                $open_shift->increment('money_out', abs($pincode->nominal));
                            }
                            \Aireset\Pincode::where('id', $pincode->id)->delete();
                        }
                        return redirect()->back()->withSuccess(trans('app.balance_updated'));
                        break;
                }
            }
        }
/*        public function security()
        {
            if( config('LicenseDK.APL_INCLUDE_KEY_CONFIG') != 'wi9qydosuimsnls5zoe5q298evkhim0ughx1w16qybs2fhlcpn' )
            {
                return false;
            }
            if( md5_file(base_path() . '/app/Lib/LicenseDK.php') != '3c5aece202a4218a19ec8c209817a74e' )
            {
                return false;
            }
            if( md5_file(base_path() . '/config/LicenseDK.php') != '951a0e23768db0531ff539d246cb99cd' )
            {
                return false;
            }
            return true;
        }*/
    }

}
namespace
{
    function onkXppk3PRSZPackRnkDOJaZ9()
    {
        return 'OkBM2iHjbd6FHZjtvLpNHOc3lslbxTJP6cqXsMdE4evvckFTgS';
    }

}
