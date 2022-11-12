<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_items';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'status',
        'price',
        'notes',
        'custom',
        '$order_id',
        '$order_local_number',
        '$product_id',
        '$preparation_by'
    ];

    public function product() {
        return $this->hasOne(Product::class, 'id', 'product_id')->withTrashed();
    }

    public function order() {
        return $this->hasOne(Order::class, 'id', 'order_id')->withTrashed();
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'preparation_by')->withTrashed();
    }
}
