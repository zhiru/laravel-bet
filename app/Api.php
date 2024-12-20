<?php
namespace Aireset
{
    class Api extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'apis';
        protected $fillable = [
            'keygen',
            'ip',
            'shop_id',
            'status'
        ];
        public static function boot()
        {
            parent::boot();
        }
        public function shop()
        {
            return $this->belongsTo('Aireset\Shop', 'shop_id');
        }
    }

}
