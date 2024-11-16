<?php
namespace Aireset\Http\Controllers\Api\Users
{
    class AvatarController extends \Aireset\Http\Controllers\Api\ApiController
    {
        private $users = null;
        private $avatarManager = null;
        public function __construct(\Aireset\Repositories\User\UserRepository $users, \Aireset\Services\Upload\UserAvatarManager $avatarManager)
        {
            $this->middleware('auth');
            $this->middleware('permission:users.manage');
            $this->users = $users;
            $this->avatarManager = $avatarManager;
        }
        public function update(\Aireset\User $user, \Aireset\Http\Requests\User\UploadAvatarRawRequest $request)
        {
            $name = $this->avatarManager->uploadAndCropAvatar($user, $request->file('file'));
            $user = $this->users->update($user->id, ['avatar' => $name]);
            event(new \Aireset\Events\User\UpdatedByAdmin($user));
            return $this->respondWithItem($user, new \Aireset\Transformers\UserTransformer());
        }
        public function updateExternal(\Aireset\User $user, \Illuminate\Http\Request $request)
        {
            $this->validate($request, ['url' => 'required|url']);
            $this->avatarManager->deleteAvatarIfUploaded($user);
            $user = $this->users->update($user->id, ['avatar' => $request->url]);
            event(new \Aireset\Events\User\UpdatedByAdmin($user));
            return $this->respondWithItem($user, new \Aireset\Transformers\UserTransformer());
        }
        public function destroy(\Aireset\User $user)
        {
            $this->avatarManager->deleteAvatarIfUploaded($user);
            $user = $this->users->update($user->id, ['avatar' => null]);
            event(new \Aireset\Events\User\UpdatedByAdmin($user));
            return $this->respondWithItem($user, new \Aireset\Transformers\UserTransformer());
        }
    }

}
