<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreSupplier extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_supplier'; 
    protected $primaryKey   = 'supplier_id';
    
    protected $guarded = [
        'supplier_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
