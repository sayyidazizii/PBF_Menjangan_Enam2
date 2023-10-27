<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctCheckDisbursementItem extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $table        = 'acct_check_disbursement_item'; 
    protected $primaryKey   = 'check_disbursement_item_id';
    
    protected $guarded = [
        'check_disbursement_item_id',
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
