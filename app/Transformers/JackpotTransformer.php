<?php

namespace Aireset\Transformers;

use League\Fractal\TransformerAbstract;
use Aireset\Repositories\Country\CountryRepository;
use Aireset\Repositories\Role\RoleRepository;
use Aireset\JPG;

class JackpotTransformer extends TransformerAbstract
{
    public function transform(JPG $jackpot)
    {

        return [
            'id' => $jackpot->id,
            'name' => $jackpot->name,
            'balance' => $jackpot->balance
        ];
    }
}
