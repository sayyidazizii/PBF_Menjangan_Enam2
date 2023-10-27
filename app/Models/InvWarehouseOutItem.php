<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvWarehouseOutItem extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_warehouse_out_item'; 
    protected $primaryKey   = 'warehouse_out_item_id';
    
    protected $guarded = [
        'warehouse_out_item_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
