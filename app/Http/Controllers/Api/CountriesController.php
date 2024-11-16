<?php
namespace Aireset\Http\Controllers\Api
{
    class CountriesController extends ApiController
    {
        private $countries = null;
        public function __construct(\Aireset\Repositories\Country\CountryRepository $countries)
        {
            $this->middleware('auth');
            $this->countries = $countries;
        }
        public function index()
        {
            return $this->respondWithCollection($this->countries->all(), new \Aireset\Transformers\CountryTransformer());
        }
    }

}
