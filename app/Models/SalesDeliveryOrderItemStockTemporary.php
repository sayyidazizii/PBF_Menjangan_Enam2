<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDeliveryOrderItemStockTemporary extends Model
{
    protected $table        = 'sales_delivery_order_item_stock_temporary'; 
    protected $primaryKey   = 'sales_delivery_order_item_stock_temporary_id';
    
    protected $guarded = [
        'sales_delivery_order_item_stock_temporary_id',
    ];
}
