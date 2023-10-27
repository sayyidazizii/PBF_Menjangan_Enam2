<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesCollectionGiro extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'sales_collection_giro'; 
    protected $primaryKey   = 'collection_giro_id';
    
    protected $guarded = [
        'collection_giro_id',
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
