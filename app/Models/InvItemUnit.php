<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvItemUnit extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_item_unit'; 
    protected $primaryKey   = 'item_unit_id';
    
    protected $guarded = [
        'item_unit_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
