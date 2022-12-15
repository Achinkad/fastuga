<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';
    protected $primaryKey = 'id';

    protected $fillable = [
        'phone',
        'points',
        'nif',
        'default_payment_type',
        'default_payment_reference',
        'custom',
        '$user_id'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function order() {
        return $this->hasMany(Order::class, 'customer_id')->withTrashed();
    }
}
