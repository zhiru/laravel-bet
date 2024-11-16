<?php
namespace Aireset\Http\Controllers\Api\Users
{
    class SessionsController extends \Aireset\Http\Controllers\Api\ApiController
    {
        public function __construct()
        {
            $this->middleware('auth');
            $this->middleware('permission:users.manage');
            $this->middleware('session.database');
        }
        public function index(\Aireset\User $user, \Aireset\Repositories\Session\SessionRepository $sessions)
        {
            return $this->respondWithCollection($sessions->getUserSessions($user->id), new \Aireset\Transformers\SessionTransformer());
        }
    }

}
