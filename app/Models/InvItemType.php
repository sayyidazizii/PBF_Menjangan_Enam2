<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvItemType extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_item_type'; 
    protected $primaryKey   = 'item_type_id';
    
    protected $guarded = [
        'item_type_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
