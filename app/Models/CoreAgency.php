<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreAgency extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_agency'; 
    protected $primaryKey   = 'agency_id';
    
    protected $guarded = [
        'agency_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
