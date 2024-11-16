<?php
namespace Aireset\Http\Controllers\Web\Frontend
{
    class HomeController extends \Aireset\Http\Controllers\Controller
    {
        public function index(\Illuminate\Http\Request $request)
        {
            return view('frontend.Default.homepage');
        }
    }
}
