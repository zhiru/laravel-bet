<?php
namespace Aireset
{
    class GameCategory extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'game_categories';
        protected $fillable = [
            'game_id',
            'category_id'
        ];
        public $timestamps = false;
        public static function boot()
        {
            parent::boot();
        }
        public function category()
        {
            return $this->belongsTo('Aireset\Category');
        }
        public function game()
        {
            return $this->belongsTo('Aireset\Game');
        }
    }

}
