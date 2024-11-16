<?php

namespace Aireset\Events\Jackpot;

use Aireset\Jackpot;

class DeleteJackpot
{
    /**
     * @var Returns
     */
    protected $DeleteJackpot;

    public function __construct(Jackpot $DeleteJackpot)
    {
        $this->DeleteJackpot = $DeleteJackpot;
    }

    /**
     * @Jackpot Jackpots
     */
    public function getDeleteJackpot()
    {
        return $this->DeleteJackpot;
    }
}
