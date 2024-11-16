<?php
namespace Aireset
{
    class ShopUser extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'shops_user';
        protected $fillable = [
            'shop_id',
            'user_id'
        ];
        public $timestamps = false;
        public static function boot()
        {
            parent::boot();
        }
        public function shop()
        {
            return $this->belongsTo('Aireset\Shop', 'shop_id');
        }
        public function user()
        {
            return $this->belongsTo('Aireset\User', 'user_id');
        }
    }

}
