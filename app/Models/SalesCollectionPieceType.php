<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesCollectionPieceType extends Model
{
/**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

     protected $table        = 'sales_collection_piece_type'; 
     protected $primaryKey   = 'sales_collection_piece_type_id';
     
     protected $guarded = [
         'sales_collection_piece_type_id',
     ];
 
     /**
      * The attributes that should be hidden for serialization.
      *
      * @var array
      */
    
}
