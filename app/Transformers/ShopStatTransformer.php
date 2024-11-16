<?php

namespace Aireset\Transformers;

use League\Fractal\TransformerAbstract;
use Aireset\Repositories\Country\CountryRepository;
use Aireset\Repositories\Role\RoleRepository;
use Aireset\ShopStat;

class ShopStatTransformer extends TransformerAbstract
{
    public function transform(ShopStat $stat)
    {

        return [
            'id' => $stat->id,
            'user_id' => $stat->user_id,
            'type' => $stat->type,
            'sum' => $stat->sum,
            'shop_id' => $stat->shop_id,
            'date_time' => $stat->date_time,
        ];
    }
}
