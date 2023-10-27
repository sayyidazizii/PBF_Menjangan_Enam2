<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvWarehouseOutType extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_warehouse_out_type'; 
    protected $primaryKey   = 'warehouse_out_type_id';
    
    protected $guarded = [
        'warehouse_out_type_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
