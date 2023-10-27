<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderReturnItem extends Model
{
    protected $table        = 'sales_order_return_item'; 
    protected $primaryKey   = 'sales_order_return_item_id';
    
    protected $guarded = [
        'sales_order_return_item_id',
    ]; 
}
