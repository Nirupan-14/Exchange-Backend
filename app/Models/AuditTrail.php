<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AuditTrail extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'audit_trails';

    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['user_id', 'action', 'timestamp'];
}
