<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesDeliveryOrder extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'sales_delivery_order'; 
    protected $primaryKey   = 'sales_delivery_order_id';
    
    protected $guarded = [
        'sales_delivery_order_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
