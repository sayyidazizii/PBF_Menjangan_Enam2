<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesDeliveryNoteItemStock extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'sales_delivery_note_item_stock'; 
    protected $primaryKey   = 'sales_delivery_note_item_stock_id';
    
    protected $guarded = [
        'sales_delivery_note_item_stock_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
