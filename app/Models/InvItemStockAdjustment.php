<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvItemStockAdjustment extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_item_stock_adjustment'; 
    protected $primaryKey   = 'stock_adjustment_id';
    
    protected $guarded = [
        'stock_adjustment_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
