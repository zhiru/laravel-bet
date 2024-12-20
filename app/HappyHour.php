<?php
namespace Aireset
{
    class HappyHour extends \Illuminate\Database\Eloquent\Model
    {
        protected $table = 'happyhours';
        protected $fillable = [
            'multiplier',
            'wager',
            'time',
            'status',
            'shop_id'
        ];
        public static $values = [
            'multiplier' => [
                'x2',
                'x3',
                'x4',
                'x5',
                'x10'
            ],
            'wager' => [
                'x2',
                'x3',
                'x4',
                'x5',
                'x10'
            ],
            'time' => [
                '00:00 - 01:00',
                '01:00 - 02:00',
                '02:00 - 03:00',
                '03:00 - 04:00',
                '04:00 - 05:00',
                '05:00 - 06:00',
                '06:00 - 07:00',
                '07:00 - 08:00',
                '08:00 - 09:00',
                '09:00 - 10:00',
                '10:00 - 11:00',
                '11:00 - 12:00',
                '12:00 - 13:00',
                '13:00 - 14:00',
                '14:00 - 15:00',
                '15:00 - 16:00',
                '16:00 - 17:00',
                '17:00 - 18:00',
                '18:00 - 19:00',
                '19:00 - 20:00',
                '20:00 - 21:00',
                '21:00 - 22:00',
                '22:00 - 23:00',
                '23:00 - 00:00'
            ]
        ];
        public static function boot()
        {
            parent::boot();
        }
    }

}
