<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreGrade extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'core_grade'; 
    protected $primaryKey   = 'grade_id';
    
    protected $guarded = [
        'grade_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
