<?php
namespace Aireset\Http\Controllers\Api
{
    class ActivityController extends ApiController
    {
        public function __construct()
        {
            $this->middleware('auth');
            $this->middleware('permission:users.activity');
        }
        public function index(\Aireset\Http\Requests\Activity\GetActivitiesRequest $request, \Aireset\Repositories\Activity\ActivityRepository $activities)
        {
            $result = $activities->paginateActivities(($request->per_page ?: 20), $request->search);
            return $this->respondWithPagination($result, new \Aireset\Transformers\ActivityTransformer());
        }
    }

}
