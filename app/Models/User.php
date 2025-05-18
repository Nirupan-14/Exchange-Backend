<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class User extends Model implements Authenticatable
{
    use AuthenticableTrait;

    protected $connection = 'mongodb';
    protected $collection = 'auth';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
}
