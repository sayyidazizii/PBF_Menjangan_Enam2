<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDeliveryOrderItemStock extends Model
{
    protected $table        = 'sales_delivery_order_item_stock'; 
    protected $primaryKey   = 'sales_delivery_order_item_stock_id';
    
    protected $guarded = [
        'sales_delivery_order_item_stock_id',
    ];
}
