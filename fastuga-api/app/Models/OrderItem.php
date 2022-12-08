<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'status',
        'price',
        'notes',
        'custom',
        'order_id',
        'order_local_number',
        'product_id',
        'preparation_by'
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }

    public function order() {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'preparation_by')->withTrashed();
    }
}
