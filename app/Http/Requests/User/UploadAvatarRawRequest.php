<?php
namespace Aireset\Http\Requests\User
{
    class UploadAvatarRawRequest extends \Aireset\Http\Requests\BinaryFileUploadRequest
    {
        public function rules()
        {
            return ['file' => 'required|image'];
        }
        public function messages()
        {
            return ['file.required' => 'The file is required.'];
        }
    }

}
