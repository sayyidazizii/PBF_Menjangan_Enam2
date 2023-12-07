<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use App\Models\AcctAccount;
use App\Models\AcctBankDisbursement;
use App\Models\AcctBankDisbursementItem;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\CoreProject;
use App\Models\CoreProjectCategory;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\SalesCustomer;
use App\Models\SystemLogUser;
use App\Models\User;
use App\Models\CoreCustomer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcctBankDisbursementController extends Controller
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
        Session::forget('acctdisbursementelements');
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
        $acctdisbursement    = AcctBankDisbursement::select('acct_bank_disbursement.*')
        ->where('acct_bank_disbursement.data_state','=',0)
        ->where('acct_bank_disbursement.bank_disbursement_date','>=',$start_date)
        ->where('acct_bank_disbursement.bank_disbursement_date','<=',$end_date)
        ->orderBy('acct_bank_disbursement.bank_disbursement_date', 'DESC')
        ->get();

        return view('content/AcctBankDisbursement/ListAcctBankDisbursement', compact('acctdisbursement','start_date','end_date'));
    }

    public function addAcctBankDisbursement()
    {
        $acctdisbursementelements= Session::get('acctdisbursementelements');
        $acctdisbursementitem = Session::get('dataacctdisbursementitem');
        
        $branch_id = User::select('branch_id')->where('user_id','=',Auth::id())->first();

        $salescustomer			= SalesCustomer::select('sales_customer.customer_id', 'sales_customer.customer_name')
        ->where('sales_customer.data_state', '=', 0)
        ->where('sales_customer.branch_id', '=', $branch_id['branch_id'])
        ->get()
        ->pluck('sales_customer.customer_name', 'sales_customer.customer_id');

        $acctaccount        = AcctAccount::where('acct_account.data_state','=','0')
        ->where('parent_account_status','=',0)
        ->select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS account_code'))
        ->pluck('account_code', 'account_id');

        $preference_company = PreferenceCompany::first();

        $cash_account_id 	= $preference_company['cash_account_id'];
        $bank_account_id	= $preference_company['bank_account_id'];

        $acctaccountcashbank	= AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS account_code'))
        ->where('data_state', '=', 0)
        ->where('parent_account_id', '=', $cash_account_id)
        ->orWhere('parent_account_id', '=', $bank_account_id)
        ->get()
        ->pluck('account_code', 'account_id');

        $corecustomer           = CoreCustomer::where('data_state','=',0)->pluck('customer_name', 'customer_id');


        return view('content/AcctBankDisbursement/FormAddAcctBankDisbursement', compact('corecustomer', 'salescustomer', 'acctaccount', 'acctaccountcashbank', 'acctdisbursementitem', 'acctdisbursementelements'));
    }

    public function elements_add(Request $request){
        $acctdisbursementelements= Session::get('acctdisbursementelements');
        if(!$acctdisbursementelements || $acctdisbursementelements == ''){
            $acctdisbursementelements['bank_disbursement_date']     = '';
            $acctdisbursementelements['account_id']           = '';   
            $acctdisbursementelements['customer_id']           = '';   
            $acctdisbursementelements['bank_disbursement_title']  = '';
            $acctdisbursementelements['bank_disbursement_description']   = '';
        }
        $acctdisbursementelements[$request->name] = $request->value;
        Session::put('acctdisbursementelements', $acctdisbursementelements);
    }

    public function detailAcctBankDisbursement($bank_disbursement_id)
    {
        $acctdisbursementdetail = AcctBankDisbursement::select('acct_bank_disbursement.*', 'acct_account.account_code', 'acct_account.account_name')
        ->where('bank_disbursement_id', '=', $bank_disbursement_id)
        ->join('acct_account', 'acct_bank_disbursement.account_id', '=', 'acct_account.account_id')
        ->first();

        $acctdisbursementitem = AcctBankDisbursementItem::select('acct_bank_disbursement_item.bank_disbursement_item_id', 'acct_bank_disbursement_item.bank_disbursement_item_title', 'acct_bank_disbursement_item.bank_disbursement_item_amount','acct_account.account_code', 'acct_account.account_name')
        ->join('acct_account', 'acct_bank_disbursement_item.account_id', '=', 'acct_account.account_id')
        ->where('acct_bank_disbursement_item.bank_disbursement_id', '=', $bank_disbursement_id)
        ->get();
        
        $branch_id = User::select('branch_id')->where('user_id','=',Auth::id())->first();

        $salescustomer			= SalesCustomer::select('sales_customer.customer_id', 'sales_customer.customer_name')
        ->where('sales_customer.data_state', '=', 0)
        ->where('sales_customer.branch_id', '=', $branch_id['branch_id'])
        ->get()
        ->pluck('sales_customer.customer_name', 'sales_customer.customer_id');

        $acctaccount        = AcctAccount::where('acct_account.data_state','=','0')
        ->where('parent_account_status','=',0)
        ->select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS account_code'))
        ->get();

        $preference_company = PreferenceCompany::first();

        $cash_account_id 	= $preference_company['cash_account_id'];
        $bank_account_id	= $preference_company['bank_account_id'];

        $acctaccountcashbank	= AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS account_code'))
        ->where('data_state', '=', 0)
        ->where('parent_account_id', '=', $cash_account_id)
        ->orWhere('parent_account_id', '=', $bank_account_id)
        ->get()
        ->pluck('account_code', 'account_id');

        return view('content/AcctBankDisbursement/FormDetailAcctBankDisbursement', compact('salescustomer', 'acctaccount', 'acctaccountcashbank', 'acctdisbursementitem', 'acctdisbursementdetail', 'bank_disbursement_id'));
    }

    public function voidAcctBankDisbursement($bank_disbursement_id)
    {
        $branch_id = User::select('branch_id')->where('user_id','=',Auth::id())->first();
        
        $acctdisbursementdetail = AcctBankDisbursement::select('acct_bank_disbursement.*', 'acct_account.account_code', 'acct_account.account_name')
        ->where('bank_disbursement_id', '=', $bank_disbursement_id)
        ->join('acct_account', 'acct_bank_disbursement.account_id', '=', 'acct_account.account_id')
        ->first();

        $acctdisbursementitem = AcctBankDisbursementItem::select('acct_bank_disbursement_item.bank_disbursement_item_id', 'acct_bank_disbursement_item.bank_disbursement_item_title', 'acct_bank_disbursement_item.bank_disbursement_item_amount','acct_account.account_code', 'acct_account.account_name')
        ->join('acct_account', 'acct_bank_disbursement_item.account_id', '=', 'acct_account.account_id')
        ->where('acct_bank_disbursement_item.bank_disbursement_id', '=', $bank_disbursement_id)
        ->get();

        $salescustomer			= SalesCustomer::select('sales_customer.customer_id', 'sales_customer.customer_name')
        ->where('sales_customer.data_state', '=', 0)
        ->where('sales_customer.branch_id', '=', $branch_id['branch_id'])
        ->get()
        ->pluck('sales_customer.customer_name', 'sales_customer.customer_id');

        $acctaccount        = AcctAccount::where('acct_account.data_state','=','0')
        ->where('parent_account_status','=',0)
        ->select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS account_code'))
        ->get();

        $preference_company = PreferenceCompany::first();

        $cash_account_id 	= $preference_company['cash_account_id'];
        $bank_account_id	= $preference_company['bank_account_id'];

        $acctaccountcashbank	= AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS account_code'))
        ->where('data_state', '=', 0)
        ->where('parent_account_id', '=', $cash_account_id)
        ->orWhere('parent_account_id', '=', $bank_account_id)
        ->get()
        ->pluck('account_code', 'account_id');

        return view('content/AcctBankDisbursement/FormVoidAcctBankDisbursement', compact( 'salescustomer', 'acctaccount', 'acctaccountcashbank', 'acctdisbursementitem', 'acctdisbursementdetail', 'bank_disbursement_id'));
    }

    public function filterAcctBankDisbursement(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/bank-disbursement');
    }
    
    public function getProjectType($project_type_id)
    {
        $project_type = array (
            '0'	=> 'Proyek WBM',
            '1'	=> 'Proyek Non WBM',
        );
        return $project_type[$project_type_id];
    }

    public function selectProjectAcctDisbursement(Request $request)
    {
        Session::put('disbursementprojecttype', $request->project_type_id);

        return redirect('/bank-disbursement/add');
    }

	public function set_log($user_id, $username, $id, $class, $pk, $remark){

		date_default_timezone_set("Asia/Jakarta");

		$log = array(
			'user_id'		=>	$user_id,
			'username'		=>	$username,
			'id_previllage'	=> 	$id,
			'class_name'	=>	$class,
			'pk'			=>	$pk,
			'remark'		=> 	$remark,
			'log_stat'		=>	'1',
			'log_time'		=>	date("Y-m-d G:i:s")
		);
		return SystemLogUser::create($log);
	}
    
    public function addArrayAcctBankDisbursementItem(Request $request)
    {
        $dataacctdisbursementitem = array(
            'record_id'				=> date('YmdHis'),
            'account_id_item'		=> $request->account_id_item,
            'bank_disbursement_item_amount'	=> $request->bank_disbursement_item_amount,
            'bank_disbursement_item_title'	=> $request->bank_disbursement_item_title,
        );

        $lastdataacctdisbursementitem = Session::get('dataacctdisbursementitem');
        if($lastdataacctdisbursementitem !== null){
            array_push($lastdataacctdisbursementitem, $dataacctdisbursementitem);
            Session::put('dataacctdisbursementitem', $lastdataacctdisbursementitem);
        }else{
            $lastdataacctdisbursementitem = [];
            array_push($lastdataacctdisbursementitem, $dataacctdisbursementitem);
            Session::push('dataacctdisbursementitem', $dataacctdisbursementitem);
        }
        
        return redirect('/bank-disbursement/add');
    }

    public function deleteArrayAcctBankDisbursementItem($record_id)
    {
        $arrayBaru			= array();
        $dataArrayHeader	= Session::get('dataacctdisbursementitem');
        
        foreach($dataArrayHeader as $key=>$val){
            if($key != $record_id){
                $arrayBaru[$key] = $val;
            }
        }
        Session::forget('dataacctdisbursementitem');
        Session::put('dataacctdisbursementitem', $arrayBaru);

        return redirect('/bank-disbursement/add');
    }
    
    public function getAccountName($account_id)
    {
        $account = AcctAccount::select('account_name')
        ->where('account_id', '=', $account_id)
        ->first();

        return $account['account_name'];
    }

    public function getCustomerName($customer_id)
    {
        $customer = CoreCustomer::select('customer_name')
        ->where('customer_id', '=', $customer_id)
        ->first();

        if($customer == null){
            return '-';
        }

        return $customer['customer_name'];
    }
    
    public function processAddAcctBankDisbursement(Request $request)
    {
        $session_AcctDisbursementitem		= Session::get('dataacctdisbursementitem');
        $branch_id = User::select('branch_id')->where('user_id','=',Auth::id())->first();
        
        $data_AcctDisbursement = array(
            'branch_id'						=> $branch_id['branch_id'],
            'bank_disbursement_date'				=> $request->bank_disbursement_date,
            'bank_disbursement_title'			=> $request->bank_disbursement_title,
            'account_id'					=> $request->account_id,
            'customer_id'					=> $request->customer_id,
            'bank_disbursement_description'		=> $request->bank_disbursement_description,
            'bank_disbursement_amount_total' 	=> $request->bank_disbursement_amount_total,
            'bank_disbursement_token' 			=> md5(rand()),
            'data_state' 					=> 0,
            'created_id' 					=> Auth::id(),
            'created_on' 					=> date('Y-m-d H:i:s'),
        );

        $transaction_module_code 	= "DS";

        $transaction_module_id 		= PreferenceTransactionModule::select('transaction_module_id')
        ->where('preference_transaction_module.transaction_module_code', '=', $transaction_module_code)
        ->first();

        $disbursement_token 				= AcctBankDisbursement::select('bank_disbursement_token')
        ->where('bank_disbursement_token', '=', $data_AcctDisbursement['bank_disbursement_token'])
        ->get();
        
        if (!empty($session_AcctDisbursementitem)){
            if(count($disbursement_token) == 0){
                if(AcctBankDisbursement::create($data_AcctDisbursement)){
                    $acctdisbursement_last 		= AcctBankDisbursement::select('bank_disbursement_id', 'bank_disbursement_no')
                    ->where('created_id', '=', $data_AcctDisbursement['created_id'])
                    ->orderBy('bank_disbursement_id', 'DESC')
                    ->first();
                    
                    $journal_voucher_period 	= date("Ym", strtotime($data_AcctDisbursement['bank_disbursement_date']));

                    $data_journal = array(
                        'branch_id'						=> $branch_id['branch_id'],
                        'journal_voucher_period' 		=> $journal_voucher_period,
                        'journal_voucher_date'			=> $data_AcctDisbursement['bank_disbursement_date'],
                        'journal_voucher_title'			=> $data_AcctDisbursement['bank_disbursement_title'],
                        'journal_voucher_no'			=> $acctdisbursement_last['bank_disbursement_no'],
                        'journal_voucher_description'	=> $acctdisbursement_last['bank_disbursement_no'],
                        'transaction_module_id'			=> $transaction_module_id['transaction_module_id'],
                        'transaction_module_code'		=> $transaction_module_code,
                        'transaction_journal_id' 		=> $acctdisbursement_last['bank_disbursement_id'],
                        'transaction_journal_no' 		=> $acctdisbursement_last['bank_disbursement_no'],
                        'journal_voucher_token'			=> $data_AcctDisbursement['bank_disbursement_token'],
                        'journal_voucher_type_id'       => 4 ,
                        'created_id' 					=> $data_AcctDisbursement['created_id'],
                        'created_on' 					=> $data_AcctDisbursement['created_on']
                    );
                    
                    AcctJournalVoucher::create($data_journal);		

                    $journal_voucher_id 	= AcctJournalVoucher::select('journal_voucher_id')
                    ->where('created_id', '=', $data_AcctDisbursement['created_id'])
                    ->orderBy('journal_voucher_id', 'DESC')
                    ->first();

                    $disbursement_id 		= AcctBankDisbursement::select('bank_disbursement_id')
                    ->where('created_id', $data_AcctDisbursement['created_id'])
                    ->orderBy('bank_disbursement_id', 'DESC')
                    ->first();
                    
                    $no = 0;
                    
                    foreach($session_AcctDisbursementitem as $key=>$val){
                        $data_AcctDisbursementitem = array(
                            'bank_disbursement_id'			=> $disbursement_id['bank_disbursement_id'],
                            'account_id'				=> $val['account_id_item'],
                            'bank_disbursement_item_title'	=> $val['bank_disbursement_item_title'],
                            'bank_disbursement_item_amount'	=> $val['bank_disbursement_item_amount'],
                            'bank_disbursement_item_token' 	=> $data_AcctDisbursement['bank_disbursement_token'].$val['account_id_item'].$no
                        );

                        $no++;

                        if(AcctBankDisbursementItem::create($data_AcctDisbursementitem)){

                            $account_id_default_status 	= AcctAccount::select('account_default_status')
                            ->where('account_id', '=', $data_AcctDisbursementitem['account_id'])
                            ->where('data_state', '=' ,0)
                            ->first();

                            $data_debit = array (
                                'journal_voucher_id'			=> $journal_voucher_id['journal_voucher_id'],
                                'account_id'					=> $data_AcctDisbursementitem['account_id'],
                                'journal_voucher_description'	=> $data_AcctDisbursementitem['bank_disbursement_item_title'],
                                'journal_voucher_amount'		=> ABS($data_AcctDisbursementitem['bank_disbursement_item_amount']),
                                'journal_voucher_debit_amount'	=> ABS($data_AcctDisbursementitem['bank_disbursement_item_amount']),
                                'account_id_default_status'		=> $account_id_default_status['account_default_status'],
                                'account_id_status'				=> 1,
                                'journal_voucher_item_token'	=> $data_AcctDisbursement['bank_disbursement_token'].$data_AcctDisbursementitem['account_id'].$no
                            );

                            AcctJournalVoucherItem::create($data_debit);

                            $username = User::select('name')->where('user_id','=',Auth::id())->first();

                            $this->set_log(Auth::id(), $username['name'],'1089','Application.cashAcctDisbursement.cashAcctDisbursementinsertprocess', $username['name'],'Add Cash Disbursement');

                            continue;
                        } else {
                            $msg = "Tambah Pengeluaran Kas Gagal";
                            Session::forget('dataacctdisbursementitem');
                            return redirect('/bank-disbursement/add')->with('msg',$msg);
                            break;
                        }
                    }

                    $account_id_default_status 	= AcctAccount::select('account_default_status')
                    ->where('account_id', '=', $data_AcctDisbursement['account_id'])
                    ->where('data_state', '=', 0)
                    ->first();

                    $data_credit = array (
                        'journal_voucher_id'			=> $journal_voucher_id['journal_voucher_id'],
                        'account_id'					=> $data_AcctDisbursement['account_id'],
                        'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                        'journal_voucher_amount'		=> ABS($data_AcctDisbursement['bank_disbursement_amount_total']),
                        'journal_voucher_credit_amount'	=> ABS($data_AcctDisbursement['bank_disbursement_amount_total']),
                        'account_id_default_status'		=> $account_id_default_status['account_default_status'],
                        'account_id_status'				=> 0,
                        'journal_voucher_item_token'	=> $data_AcctDisbursement['bank_disbursement_token'].$data_AcctDisbursementitem['account_id']."0"
                    );

                    AcctJournalVoucherItem::create($data_credit);

                    $msg = "Tambah Pengeluaran Kas Berhasil";
                    Session::forget('dataacctdisbursementitem');
                    return redirect('/bank-disbursement')->with('msg',$msg);
                }else{
                    $msg = "Tambah Pengeluaran Kas Gagal";
                    Session::forget('dataacctdisbursementitem');
                    return redirect('/bank-disbursement/add')->with('msg',$msg);
                }
            } else {
                $acctdisbursement_last 		= AcctBankDisbursement::select('bank_disbursement_id', 'bank_disbursement_no')
                ->where('created_id', '=', $data_AcctDisbursement['created_id'])
                ->orderBy('bank_disbursement_id', 'DESC')
                ->first();
                    
                $journal_voucher_period 	= date("Ym", strtotime($data_AcctDisbursement['disbursement_date']));

                $data_journal = array(
                    'branch_id'						=> $branch_id['branch_id'],
                    'journal_voucher_period' 		=> $journal_voucher_period,
                    'journal_voucher_date'			=> $data_AcctDisbursement['bank_disbursement_date'],
                    'journal_voucher_title'			=> $data_AcctDisbursement['bank_disbursement_title'],
                    'journal_voucher_no'			=> $acctdisbursement_last['bank_disbursement_no'],
                    'journal_voucher_description'	=> $acctdisbursement_last['bank_disbursement_no'],
                    'transaction_module_id'			=> $transaction_module_id['transaction_module_id'],
                    'transaction_module_code'		=> $transaction_module_code,
                    'transaction_journal_id' 		=> $acctdisbursement_last['bank_disbursement_id'],
                    'transaction_journal_no' 		=> $acctdisbursement_last['bank_disbursement_no'],
                    'journal_voucher_token'			=> $data_AcctDisbursement['bank_disbursement_token'],
                    'created_id' 					=> $data_AcctDisbursement['created_id'],
                    'created_on' 					=> $data_AcctDisbursement['created_on']
                );
                
                $journal_voucher_token 	= AcctJournalVoucher::select('journal_voucher_token')
                ->where('journal_voucher_token', '=', $data_journal['journal_voucher_token'])
                ->get();

                if(count($journal_voucher_token) == 0){
                    AcctJournalVoucher::create($data_journal);	
                }	

                $journal_voucher_id 	= AcctJournalVoucher::select('journal_voucher_id')
                ->where('created_id', '=', $data_AcctDisbursement['created_id'])
                ->orderBy('journal_voucher_id', 'DESC')
                ->first();

                $disbursement_id 		= AcctBankDisbursement::select('disbursement_id')
                ->where('created_id', $data_AcctDisbursement['created_id'])
                ->orderBy('disbursement_id', 'DESC')
                ->first();
                
                $no=0;
                
                foreach($session_AcctDisbursementitem as $key=>$val){
                    $data_AcctDisbursementitem = array(
                        'bank_disbursement_id'			=> $disbursement_id['bank_disbursement_id'],
                        'account_id'				=> $val['account_id_item'],
                        'bank_disbursement_item_title'	=> $val['bank_disbursement_item_title'],
                        'bank_disbursement_item_amount'	=> $val['bank_disbursement_item_amount'],
                        'bank_disbursement_item_token' 	=> $data_AcctDisbursement['bank_disbursement_token'].$val['account_id_item'].$no
                    );

                    $no++;

                    $disbursement_item_token 	= AcctBankDisbursementItem::select('bank_disbursement_item_token')
                    ->where('bank_disbursement_item_token', '=', $data_AcctDisbursementitem['bank_disbursement_item_token'])
                    ->get();


                    if(count($disbursement_item_token) == 0){
                        if(AcctBankDisbursementItem::create($data_AcctDisbursementitem)){

                            $account_id_default_status 	= AcctAccount::select('account_default_status')
                            ->where('account_id', '=', $data_AcctDisbursementitem['account_id'])
                            ->where('data_state', '=', 0)
                            ->first();

                            $data_debit = array (
                                'journal_voucher_id'			=> $journal_voucher_id['journal_voucher_id'],
                                'account_id'					=> $data_AcctDisbursementitem['account_id'],
                                'journal_voucher_description'	=> $data_AcctDisbursementitem['disbursement_item_title'],
                                'journal_voucher_amount'		=> ABS($data_AcctDisbursementitem['disbursement_item_amount']),
                                'journal_voucher_debit_amount'	=> ABS($data_AcctDisbursementitem['disbursement_item_amount']),
                                'account_id_default_status'		=> $account_id_default_status['account_default_status'],
                                'account_id_status'				=> 1,
                                'journal_voucher_item_token'	=> $data_AcctDisbursement['disbursement_token'].$data_AcctDisbursementitem['account_id'].$no
                            );

                            $journal_voucher_item_token 	= AcctJournalVoucherItem::select('journal_voucher_item_token')
                            ->where('journal_voucher_item_token', '=', $data_debit['journal_voucher_item_token'])
                            ->get();

                            if(count($journal_voucher_item_token) == 0){
                                AcctJournalVoucherItem::create($data_debit);
                            }
                            
                            $username = User::select('name')->where('user_id','=',Auth::id())->first();

                            $this->set_log(Auth::id(),$username['name'],'1089','Application.cashAcctDisbursement.cashAcctDisbursementinsertprocess',$username['name'],'Add Cash Disbursement');

                            $msg = "Tambah Pengeluaran Kas Berhasil";
                            continue;
                        } else {
                            $msg = "Tambah Pengeluaran Kas Gagal";
                            Session::forget('dataacctdisbursementitem');
                            return redirect('/bank-disbursement/add')->with('msg',$msg);
                            break;
                        }
                    } else {
                        $account_id_default_status 	= AcctAccount::select('account_default_status')
                        ->where('account_id', '=', $data_AcctDisbursementitem['account_id'])
                        ->where('data_state', '=', 0)
                        ->first();

                        $data_debit = array (
                            'journal_voucher_id'			=> $journal_voucher_id['journal_voucher_id'],
                            'account_id'					=> $data_AcctDisbursementitem['account_id'],
                            'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                            'journal_voucher_amount'		=> ABS($data_AcctDisbursementitem['disbursement_item_amount']),
                            'journal_voucher_debit_amount'	=> ABS($data_AcctDisbursementitem['disbursement_item_amount']),
                            'account_id_default_status'		=> $account_id_default_status,
                            'account_id_status'				=> 1,
                            'journal_voucher_item_token'	=> $data_AcctDisbursement['disbursement_token'].$data_AcctDisbursementitem['account_id'].$no
                        );

                        $journal_voucher_item_token 	= AcctJournalVoucherItem::select('journal_voucher_item_token')
                        ->where('journal_voucher_item_token', '=', $data_debit['journal_voucher_item_token'])
                        ->get();

                        if(count($journal_voucher_item_token) == 0){
                            AcctJournalVoucherItem::create($data_debit);
                        }

                        $username = User::select('name')->where('user_id','=',Auth::id())->first();

                        $this->set_log(Auth::id(), $username['name'],'1089','Application.cashAcctDisbursement.cashAcctDisbursementinsertprocess', $username['name'],'Add Cash Disbursement');

                        $msg = "Tambah Pengeluaran Kas Berhasil";
                        
                    }
                    
                }

                $account_id_default_status 	= AcctAccount::select('account_default_status')
                ->where('account_id', '=', $data_AcctDisbursement['account_id'])
                ->where('data_state', '=', 0)
                ->first();
                
                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id['journal_voucher_id'],
                    'account_id'					=> $data_AcctDisbursement['account_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    'journal_voucher_amount'		=> ABS($data_AcctDisbursement['bank_disbursement_amount_total']),
                    'journal_voucher_credit_amount'	=> ABS($data_AcctDisbursement['bank_disbursement_amount_total']),
                    'account_id_default_status'		=> $account_id_default_status['account_default_status'],
                    'account_id_status'				=> 0,
                    'journal_voucher_item_token'	=> $data_AcctDisbursement['bank_disbursement_token'].$data_AcctDisbursementitem['account_id']."0"
                );

                $journal_voucher_item_token 	= AcctJournalVoucherItem::select('journal_voucher_item_token')
                ->where('journal_voucher_item_token', '=', $data_credit['journal_voucher_item_token'])
                ->get();

                if(count($journal_voucher_item_token) == 0){
                    AcctJournalVoucherItem::create($data_credit);
                }
                
                Session::forget('dataacctdisbursementitem');
                return redirect('/bank-disbursement')->with('msg',$msg);
            }
            
        } else {
            $msg = "Data Detail Disbursement Empty";
            Session::forget('dataacctdisbursementitem');
            return redirect('/bank-disbursement/add')->with('msg',$msg);
        }

    }
		
    public function processVoidAcctBankDisbursement(Request $request){
        $bank_disbursement_no	= $request->bank_disbursement_no;
        
        $data = array (
            "bank_disbursement_id"			=> $request->bank_disbursement_id,
            "bank_disbursement_token_void"	=> md5(rand()),
            "voided_id"					=> Auth::id(),
            "voided_on"					=> date('Y-m-d H:i:s'),
            "voided_remark" 			=> $request->voided_remark,
            'data_state'				=> 2,
        );
        $data_edit                              = AcctBankDisbursement::findOrFail($data['bank_disbursement_id']);
        $data_edit->bank_disbursement_id             = $data['bank_disbursement_id'];
        $data_edit->bank_disbursement_token_void     = $data['bank_disbursement_token_void'];
        $data_edit->voided_id                   = $data['voided_id'];
        $data_edit->voided_on                   = $data['voided_on'];
        $data_edit->voided_remark               = $data['voided_remark'];
        $data_edit->data_state                  = $data['data_state'];

        $bank_disbursement_token_void = AcctBankDisbursement::select('bank_disbursement_token_void')
        ->where('bank_disbursement_token_void', $data['bank_disbursement_token_void'])
        ->get();

        if(count($bank_disbursement_token_void) == 0){
            if($data_edit->save()){
                $journal_voucher_id 	= AcctJournalVoucher::select('journal_voucher_id')
                ->where('transaction_journal_no', $bank_disbursement_no)
                ->first();


                $acctjournalvoucheritem = AcctJournalVoucherItem::select('acct_journal_voucher_item.journal_voucher_item_id', 'acct_journal_voucher_item.journal_voucher_id', 'acct_journal_voucher_item.account_id', 'acct_journal_voucher_item.journal_voucher_amount', 'acct_journal_voucher_item.account_id_status')
                ->where('journal_voucher_id', $journal_voucher_id['journal_voucher_id'])
                ->get();

                $data_journal = array (
                    "journal_voucher_id"			=> $journal_voucher_id['journal_voucher_id'],
                    "journal_voucher_token_void"	=> $data['bank_disbursement_token_void'],
                    "voided"						=> 1,
                    "voided_id"						=> Auth::id(),
                    "voided_on"						=> date('Y-m-d H:i:s'),
                    "voided_remark" 				=> $data['voided_remark'],
                    'data_state'					=> 2,
                );

                $data_journal_edit                              = AcctJournalVoucher::findOrFail($data_journal['journal_voucher_id']);
                $data_journal_edit->journal_voucher_id          = $data_journal['journal_voucher_id'];
                $data_journal_edit->journal_voucher_token_void  = $data_journal['journal_voucher_token_void'];
                $data_journal_edit->voided                      = $data_journal['voided'];
                $data_journal_edit->voided_id                   = $data_journal['voided_id'];
                $data_journal_edit->voided_on                   = $data_journal['voided_on'];
                $data_journal_edit->voided_remark               = $data_journal['voided_remark'];
                $data_journal_edit->data_state                  = $data_journal['data_state'];

                if ($data_journal_edit->save()){
                    foreach ($acctjournalvoucheritem as $keyItem => $valItem) {
                        $data_journal_item = array (
                            'journal_voucher_item_id'			=> $valItem['journal_voucher_item_id'],
                            'journal_voucher_id'				=> $valItem['journal_voucher_id'],
                            'account_id'						=> $valItem['account_id'],
                            'journal_voucher_amount'			=> $valItem['journal_voucher_amount'],
                            "journal_voucher_item_token_void"	=> $data['bank_disbursement_token_void'].$valItem['journal_voucher_item_id'],
                            'account_id_status'					=> $valItem['account_id_status'],
                            'data_state'						=> 2
                        );

                        $data_journal_item_edit                                      = AcctJournalVoucherItem::findOrFail($data_journal_item['journal_voucher_item_id']);
                        $data_journal_item_edit->journal_voucher_item_id             = $data_journal_item['journal_voucher_item_id'];
                        $data_journal_item_edit->journal_voucher_id                  = $data_journal_item['journal_voucher_id'];
                        $data_journal_item_edit->account_id                          = $data_journal_item['account_id'];
                        $data_journal_item_edit->journal_voucher_amount              = $data_journal_item['journal_voucher_amount'];
                        $data_journal_item_edit->journal_voucher_item_token_void     = $data_journal_item['journal_voucher_item_token_void'];
                        $data_journal_item_edit->account_id_status                   = $data_journal_item['account_id_status'];
                        $data_journal_item_edit->data_state                          = $data_journal_item['data_state'];

                        $data_journal_item_edit->save();
                    }
                }
                
                $msg = "Pembatalan Pengeluaran Kas dan Bank Sukses";
                return redirect('/bank-disbursement')->with('msg',$msg);
            }else{
                $msg = "Pembatalan Pengeluaran Kas dan Bank Gagal";
                return redirect('/bank-disbursement')->with('msg',$msg);
            }
        } else {
            $journal_voucher_id 	= AcctJournalVoucher::select('journal_voucher_id')
            ->where('transaction_journal_no', $bank_disbursement_no)
            ->first();


            $acctjournalvoucheritem = AcctJournalVoucherItem::select('acct_journal_voucher_item.journal_voucher_item_id', 'acct_journal_voucher_item.journal_voucher_id', 'acct_journal_voucher_item.account_id', 'acct_journal_voucher_item.journal_voucher_amount', 'acct_journal_voucher_item.account_id_status')
            ->where('journal_voucher_id', $journal_voucher_id['journal_voucher_id'])
            ->get();

            $data_journal = array (
                "journal_voucher_id"			=> $journal_voucher_id['journal_voucher_id'],
                "journal_voucher_token_void"	=> $data['bank_disbursement_token_void'],
                "voided"						=> 1,
                "voided_id"						=> Auth::id(),
                "voided_on"						=> date('Y-m-d H:i:s'),
                "voided_remark" 				=> $data['voided_remark'],
                'data_state'					=> 2,
            );

            $data_journal_edit                              = AcctJournalVoucher::findOrFail($data_journal['journal_voucher_id']);
            $data_journal_edit->journal_voucher_id          = $data_journal['journal_voucher_id'];
            $data_journal_edit->journal_voucher_token_void  = $data_journal['journal_voucher_token_void'];
            $data_journal_edit->voided                      = $data_journal['voided'];
            $data_journal_edit->voided_id                   = $data_journal['voided_id'];
            $data_journal_edit->voided_on                   = $data_journal['voided_on'];
            $data_journal_edit->voided_remark               = $data_journal['voided_remark'];
            $data_journal_edit->data_state                  = $data_journal['data_state'];

            $journal_voucher_token_void = AcctJournalVoucher::select('journal_voucher_token_void')
            ->where('journal_voucher_token_void', $data_journal['journal_voucher_token_void'])
            ->get();

            if(count($journal_voucher_token_void) == 0){
                if ($data_journal_edit->save()){
                    foreach ($acctjournalvoucheritem as $keyItem => $valItem) {
                        $data_journal_item = array (
                            'journal_voucher_item_id'			=> $valItem['journal_voucher_item_id'],
                            'journal_voucher_id'				=> $valItem['journal_voucher_id'],
                            'account_id'						=> $valItem['account_id'],
                            'journal_voucher_amount'			=> $valItem['journal_voucher_amount'],
                            "journal_voucher_item_token_void"	=> $data['bank_disbursement_token_void'].$valItem['journal_voucher_item_id'],
                            'account_id_status'					=> $valItem['account_id_status'],
                            'data_state'						=> 2
                        );

                        $data_journal_item_edit                                      = AcctJournalVoucherItem::findOrFail($data_journal_item['journal_voucher_item_id']);
                        $data_journal_item_edit->journal_voucher_item_id             = $data_journal_item['journal_voucher_item_id'];
                        $data_journal_item_edit->journal_voucher_id                  = $data_journal_item['journal_voucher_id'];
                        $data_journal_item_edit->account_id                          = $data_journal_item['account_id'];
                        $data_journal_item_edit->journal_voucher_amount              = $data_journal_item['journal_voucher_amount'];
                        $data_journal_item_edit->journal_voucher_item_token_void     = $data_journal_item['journal_voucher_item_token_void'];
                        $data_journal_item_edit->account_id_status                   = $data_journal_item['account_id_status'];
                        $data_journal_item_edit->data_state                          = $data_journal_item['data_state'];


                        $journal_voucher_item_token_void = AcctJournalVoucherItem::select('journal_voucher_item_token_void')
                        ->where('journal_voucher_item_token_void', $data_journal_item['journal_voucher_item_token_void'])
                        ->get();

                        if(count($journal_voucher_item_token_void) == 0){
                            $data_journal_item_edit->save();
                        }
                        
                    }
                }
            } else {
                foreach ($acctjournalvoucheritem as $keyItem => $valItem) {
                    $data_journal_item = array (
                        'journal_voucher_item_id'			=> $valItem['journal_voucher_item_id'],
                        'journal_voucher_id'				=> $valItem['journal_voucher_id'],
                        'account_id'						=> $valItem['account_id'],
                        'journal_voucher_amount'			=> $valItem['journal_voucher_amount'],
                        "journal_voucher_item_token_void"	=> $data['bank_disbursement_token_void'].$valItem['journal_voucher_item_id'],
                        'account_id_status'					=> $valItem['account_id_status'],
                        'data_state'						=> 2
                    );

                    $data_journal_item_edit                                      = AcctJournalVoucherItem::findOrFail($data_journal_item['journal_voucher_item_id']);
                    $data_journal_item_edit->journal_voucher_item_id             = $data_journal_item['journal_voucher_item_id'];
                    $data_journal_item_edit->journal_voucher_id                  = $data_journal_item['journal_voucher_id'];
                    $data_journal_item_edit->account_id                          = $data_journal_item['account_id'];
                    $data_journal_item_edit->journal_voucher_amount              = $data_journal_item['journal_voucher_amount'];
                    $data_journal_item_edit->journal_voucher_item_token_void     = $data_journal_item['journal_voucher_item_token_void'];
                    $data_journal_item_edit->account_id_status                   = $data_journal_item['account_id_status'];
                    $data_journal_item_edit->data_state                          = $data_journal_item['data_state'];

                    $journal_voucher_item_token_void = AcctJournalVoucherItem::select('journal_voucher_item_token_void')
                    ->where('journal_voucher_item_token_void', $data_journal_item['journal_voucher_item_token_void'])
                    ->get();

                    if(count($journal_voucher_item_token_void) == 0){
                        $data_journal_item_edit->save();
                    }
                    
                }
                $msg = "Pembatalan Pengeluaran Kas dan Bank Sukses";
                return redirect('/bank-disbursement')->with('msg',$msg);
            }
        }
    }
}
