<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcctAccount extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    
    protected $table        = 'acct_account'; 
    protected $primaryKey   = 'account_id';
    
    protected $guarded = [
        'account_id',
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
