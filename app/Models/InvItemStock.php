<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvItemStock extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'inv_item_stock'; 
    protected $primaryKey   = 'item_stock_id';
    
    protected $guarded = [
        'item_stock_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
