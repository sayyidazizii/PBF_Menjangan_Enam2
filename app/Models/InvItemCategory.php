<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvItemCategory extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_item_category'; 
    protected $primaryKey   = 'item_category_id';
    
    protected $guarded = [
        'item_category_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
