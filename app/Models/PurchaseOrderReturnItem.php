<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderReturnItem extends Model
{
/**
 * The attributes that are mass assignable.
 *
 * @var string[]
 */

    protected $table        = 'purchase_order_return_item'; 
    protected $primaryKey   = 'purchase_order_return_item_id';
    
    protected $guarded = [
        'purchase_order_return_item_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
    *
    * @var array
    */
    protected $hidden = [
    ];
}
