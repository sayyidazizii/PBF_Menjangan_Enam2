<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesCollectionDiscount extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

     protected $table        = 'sales_collection_discount'; 
     protected $primaryKey   = 'sales_collection_discount_id';
     
     protected $guarded = [
         'sales_collection_discount_id',
     ];
 
     /**
      * The attributes that should be hidden for serialization.
      *
      * @var array
      */
     protected $hidden = [
     ];
}
