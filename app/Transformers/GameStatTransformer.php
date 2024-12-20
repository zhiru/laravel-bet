<?php

namespace Aireset\Transformers;

use League\Fractal\TransformerAbstract;
use Aireset\Repositories\Country\CountryRepository;
use Aireset\Repositories\Role\RoleRepository;
use Aireset\StatGame;

class GameStatTransformer extends TransformerAbstract
{
    public function transform(StatGame $stat)
    {

        return [
            'id' => $stat->id,
            'game' => $stat->game,
            'date_time' => $stat->date_time,
			'user_id' => $stat->user_id,
			'balance' => $stat->balance,
			'bet' => $stat->bet,
			'win' => $stat->win,
            'percent' => $stat->percent,
            'percent_jps' => $stat->percent_jps,
            'percent_jpg' => $stat->percent_jpg,
            'profit' => $stat->profit,
            'denomination' => $stat->denomination,
            'shop_id' => $stat->shop_id,
        ];
    }
}
