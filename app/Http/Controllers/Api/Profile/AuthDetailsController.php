<?php
namespace Aireset\Http\Controllers\Api\Profile
{
    class AuthDetailsController extends \Aireset\Http\Controllers\Api\ApiController
    {
        public function __construct()
        {
            $this->middleware('auth');
        }
        public function update(\Aireset\Http\Requests\User\UpdateProfileLoginDetailsRequest $request, \Aireset\Repositories\User\UserRepository $users)
        {
            $user = $request->user();
            $data = $request->only([
                'username',
                'password'
            ]);
            $user = $users->update($user->id, $data);
            return $this->respondWithItem($user, new \Aireset\Transformers\UserTransformer());
        }
    }

}
