<?php

namespace Aireset\Events\Shop;

use Aireset\Shop;

class ShopEdited
{
    /**
     * @var User
     */
    protected $editedShop;

    public function __construct(Shop $editedShop)
    {
        $this->editedShop = $editedShop;
    }

    /**
     * @return User
     */
    public function getEditedShop()
    {
        return $this->editedShop;
    }
}
