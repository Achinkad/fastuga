<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Sanctum\HasApiTokens;
// Add Laravel Tokens
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'photo_url',
        'custom'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'blocked'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime'
    ];

    public function customer() {
        return $this->hasOne(Customer::class, 'user_id')->withTrashed();
    }

    public function preparation_order() {
        return $this->hasMany(OrderItem::class, 'preparation_id')->withTrashed();
    }

    public function delivered_order() {
        return $this->hasMany(Order::class, 'delivered_id')->withTrashed();
    }
}
