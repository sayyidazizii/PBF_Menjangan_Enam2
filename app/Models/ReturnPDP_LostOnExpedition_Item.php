<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnPDP_LostOnExpedition_Item extends Model
{
    protected $table        = 'return_pdp_lost_on_expedition_item'; 
    protected $primaryKey   = 'return_pdp_lost_on_expedition_item_id';
    
    protected $guarded = [
        'return_pdp_lost_on_expedition_item_id',
    ];

    protected $hidden = [
    ];
}
