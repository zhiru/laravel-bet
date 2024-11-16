<?php
namespace Aireset\Http\Requests\Page
{
    class CreatePageRequest extends \Aireset\Http\Requests\Request
    {
        public function rules()
        {
            $rules = [
                'path' => 'required|unique:pages|max:255',
                'body' => 'required|min:50',
                'title' => 'required',
                'sub_title' => 'required',
                'description' => 'required',
                'keywords' => 'required'
            ];
            return $rules;
        }
    }

}
