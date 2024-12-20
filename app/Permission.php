<?php
namespace Aireset
{
    class Permission extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'permissions';
        protected $fillable = [
            'name',
            'display_name',
            'description'
        ];
        protected $casts = ['removable' => 'boolean'];
        public static function boot()
        {
            parent::boot();
        }
    }

}
