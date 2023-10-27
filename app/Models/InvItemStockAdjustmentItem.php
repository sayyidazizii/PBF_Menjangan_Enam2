<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvItemStockAdjustmentItem extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_item_stock_adjustment_item'; 
    protected $primaryKey   = 'stock_adjustment_item_id';
    
    protected $guarded = [
        'stock_adjustment_item_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
