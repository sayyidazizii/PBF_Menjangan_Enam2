<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\AcctAccount;
use App\Models\AcctAccountType;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class AcctAccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $acctaccount    = AcctAccount::select('acct_account.*')->where('acct_account.data_state','=',0)->orderBy('acct_account.account_code', 'ASC')->get();


        return view('content/AcctAccount/ListAcctAccount', compact('acctaccount'));
    }

    public function addAcctAccount()
    {
        $acctaccounttype    = AcctAccountType::where('acct_account_type.data_state', '0')
        ->pluck('account_type_name', 'account_type_id');

        $acctaccountsettingstatus = array (
            array(  'account_default_status'	=> '1',
                    'account_default_name'	    => 'Debit',
            ),
            array(  'account_default_status'	=> '0',
                    'account_default_name'	    => 'Kredit',
            ),
        );
        $acctaccountsettingstatus = collect($acctaccountsettingstatus);
        $acctaccountsettingstatus = $acctaccountsettingstatus->pluck('account_default_name', 'account_default_status');

        $acctaccountcode  = AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS account_code'))
        ->where('acct_account.data_state', '0')
        ->orderBy('account_id','DESC')
        ->pluck('account_code', 'account_id');

        return view('content/AcctAccount/FormAddAcctAccount', compact('acctaccounttype','acctaccountsettingstatus', 'acctaccountcode'));
    }

    public function editAcctAccount($account_id)
    {
        $acctaccounttype    = AcctAccountType::where('acct_account_type.data_state','=','0')->get()->pluck('account_type_name','account_type_id');
        $datacollection = collect([
            ['account_default_status' => '1', 'account_default_name' => 'Debit'],
            ['account_default_status' => '0', 'account_default_name' => 'Kredit'],
        ]);
        $acctaccountsettingstatus   = $datacollection->pluck('account_default_name','account_default_status');
        $acctaccountcode            = AcctAccount::where('acct_account.data_state','=','0')->select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))->get()->pluck('full_name','account_id');
        $acctaccount                = AcctAccount::where('account_id','=',$account_id)->first();

        $account_type_id        = $acctaccount['account_type_id'];
        $account_default_status = $acctaccount['account_default_status'];
        $parent_account_id      = $acctaccount['parent_account_id'];

        return view('content/AcctAccount/FormEditAcctAccount', compact('acctaccounttype','acctaccountsettingstatus', 'acctaccountcode', 'acctaccount', 'account_type_id', 'account_default_status', 'parent_account_id'));
    }
    
    public function getDefaultStatus($account_default_status)
    {
        $default_status = array (
            '1'	=> 'Debit',
            '0'	=> 'Kredit',
        );
        return $default_status[$account_default_status];
    }

    public function processAddAcctAccount(Request $request)
    {
        $fields = array (
            'account_code'           => $request->account_code,
            'account_name'           => $request->account_name,
            'account_type_id'        => $request->account_type_id,
            'account_default_status' => $request->account_default_status,
            'parent_account_id'      => $request->parent_account_id,
        );

        if(empty($fields['parent_account_id'])){
            $account = AcctAccount::create([
                'account_code'              => $fields['account_code'],
                'account_name'              => $fields['account_name'],
                'account_type_id'           => $fields['account_type_id'],
                'account_default_status'    => $fields['account_default_status'],
                'parent_account_id'         => $fields['parent_account_id'],
                'parent_account_status'		=> 1,
                'created_id'				=> Auth::id(),
            ]);
        } else {
            $account = AcctAccount::create([
                'account_code'              => $fields['account_code'],
                'account_name'              => $fields['account_name'],
                'account_type_id'           => $fields['account_type_id'],
                'account_default_status'    => $fields['account_default_status'],
                'parent_account_id'         => $fields['parent_account_id'],
                'parent_account_status'		=> 0,
                'created_id'				=> Auth::id(),
            ]);
        }


        $msg = 'Tambah No Perkiraan Berhasil';
        return redirect('/account/add')->with('msg',$msg);
    }

    public function processEditAcctAccount(Request $request, $account_id)
    {
        $account = AcctAccount::findOrFail($account_id);
        
        $account->account_code = $request->account_code;
        $account->account_name = $request->account_name;
        $account->account_type_id = $request->account_type_id;
        $account->account_default_status = $request->account_default_status;
        $account->account_remark = $request->account_remark;

        if(empty($request->parent_account_id)){
            $account->parent_account_status = 1;
            $account->parent_account_id = $request->parent_account_id;
        } else {
            $account->parent_account_status = 0;
            $account->parent_account_id = $request->parent_account_id;
        }

        if($account->save())
        {
            $msg = $request->parent_account_id;
        }else{
            $msg = 'Edit No Perkiraan Gagal';
        }

        return redirect('/account')->with('msg',$msg);
    }
    
    public function deleteAcctAccount($account_id)
    {
        $account = AcctAccount::findOrFail($account_id);
        $account->data_state = 1;
        if($account->save())
        {
            $msg = 'Hapus System User Berhasil';
        }else{
            $msg = 'Hapus System User Gagal';
        }

        return redirect('/account')->with('msg',$msg);
    }
}
