<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvWarehouseOut extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_warehouse_out'; 
    protected $primaryKey   = 'warehouse_out_id';
    
    protected $guarded = [
        'warehouse_out_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
