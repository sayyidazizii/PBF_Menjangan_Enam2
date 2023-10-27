<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderType extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'purchase_order_type'; 
    protected $primaryKey   = 'purchase_order_type_id';
    
    protected $guarded = [
        'purchase_order_type_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
