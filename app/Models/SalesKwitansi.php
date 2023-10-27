<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesKwitansi extends Model
{
   /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

     protected $table        = 'sales_kwitansi'; 
     protected $primaryKey   = 'sales_kwitansi_id';
     
     protected $guarded = [
         'sales_kwitansi_id',
     ];
 
     /**
      * The attributes that should be hidden for serialization.
      *
      * @var array
      */
     protected $hidden = [
     ];
}
