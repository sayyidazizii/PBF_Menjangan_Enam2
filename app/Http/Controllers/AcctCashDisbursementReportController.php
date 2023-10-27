<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\AcctCashDisbursement;
use App\Models\CoreBranch;
use App\Models\CoreProject;
use Illuminate\Support\Facades\Session;

class AcctCashDisbursementReportController extends Controller
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
        
        
        $acctdisbursement    = AcctCashDisbursement::select('acct_cash_disbursement.*')
        ->where('acct_cash_disbursement.data_state','=',0)
        ->where('acct_cash_disbursement.cash_disbursement_date','>=',$start_date)
        ->where('acct_cash_disbursement.cash_disbursement_date','<=',$end_date)
        ->where('acct_cash_disbursement.branch_id','=',$branch_id)
        ->orderBy('acct_cash_disbursement.cash_disbursement_date', 'DESC')
        ->get();


        return view('content/AcctCashDisbursementReport/ListAcctCashDisbursementReport', compact('acctdisbursement','start_date','end_date','branch', 'branch_id'));
    }

    public function filterAcctCashDisbursementReport(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $branch_id      = $request->branch_id;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('branch_id', $branch_id);

        return redirect('/report-cash-disbursement');
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
