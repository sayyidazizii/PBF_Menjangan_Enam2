<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctBankDisbursement extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_bank_disbursement'; 
    protected $primaryKey   = 'bank_disbursement_id';
    
    protected $guarded = [
        'bank_disbursement_id',
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
