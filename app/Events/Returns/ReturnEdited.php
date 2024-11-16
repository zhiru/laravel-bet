<?php

namespace Aireset\Events\Returns;

use Aireset\Returns;

class ReturnEdited
{
    /**
     * @var Returns
     */
    protected $editedReturn;

    public function __construct(Returns $editedReturn)
    {
        $this->editedReturn = $editedReturn;
    }

    /**
     * @return Returns
     */
    public function getEditedReturn()
    {
        return $this->editedReturn;
    }
}
