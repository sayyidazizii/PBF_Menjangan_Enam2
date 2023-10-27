<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnPDP extends Model
{
    protected $table        = 'return_pdp'; 
    protected $primaryKey   = 'return_pdp_id';
    
    protected $guarded = [
        'return_pdp_id',
    ];

    protected $hidden = [
    ];
}
