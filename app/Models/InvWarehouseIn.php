<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvWarehouseIn extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_warehouse_in'; 
    protected $primaryKey   = 'warehouse_in_id';
    
    protected $guarded = [
        'warehouse_in_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
