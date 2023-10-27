<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\AcctBankReceipt;
use App\Models\CoreBranch;
use App\Models\CoreProject;
use Illuminate\Support\Facades\Session;

class AcctBankReceiptReportController extends Controller
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
        
        $branch         = CoreBranch::where('data_state','=',0)->get();

        if(!Session::get('start_date')){
            $start_date     = date('Y-m-d');
        }else{
            $start_date = Session::get('start_date');
        }

        if(!Session::get('end_date')){
            $end_date     = date('Y-m-d');
        }else{
            $end_date = Session::get('end_date');
        }

        if(!Session::get('branch_id')){
            $branch_id     = $branch[0]['branch_id'];
        }else{
            $branch_id = Session::get('branch_id');
        }
        
        
        $acctreceipt    = AcctBankReceipt::select('acct_bank_receipt.*')
        ->where('acct_bank_receipt.data_state','=',0)
        ->where('acct_bank_receipt.bank_receipt_date','>=',$start_date)
        ->where('acct_bank_receipt.bank_receipt_date','<=',$end_date)
        // ->where('acct_bank_receipt.branch_id','=',$branch_id)
        ->orderBy('acct_bank_receipt.bank_receipt_date', 'DESC')
        ->get();


        return view('content/AcctBankReceiptReport/ListAcctBankReceiptReport', compact('acctreceipt','start_date','end_date','branch', 'branch_id'));
    }

    public function filterAcctBankReceiptReport(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $branch_id      = $request->branch_id;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('branch_id', $branch_id);

        return redirect('/report-bank-receipt');
    }
    
    public function getProjectType($project_type_id)
    {
        $project_type = array (
            '0'	=> 'Proyek WBM',
            '1'	=> 'Proyek Non WBM',
        );
        return $project_type[$project_type_id];
    }
}
