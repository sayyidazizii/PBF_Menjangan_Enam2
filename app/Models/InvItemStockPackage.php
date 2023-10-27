<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvItemStockPackage extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_item_stock_package'; 
    protected $primaryKey   = 'item_stock_package_id';
    
    protected $guarded = [
        'item_stock_package_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
