<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreCustomer extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_customer'; 
    protected $primaryKey   = 'customer_id';
    
    protected $guarded = [
        'customer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
