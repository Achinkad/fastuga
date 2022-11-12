<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';
    protected $primaryKey = 'id';

    protected $fillable = [
        'ticket_number',
        'status',
        'total_price',
        'total_paid',
        'total_paid_with_points',
        'points_gained',
        'points_used_to_pay',
        'payment_type'
        'payment_reference',
        'date',
        'custom'
        '$customer_id',
        '$delivered_by',
    ];

    public function customer() {
        return $this->hasOne(Customer::class, 'id', 'customer_id')->withTrashed();
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'delivered_by')->withTrashed();
    }

    public function order_item() {
        return $this->belongsTo(OrderItem::class, 'order_id', 'id');
    }
}
