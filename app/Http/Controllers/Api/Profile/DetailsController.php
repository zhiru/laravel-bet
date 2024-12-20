<?php
namespace Aireset\Http\Controllers\Api\Profile
{
    class DetailsController extends \Aireset\Http\Controllers\Api\ApiController
    {
        public function __construct()
        {
            $this->middleware('auth');
        }
        public function index()
        {
            return $this->respondWithItem(auth()->user(), new \Aireset\Transformers\UserTransformer());
        }
        public function update(\Aireset\Http\Requests\User\UpdateProfileDetailsRequest $request, \Aireset\Repositories\User\UserRepository $users)
        {
            $user = $request->user();
            $data = collect($request->all());
            $data = $data->only([
                'first_name',
                'last_name',
                'birthday',
                'phone',
                'address'
            ])->toArray();
            $user = $users->update($user->id, $data);
            event(new \Aireset\Events\User\UpdatedProfileDetails());
            return $this->respondWithItem($user, new \Aireset\Transformers\UserTransformer());
        }
        public function returns(\Aireset\Http\Requests\User\UpdateProfileDetailsRequest $request, \Aireset\Repositories\User\UserRepository $users)
        {
            $user = $request->user();
            $sum = floatval($user->count_return);
            if( $sum > 0 )
            {
                $user->increment('balance', $sum);
                $user->increment('count_balance', $sum);
                $user->update(['count_return' => 0]);
                \Aireset\Transaction::create([
                    'user_id' => $user->id,
                    'summ' => $sum,
                    'system' => 'Refund'
                ]);
                return response()->json([
                    'success' => 'success',
                    'value' => $sum
                ], 200);
            }
            return response()->json([
                'success' => 'success',
                'value' => 0
            ], 200);
        }
    }

}
