<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorePackage extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_package'; 
    protected $primaryKey   = 'package_id';
    
    protected $guarded = [
        'package_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
