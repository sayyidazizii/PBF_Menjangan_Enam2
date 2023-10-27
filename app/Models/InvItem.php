<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvItem extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_item'; 
    protected $primaryKey   = 'item_id';
    
    protected $guarded = [
        'item_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
