<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderItemStock extends Model
{
    protected $table        = 'sales_order_item_stock'; 
    protected $primaryKey   = 'sales_order_item_stock_id';
    
    protected $guarded = [
        'sales_order_item_stock_id',
    ];
}
