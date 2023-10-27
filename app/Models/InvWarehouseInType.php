<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvWarehouseInType extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_warehouse_in_type'; 
    protected $primaryKey   = 'warehouse_in_type_id';
    
    protected $guarded = [
        'warehouse_in_type_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
