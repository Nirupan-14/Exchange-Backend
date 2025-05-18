<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ExchangeRateAud extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'exchange_rate_aud'; // your MongoDB collection

    protected $fillable = [
        'currency_from',
        'currency_to',
        'rate',
        'updated_at',
        'created_at',
    ];

    public $timestamps = true;
}
