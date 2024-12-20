<?php
namespace Aireset\Http\Controllers\Api
{
    class SessionsController extends ApiController
    {
        private $sessions = null;
        public function __construct(\Aireset\Repositories\Session\SessionRepository $sessions)
        {
            $this->middleware('auth');
            $this->middleware('session.database');
            $this->sessions = $sessions;
        }
        public function show($session)
        {
            $this->authorize('manage-session', $session);
            return $this->respondWithItem($session, new \Aireset\Transformers\SessionTransformer());
        }
        public function destroy($session)
        {
            $this->authorize('manage-session', $session);
            $this->sessions->invalidateSession($session->id);
            return $this->respondWithSuccess();
        }
    }

}
