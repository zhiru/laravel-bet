<?php

namespace Aireset\Events\Returns;

use Aireset\Returns;

class NewReturn
{
    /**
     * @var Returns
     */
    protected $NewReturn;

    public function __construct(Returns $NewReturn)
    {
        $this->NewReturn = $NewReturn;
    }

    /**
     * @return Returns
     */
    public function getNewReturn()
    {
        return $this->NewReturn;
    }
}
