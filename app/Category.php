<?php
namespace Aireset
{
    class Category extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'categories';
        protected $fillable = [
            'title',
            'parent',
            'position',
            'href',
            'original_id',
            'shop_id'
        ];
        public $timestamps = false;
        public static function boot()
        {
            parent::boot();
            self::deleting(function($model)
            {
                GameCategory::where('category_id', $model->id)->delete();
            });
        }
        public function inner()
        {
            $shop_id = (\Auth::check() ? \Auth::user()->shop_id : 0);
            return $this->hasMany('Aireset\Category', 'parent')->orderBy('position', 'ASC');
        }
        public function parentOne()
        {
            return $this->hasOne('Aireset\Category', 'id', 'parent');
        }
        public function games()
        {
            return $this->hasMany('Aireset\GameCategory', 'category_id');
        }
    }

}
