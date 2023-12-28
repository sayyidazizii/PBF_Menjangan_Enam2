<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesCollectionDiscountItem extends Model
{
  /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

     protected $table        = 'sales_collection_item_discount'; 
     protected $primaryKey   = 'collection_item_id';
     
     protected $guarded = [
         'collection_item_id',
     ];
 
     /**
      * The attributes that should be hidden for serialization.
      *
      * @var array
      */
     protected $hidden = [
     ];
}
