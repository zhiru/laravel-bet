<?php
namespace Aireset\Http\Middleware
{
    class TrimStrings extends \Illuminate\Foundation\Http\Middleware\TrimStrings
    {
        protected $except = [
            'password',
            'password_confirmation'
        ];
    }

}
