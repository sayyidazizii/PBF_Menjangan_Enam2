<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreProjectCategory extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_project_category'; 
    protected $primaryKey   = 'project_category_id';
    
    protected $guarded = [
        'project_category_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
