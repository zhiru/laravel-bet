<?php

namespace Aireset\Events\Jackpot;

use Aireset\Jackpot;

class JackpotEdited
{
    /**
     * @var Jackpots
     */
    protected $editedJackpot;

    public function __construct(Jackpot $editedJackpot)
    {
        $this->editedJackpot = $editedJackpot;
    }

    /**
     * @Jackpot Jackpots
     */
    public function getEditedJackpot()
    {
        return $this->editedJackpot;
    }

}
