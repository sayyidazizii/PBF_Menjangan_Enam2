<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctCashReceipt extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_cash_receipt'; 
    protected $primaryKey   = 'cash_receipt_id';
    
    protected $guarded = [
        'cash_receipt_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
}
