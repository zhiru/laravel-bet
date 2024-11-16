<?php

namespace Aireset\Events\Game;

use Aireset\Game;

class NewGame
{
    /**
     * @var Returns
     */
    protected $NewGame;

    public function __construct(Game $NewGame)
    {
        $this->NewGame = $NewGame;
    }

    /**
     * @Game Games
     */
    public function getNewGame()
    {
        return $this->NewGame;
    }
}
