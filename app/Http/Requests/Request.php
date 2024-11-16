<?php
namespace Aireset\Http\Requests
{
    abstract class Request extends \Illuminate\Foundation\Http\FormRequest
    {
        public function authorize()
        {
            return true;
        }
    }

}
