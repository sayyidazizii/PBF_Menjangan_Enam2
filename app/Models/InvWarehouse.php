<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvWarehouse extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_warehouse'; 
    protected $primaryKey   = 'warehouse_id';
    
    protected $guarded = [
        'warehouse_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
