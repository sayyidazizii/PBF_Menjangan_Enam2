<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvItemStockCard extends Model
{
    protected $table        = 'inv_item_stock_card'; 
    protected $primaryKey   = 'item_stock_card_id';
    
    protected $guarded = [
        'item_stock_card_id',
    ];
}
