<?php
namespace Aireset
{
    class BonusPreset extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'bonus_preset';
        public static function boot()
        {
            parent::boot();
        }
    }

}
