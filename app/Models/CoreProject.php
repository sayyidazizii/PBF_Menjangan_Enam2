<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreProject extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_project'; 
    protected $primaryKey   = 'project_id';
    
    protected $guarded = [
        'project_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
