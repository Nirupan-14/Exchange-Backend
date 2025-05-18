<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ExchangeRateGbp extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'exchange_rate_gbp';

    protected $fillable = [
        'currency_from',
        'currency_to',
        'rate',
        'updated_at',
        'created_at',
    ];

    public $timestamps = true;
}
