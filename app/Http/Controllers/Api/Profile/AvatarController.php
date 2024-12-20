<?php
namespace Aireset\Http\Controllers\Api\Profile
{
    class AvatarController extends \Aireset\Http\Controllers\Api\ApiController
    {
        private $users = null;
        private $avatarManager = null;
        public function __construct(\Aireset\Repositories\User\UserRepository $users, \Aireset\Services\Upload\UserAvatarManager $avatarManager)
        {
            $this->middleware('auth');
            $this->users = $users;
            $this->avatarManager = $avatarManager;
        }
        public function update(\Aireset\Http\Requests\User\UploadAvatarRawRequest $request)
        {
            $name = $this->avatarManager->uploadAndCropAvatar(auth()->user(), $request->file('file'));
            $user = $this->users->update(auth()->id(), ['avatar' => $name]);
            event(new \Aireset\Events\User\ChangedAvatar());
            return $this->respondWithItem($user, new \Aireset\Transformers\UserTransformer());
        }
        public function updateExternal(\Illuminate\Http\Request $request)
        {
            $this->validate($request, ['url' => 'required|url']);
            $this->avatarManager->deleteAvatarIfUploaded(auth()->user());
            $user = $this->users->update(auth()->id(), ['avatar' => $request->url]);
            event(new \Aireset\Events\User\ChangedAvatar());
            return $this->respondWithItem($user, new \Aireset\Transformers\UserTransformer());
        }
        public function destroy()
        {
            $user = auth()->user();
            $this->avatarManager->deleteAvatarIfUploaded($user);
            $user = $this->users->update($user->id, ['avatar' => null]);
            event(new \Aireset\Events\User\ChangedAvatar());
            return $this->respondWithItem($user, new \Aireset\Transformers\UserTransformer());
        }
    }

}
