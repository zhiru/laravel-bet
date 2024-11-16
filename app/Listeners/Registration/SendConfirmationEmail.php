<?php

namespace Aireset\Listeners\Registration;

use Aireset\Events\User\Registered;
use Aireset\Notifications\EmailConfirmation;
use Aireset\Repositories\User\UserRepository;

class SendConfirmationEmail
{
    /**
     * @var UserRepository
     */
    private $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        if (! settings('use_email')) {
            return;
        }

        $user = $event->getRegisteredUser();

        $token = str_random(60);
        $this->users->update($user->id, [
            'confirmation_token' => $token
        ]);

        $user->notify(new EmailConfirmation($token));
    }
}
