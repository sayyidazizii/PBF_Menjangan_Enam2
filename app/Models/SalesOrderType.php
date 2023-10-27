<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderType extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'sales_order_type'; 
    protected $primaryKey   = 'sales_order_type_id';
    
    protected $guarded = [
        'sales_order_type_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
