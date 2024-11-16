<?php
namespace Aireset\Http\Controllers\Api\Users
{
    class ActivityController extends \Aireset\Http\Controllers\Api\ApiController
    {
        private $activities = null;
        public function __construct(\Aireset\Repositories\Activity\ActivityRepository $activities)
        {
            $this->middleware('auth');
            $this->middleware('permission:users.activity');
            $this->activities = $activities;
        }
        public function index(\Aireset\User $user, \Aireset\Http\Requests\Activity\GetActivitiesRequest $request)
        {
            $activities = $this->activities->paginateActivitiesForUser($user->id, ($request->per_page ?: 100000), $request->search);
            return $this->respondWithPagination($activities, new \Aireset\Transformers\ActivityTransformer());
        }
    }

}
