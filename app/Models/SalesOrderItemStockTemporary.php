<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderItemStockTemporary extends Model
{
    protected $table        = 'sales_order_item_stock_temporary'; 
    protected $primaryKey   = 'sales_order_item_stock_temporary_id';
    
    protected $guarded = [
        'sales_order_item_stock_temporary_id',
    ];
}
