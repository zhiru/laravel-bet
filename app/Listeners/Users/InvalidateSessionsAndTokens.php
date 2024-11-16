<?php

namespace Aireset\Listeners\Users;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Aireset\Events\User\Banned;
use Aireset\Events\User\LoggedIn;
use Aireset\Repositories\Session\SessionRepository;
use Aireset\Repositories\User\UserRepository;
use Aireset\Services\Auth\Api\Token;

class InvalidateSessionsAndTokens
{
    /**
     * @var SessionRepository
     */
    private $sessions;

    public function __construct(SessionRepository $sessions)
    {
        $this->sessions = $sessions;
    }

    /**
     * Handle the event.
     *
     * @param Banned $event
     * @return void
     */
    public function handle(Banned $event)
    {
        $user = $event->getBannedUser();

        $this->sessions->invalidateAllSessionsForUser($user->id);

        Token::where('user_id', $user->id)->delete();
    }
}
