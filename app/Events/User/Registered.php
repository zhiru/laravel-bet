<?php

namespace Aireset\Events\User;

use Aireset\User;

class Registered
{
    /**
     * @var User
     */
    private $registeredUser;

    /**
     * Registered constructor.
     * @param User $registeredUser
     */
    public function __construct(User $registeredUser)
    {
        $this->registeredUser = $registeredUser;
    }

    /**
     * @return User
     */
    public function getRegisteredUser()
    {
        return $this->registeredUser;
    }
}
