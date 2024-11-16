<?php
namespace Aireset\Http\Controllers\Api\Profile
{
    class SessionsController extends \Aireset\Http\Controllers\Api\ApiController
    {
        public function __construct()
        {
            $this->middleware('auth');
            $this->middleware('session.database');
        }
        public function index(\Aireset\Repositories\Session\SessionRepository $sessions)
        {
            $sessions = $sessions->getUserSessions(auth()->id());
            return $this->respondWithCollection($sessions, new \Aireset\Transformers\SessionTransformer());
        }
    }

}
