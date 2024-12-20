<?php

namespace Aireset\Transformers;

use League\Fractal\TransformerAbstract;
use Aireset\Repositories\Country\CountryRepository;
use Aireset\Repositories\Role\RoleRepository;
use Aireset\BankStat;

class BankStatTransformer extends TransformerAbstract
{
    public function transform(BankStat $stat)
    {

        return [
            'id' => $stat->id,
            'name' => $stat->name,
            'user_id' => $stat->user_id,
            'type' => $stat->type,
            'sum' => $stat->sum,
            'old' => $stat->old,
            'new' => $stat->new,
            'shop_id' => $stat->shop_id,
            'created_at' => $stat->created_at,
        ];
    }
}
