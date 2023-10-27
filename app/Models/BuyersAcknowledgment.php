<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyersAcknowledgment extends Model
{
    protected $table        = 'buyers_acknowledgment'; 
    protected $primaryKey   = 'buyers_acknowledgment_id';
    
    protected $guarded = [
        'buyers_acknowledgment_id',
    ];

    protected $hidden = [
    ];
}
