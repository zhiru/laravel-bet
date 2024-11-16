<?php
namespace Aireset\Http\Controllers\Api
{
    class OpenShiftController extends ApiController
    {
        public function __construct()
        {
            $this->middleware('auth');
        }
        public function start_shift(\Illuminate\Http\Request $request)
        {
            $user = auth()->user();
            if( $user->hasRole([
                'admin',
                'distributor',
                'cashier'
            ]) )
            {
                $users = \Aireset\User::where([
                    'shop_id' => $user->shop_id,
                    'role_id' => 1
                ])->where('balance', '>', 0)->count();
                if( $users )
                {
                    return $this->errorWrongArgs($users . ' with balance > 0');
                }
                $count = \Aireset\OpenShift::where([
                    'user_id' => auth()->user()->id,
                    'end_date' => null
                ])->first();
                if( $count )
                {
                    $summ = \Aireset\User::where([
                        'shop_id' => $user->shop_id,
                        'role_id' => 1
                    ])->sum('balance');
                    $count->update([
                        'end_date' => \Carbon\Carbon::now(),
                        'last_banks' => $count->banks(),
                        'last_returns' => $count->returns(),
                        'users' => $summ
                    ]);
                }
                $shop = \Aireset\Shop::find($user->shop_id);
                if( !$shop )
                {
                    return $this->errorWrongArgs(trans('app.wrong_shop'));
                }
                if( $count )
                {
                    \Aireset\OpenShift::create([
                        'start_date' => \Carbon\Carbon::now(),
                        'balance' => $shop->balance,
                        'old_banks' => $count->banks(),
                        'user_id' => auth()->user()->id,
                        'shop_id' => $user->shop_id
                    ]);
                }
                else
                {
                    \Aireset\OpenShift::create([
                        'start_date' => \Carbon\Carbon::now(),
                        'balance' => $shop->balance,
                        'user_id' => auth()->user()->id,
                        'shop_id' => $user->shop_id
                    ]);
                }
                return $this->respondWithSuccess();
            }
            return $this->errorWrongArgs('Wrong user. Only cashier');
        }
        public function info(\Illuminate\Http\Request $request)
        {
            if( !$request->shop_id )
            {
                return $this->errorWrongArgs(trans('app.wrong_shop'));
            }
            if( !in_array($request->shop_id, auth()->user()->availableShops()) )
            {
                return $this->errorWrongArgs(trans('app.wrong_shop'));
            }
            $shift = \Aireset\OpenShift::where([
                'shop_id' => $request->shop_id,
                'end_date' => null
            ])->first();
            if( $shift )
            {
                return $this->respondWithArray([
                    'id' => $shift->id,
                    'start_date' => $shift->start_date
                ]);
            }
            return $this->errorWrongArgs(trans('app.shift_not_opened'));
        }
    }

}
