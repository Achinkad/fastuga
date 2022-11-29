<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

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
        'payment_type',
        'payment_reference',
        'date',
        'custom',
        '$customer_id',
        '$delivered_by'
    ];

    public function order_item() {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id')->withTrashed();
    }

    public function delivered_by_user() {
        return $this->belongsTo(User::class, 'delivered_by')->withTrashed();
    }
}
