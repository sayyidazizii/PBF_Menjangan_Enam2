<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyersAcknowledgmentItem extends Model
{
    protected $table        = 'buyers_acknowledgment_item'; 
    protected $primaryKey   = 'buyers_acknowledgment_item_id';
    
    protected $guarded = [
        'buyers_acknowledgment_item_id',
    ];

    protected $hidden = [
    ];
}
