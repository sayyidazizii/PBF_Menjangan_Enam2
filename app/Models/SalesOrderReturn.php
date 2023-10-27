<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderReturn extends Model
{
    protected $table        = 'sales_order_return'; 
    protected $primaryKey   = 'sales_order_return_id';
    
    protected $guarded = [
        'sales_order_return_id',
    ];

    protected $hidden = [
    ];
}
