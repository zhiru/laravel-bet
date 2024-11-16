<?php

namespace Aireset\Listeners\Login;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Aireset\Events\User\LoggedIn;
use Aireset\Repositories\User\UserRepository;

class UpdateLastLoginTimestamp
{
    /**
     * @var UserRepository
     */
    private $users;
    /**
     * @var Guard
     */
    private $guard;

    public function __construct(UserRepository $users, Guard $guard)
    {
        $this->users = $users;
        $this->guard = $guard;
    }

    /**
     * Handle the event.
     *
     * @param LoggedIn $event
     * @return void
     */
    public function handle(LoggedIn $event)
    {
        $this->users->update(
            $this->guard->id(),
            ['last_login' => Carbon::now()]
        );
    }
}
