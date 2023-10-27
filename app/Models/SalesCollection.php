<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesCollection extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'sales_collection'; 
    protected $primaryKey   = 'collection_id';
    
    protected $guarded = [
        'collection_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
}
