<?php

// app/Models/ExchangeRate.php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'exchange_rates';

    protected $fillable = ['currency_from', 'currency_to', 'rate', 'updated_at'];
}
