<?php
namespace Aireset
{
    class Task extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'tasks';
        protected $fillable = [
            'category',
            'user_id',
            'action',
            'item_id',
            'details',
            'ip_address',
            'user_agent',
            'finished'
        ];
        public $timestamps = false;
        public static function boot()
        {
            parent::boot();
        }
    }

}
