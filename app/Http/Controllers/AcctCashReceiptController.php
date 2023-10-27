<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\AcctAccount;
use App\Models\AcctCashReceipt;
use App\Models\AcctCashReceiptItem;
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
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcctCashReceiptController extends Controller
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
        Session::forget('acctreceiptelements');
        Session::forget('dataacctreceiptitem');
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
        $acctreceipt    = AcctCashReceipt::select('acct_cash_receipt.*')
        ->where('acct_cash_receipt.data_state','=',0)
        ->where('acct_cash_receipt.cash_receipt_date','>=',$start_date)
        ->where('acct_cash_receipt.cash_receipt_date','<=',$end_date)
        ->orderBy('acct_cash_receipt.cash_receipt_id', 'DESC')
        ->get();

        return view('content/AcctCashReceipt/ListAcctCashReceipt', compact('acctreceipt','start_date','end_date'));
    }

    // public function selectProjectAcctCashReceipt(Request $request)
    // {
    //     Session::put('receiptprojecttype', $request->project_type_id);

    //     return redirect('/cash-receipt/add');
    // }

    public function addAcctCashReceipt()
    {
        $acctreceiptelements= Session::get('acctreceiptelements');
        $acctreceiptitem = Session::get('dataacctreceiptitem');
        
        $branch_id = User::select('branch_id')->where('user_id','=',Auth::id())->first();

        $coreprojectstatus = array (
            9   => 'Reguler',
            0   => 'Baru', 
            1   => 'Selesai', 
            2   => 'Pending',
        );

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


        return view('content/AcctCashReceipt/FormAddAcctCashReceipt', compact('salescustomer', 'acctaccount', 'acctaccountcashbank', 'acctreceiptitem', 'acctreceiptelements', 'corecustomer'));
    }

    public function elements_add(Request $request){
        $acctreceiptelements= Session::get('acctreceiptelements');
        if(!$acctreceiptelements || $acctreceiptelements == ''){
            $acctreceiptelements['customer_id']   = '';
            $acctreceiptelements['cash_receipt_date']     = '';
            $acctreceiptelements['account_id']           = '';   
            $acctreceiptelements['customer_id']           = '';   
            $acctreceiptelements['cash_receipt_title']  = '';
            $acctreceiptelements['cash_receipt_description']   = '';
        }
        $acctreceiptelements[$request->name] = $request->value;
        Session::put('acctreceiptelements', $acctreceiptelements);
    }

    public function detailAcctCashReceipt($cash_receipt_id)
    {
        $acctreceiptdetail = AcctCashReceipt::select('acct_cash_receipt.*', 'acct_account.account_code', 'acct_account.account_name')
        ->where('cash_receipt_id', '=', $cash_receipt_id)
        ->join('acct_account', 'acct_cash_receipt.account_id', '=', 'acct_account.account_id')
        ->first();

        $acctreceiptitem = AcctCashReceiptItem::select('acct_cash_receipt_item.cash_receipt_item_id', 'acct_cash_receipt_item.cash_receipt_item_title', 'acct_cash_receipt_item.cash_receipt_item_amount','acct_account.account_code', 'acct_account.account_name')
        ->join('acct_account', 'acct_cash_receipt_item.account_id', '=', 'acct_account.account_id')
        ->where('acct_cash_receipt_item.cash_receipt_id', '=', $cash_receipt_id)
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

        return view('content/AcctCashReceipt/FormDetailAcctCashReceipt', compact('salescustomer', 'acctaccount', 'acctaccountcashbank', 'acctreceiptitem', 'acctreceiptdetail'));
    }
    
    public function voidAcctCashReceipt($cash_receipt_id)
    {
        $acctreceiptdetail = AcctCashReceipt::select('acct_cash_receipt.*', 'acct_account.account_code', 'acct_account.account_name')
        ->where('cash_receipt_id', '=', $cash_receipt_id)
        ->join('acct_account', 'acct_cash_receipt.account_id', '=', 'acct_account.account_id')
        ->first();

        $acctreceiptitem = AcctCashReceiptItem::select('acct_cash_receipt_item.cash_receipt_item_id', 'acct_cash_receipt_item.cash_receipt_item_title', 'acct_cash_receipt_item.cash_receipt_item_amount','acct_account.account_code', 'acct_account.account_name')
        ->join('acct_account', 'acct_cash_receipt_item.account_id', '=', 'acct_account.account_id')
        ->where('acct_cash_receipt_item.cash_receipt_id', '=', $cash_receipt_id)
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

        return view('content/AcctCashReceipt/FormVoidAcctCashReceipt', compact('salescustomer', 'acctaccount', 'acctaccountcashbank',  'acctreceiptitem', 'acctreceiptdetail', 'cash_receipt_id'));
    }
    
    public function processVoidAcctCashReceipt(Request $request)
    {
        $receipt_no	= $request->cash_receipt_no;

        // dd($receipt_no);
        
        $data = array (
            "cash_receipt_id"				=> $request->cash_receipt_id,
            "cash_receipt_token_void"		=> md5(rand()),
            "voided_id"					=> Auth::id(),
            "voided_on"					=> date('Y-m-d H:i:s'),
            "voided_remark" 			=> $request->voided_remark,
            'data_state'				=> 2,
        );

        
        $data_edit                      = AcctCashReceipt::findOrFail($data['cash_receipt_id']);
        // dd($data_edit);
        $data_edit->cash_receipt_id          = $data['cash_receipt_id'];
        $data_edit->cash_receipt_token_void  = $data['cash_receipt_token_void'];
        $data_edit->voided_id           = $data['voided_id'];
        $data_edit->voided_on           = $data['voided_on'];
        $data_edit->voided_remark       = $data['voided_remark'];
        $data_edit->data_state          = $data['data_state'];
        
        $receipt_token_void = AcctCashReceipt::select('cash_receipt_token_void')
        ->where('cash_receipt_token_void', $data['cash_receipt_token_void'])
        ->get();

        if(count($receipt_token_void) == 0){
            if($data_edit->save()){
                $journal_voucher_id 	= AcctJournalVoucher::select('journal_voucher_id')
                ->where('transaction_journal_no', $receipt_no)
                ->first();

                
                
                $acctjournalvoucheritem = AcctJournalVoucherItem::select('acct_journal_voucher_item.journal_voucher_item_id', 'acct_journal_voucher_item.journal_voucher_id', 'acct_journal_voucher_item.account_id', 'acct_journal_voucher_item.journal_voucher_amount', 'acct_journal_voucher_item.account_id_status')
                ->where('journal_voucher_id', $journal_voucher_id['journal_voucher_id'])
                ->get();
                
                $data_journal = array (
                    "journal_voucher_id"			=> $journal_voucher_id['journal_voucher_id'],
                    "journal_voucher_token_void"	=> $data['cash_receipt_token_void'],
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
                $data_journal_edit->journal_voucher_type_id     = 4 ;
                $data_journal_edit->data_state                  = $data_journal['data_state'];
                
                if ($data_journal_edit->save()){
                    foreach ($acctjournalvoucheritem as $keyItem => $valItem) {
                        $data_journal_item = array (
                            'journal_voucher_item_id'			=> $valItem['journal_voucher_item_id'],
                            'journal_voucher_id'				=> $valItem['journal_voucher_id'],
                            'account_id'						=> $valItem['account_id'],
                            'journal_voucher_amount'			=> $valItem['journal_voucher_amount'],
                            "journal_voucher_item_token_void"	=> $data['cash_receipt_token_void'].$valItem['journal_voucher_item_id'],
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
                        
                        // dd($data_journal_item_edit);return 
                        $data_journal_item_edit->save();
                    }
                }
                
                $msg = "Pembatalan Penerimaan Kas Sukses";
                return redirect('/cash-receipt')->with('msg',$msg);
            }else{
                $msg = "Pembatalan Penerimaan Kas Gagal";
                return redirect('/cash-receipt')->with('msg',$msg);
            }
        } else {
            $journal_voucher_id 	= AcctJournalVoucher::select('journal_voucher_id')
            ->where('transaction_journal_no', $receipt_no)
            ->first();

            $acctjournalvoucheritem = AcctJournalVoucherItem::select('acct_journal_voucher_item.journal_voucher_item_id', 'acct_journal_voucher_item.journal_voucher_id', 'acct_journal_voucher_item.account_id', 'acct_journal_voucher_item.journal_voucher_amount', 'acct_journal_voucher_item.account_id_status')
            ->where('journal_voucher_id', $journal_voucher_id['journal_voucher_id'])
            ->get();

            $data_journal = array (
                "journal_voucher_id"			=> $journal_voucher_id['journal_voucher_id'],
                "journal_voucher_token_void"	=> $data['receipt_token_void'],
                "voided"						=> 1,
                "voided_id"						=> Auth::id(),
                "voided_on"						=> date('Y-m-d H:i:s'),
                "voided_remark" 				=> $data['voided_remark'],
                'data_state'					=> 2,
            );

            $journal_voucher_token_void = AcctJournalVoucher::select('journal_voucher_token_void')
            ->where('journal_voucher_token_void', $data_journal['journal_voucher_token_void'])
            ->get();
            
            $data_journal_edit                              = AcctJournalVoucher::findOrFail($data_journal['journal_voucher_id']);
            $data_journal_edit->journal_voucher_id          = $data_journal['journal_voucher_id'];
            $data_journal_edit->journal_voucher_token_void  = $data_journal['journal_voucher_token_void'];
            $data_journal_edit->voided                      = $data_journal['voided'];
            $data_journal_edit->voided_id                   = $data_journal['voided_id'];
            $data_journal_edit->voided_on                   = $data_journal['voided_on'];
            $data_journal_edit->voided_remark               = $data_journal['voided_remark'];
            $data_journal_edit->data_state                  = $data_journal['data_state'];

            if(count($journal_voucher_token_void) == 0){
                if ($data_journal_edit->save()){
                    foreach ($acctjournalvoucheritem as $keyItem => $valItem) {
                        $data_journal_item = array (
                            'journal_voucher_item_id'			=> $valItem['journal_voucher_item_id'],
                            'journal_voucher_id'				=> $valItem['journal_voucher_id'],
                            'account_id'						=> $valItem['account_id'],
                            'journal_voucher_amount'			=> $valItem['journal_voucher_amount'],
                            "journal_voucher_item_token_void"	=> $data['receipt_token_void'].$valItem['journal_voucher_item_id'],
                            'account_id_status'					=> $valItem['account_id_status'],
                            'data_state'						=> 2
                        );

                        $journal_voucher_item_token_void = AcctJournalVoucherItem::select('journal_voucher_item_token_void')
                        ->where('journal_voucher_item_token_void', $data_journal_item['journal_voucher_item_token_void'])
                        ->get();

                        if(count($journal_voucher_item_token_void) == 0){
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
                }
            } else {
                foreach ($acctjournalvoucheritem as $keyItem => $valItem) {
                    $data_journal_item = array (
                        'journal_voucher_item_id'			=> $valItem['journal_voucher_item_id'],
                        'journal_voucher_id'				=> $valItem['journal_voucher_id'],
                        'account_id'						=> $valItem['account_id'],
                        'journal_voucher_amount'			=> $valItem['journal_voucher_amount'],
                        "journal_voucher_item_token_void"	=> $data['receipt_token_void'].$valItem['journal_voucher_item_id'],
                        'account_id_status'					=> $valItem['account_id_status'],
                        'data_state'						=> 2
                    );

                    $journal_voucher_item_token_void = AcctJournalVoucherItem::select('journal_voucher_item_token_void')
                    ->where('journal_voucher_item_token_void', $data_journal_item['journal_voucher_item_token_void'])
                    ->get();

                    if(count($journal_voucher_item_token_void) == 0){
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
            }

            $msg = "Pembatalan Penerimaan Kas Sukses";
            return redirect('/cash-receipt')->with('msg',$msg);
        }
            
    }

    public function filterAcctCashReceipt(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/cash-receipt');
    }
    
    public function getProjectType($project_type_id)
    {
        $project_type = array (
            '0'	=> 'Proyek WBM',
            '1'	=> 'Proyek Non WBM',
        );
        return $project_type[$project_type_id];
    }
    
    public function getAccountName($account_id)
    {
        $account = AcctAccount::select('account_name')
        ->where('account_id', '=', $account_id)
        ->first();

        if($account == null){
            return '-';
        }

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
    
    public function addArrayAcctCashReceiptItem(Request $request)
    {
        $dataacctreceiptitem = array(
            'record_id'				    => date('YmdHis'),
            'account_id_item'		    => $request->account_id_item,
            // 'customer_id'		        => $request->customer_id,
            'cash_receipt_item_amount'	=> $request->cash_receipt_item_amount,
            'cash_receipt_item_title'	=> $request->cash_receipt_item_title,
        );

        $lastdataacctreceiptitem = Session::get('dataacctreceiptitem');
        if($lastdataacctreceiptitem !== null){
            array_push($lastdataacctreceiptitem, $dataacctreceiptitem);
            Session::put('dataacctreceiptitem', $lastdataacctreceiptitem);
        }else{
            $lastdataacctreceiptitem = [];
            array_push($lastdataacctreceiptitem, $dataacctreceiptitem);
            Session::push('dataacctreceiptitem', $dataacctreceiptitem);
        }
        
        return redirect('/cash-receipt/add');
    }

    public function deleteArrayAcctCashReceiptItem($record_id)
    {
        $arrayBaru			= array();
        $dataArrayHeader	= Session::get('dataacctreceiptitem');
        
        foreach($dataArrayHeader as $key=>$val){
            if($key != $record_id){
                $arrayBaru[$key] = $val;
            }
        }
        
        Session::forget('dataacctreceiptitem');
        Session::put('dataacctreceiptitem', $arrayBaru);

        return redirect('/cash-receipt/add');
    }

    public function processAddAcctCashReceipt(Request $request)
    {
        $session_AcctReceiptitem		= Session::get('dataacctreceiptitem');
        $branch_id = User::select('branch_id')->where('user_id','=',Auth::id())->first();
        
        $data_AcctReceipt = array(
            'branch_id'					    => $branch_id['branch_id'],
            'cash_receipt_date'				=> $request->cash_receipt_date,
            'account_id'				    => $request->account_id,
            'customer_id'				    => $request->customer_id,
            'cash_receipt_title'			=> $request->cash_receipt_title,
            'cash_receipt_description'		=> $request->cash_receipt_description,
            'cash_receipt_amount_total' 	=> $request->cash_receipt_amount_total,
            'cash_receipt_token' 			=> md5(rand()),
            'data_state' 				    => 0,
            'created_id' 				    => Auth::id(),
            'created_on' 				    => date('Y-m-d H:i:s'),
        );

        // dd($data_AcctReceipt);

        $transaction_module_code 	= "RC";

        $transaction_module_id 		= PreferenceTransactionModule::select('transaction_module_id')
        ->where('preference_transaction_module.transaction_module_code', '=', $transaction_module_code)
        ->first();

        $receipt_token 				= AcctCashReceipt::select('cash_receipt_token')
        ->where('cash_receipt_token', '=', $data_AcctReceipt['cash_receipt_token'])
        ->get();
        
        if (!empty($session_AcctReceiptitem)){
            if(count($receipt_token) == 0){
                if(AcctCashReceipt::create($data_AcctReceipt)){
                    $acctreceipt_last 		= AcctCashReceipt::select('cash_receipt_id', 'cash_receipt_no')
                    ->where('created_id', '=', $data_AcctReceipt['created_id'])
                    ->orderBy('cash_receipt_id', 'DESC')
                    ->first();
                    
                    $journal_voucher_period 	= date("Ym", strtotime($data_AcctReceipt['cash_receipt_date']));

                    $data_journal = array(
                        'branch_id'						=> $branch_id['branch_id'],
                        'journal_voucher_period' 		=> $journal_voucher_period,
                        'journal_voucher_date'			=> $data_AcctReceipt['cash_receipt_date'],
                        'journal_voucher_title'			=> $data_AcctReceipt['cash_receipt_title'],
                        'journal_voucher_no'			=> $acctreceipt_last['cash_receipt_no'],
                        'journal_voucher_description'	=> $data_AcctReceipt['cash_receipt_description'],
                        'transaction_module_id'			=> $transaction_module_id['transaction_module_id'],
                        'transaction_module_code'		=> $transaction_module_code,
                        'transaction_journal_id' 		=> $acctreceipt_last['cash_receipt_id'],
                        'transaction_journal_no' 		=> $acctreceipt_last['cash_receipt_no'],
                        'created_id' 					=> $data_AcctReceipt['created_id'],
                        'journal_voucher_token' 		=> $data_AcctReceipt['cash_receipt_token'],
                        'created_on' 					=> $data_AcctReceipt['created_on']
                    );
                    
                    AcctJournalVoucher::create($data_journal);		

                    $journal_voucher_id 	= AcctJournalVoucher::select('journal_voucher_id')
                    ->where('created_id', '=', $data_AcctReceipt['created_id'])
                    ->orderBy('journal_voucher_id', 'DESC')
                    ->first();

                    $account_id_default_status 	= AcctAccount::select('account_default_status')
                    ->where('account_id', '=', $data_AcctReceipt['account_id'])
                    ->where('data_state', '=' ,0)
                    ->first();

                    $data_debit = array (
                        'journal_voucher_id'			=> $journal_voucher_id['journal_voucher_id'],
                        'account_id'					=> $data_AcctReceipt['account_id'],
                        'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                        'journal_voucher_amount'		=> ABS($data_AcctReceipt['cash_receipt_amount_total']),
                        'journal_voucher_debit_amount'	=> ABS($data_AcctReceipt['cash_receipt_amount_total']),
                        'account_id_default_status'		=> $account_id_default_status['account_default_status'],
                        'account_id_status'				=> 1,
                        'journal_voucher_item_token'	=> $data_AcctReceipt['cash_receipt_token'].$data_AcctReceipt['account_id'].'1'
                    );

                    AcctJournalVoucherItem::create($data_debit);

                    $cash_receipt_id = AcctCashReceipt::select('cash_receipt_id')
                    ->where('created_id', '=', $data_AcctReceipt['created_id'])
                    ->orderBy('cash_receipt_id', 'DESC')
                    ->first();
                    
                    foreach($session_AcctReceiptitem as $key=>$val){
                        $data_AcctReceiptitem = array(
                            'cash_receipt_id'			=> $cash_receipt_id['cash_receipt_id'],
                            'account_id'			    => $val['account_id_item'],
                            'cash_receipt_item_title'	=> $val['cash_receipt_item_title'],
                            'cash_receipt_item_amount'	=> $val['cash_receipt_item_amount'],
                            'cash_receipt_item_token'	=> md5(rand()).$val['account_id_item']
                        );


                        if(AcctCashReceiptItem::create($data_AcctReceiptitem)){	
                            $account_id_default_status 	= AcctAccount::select('account_default_status')
                            ->where('account_id', '=',$data_AcctReceiptitem['account_id'])
                            ->where('data_state', '=',0)
                            ->first();

                            $data_credit = array (
                                'journal_voucher_id'			=> $journal_voucher_id['journal_voucher_id'],
                                'account_id'					=> $data_AcctReceiptitem['account_id'],
                                'journal_voucher_description'	=> $data_AcctReceiptitem['cash_receipt_item_title'],
                                'journal_voucher_amount'		=> ABS($data_AcctReceiptitem['cash_receipt_item_amount']),
                                'journal_voucher_credit_amount'	=> ABS($data_AcctReceiptitem['cash_receipt_item_amount']),
                                'account_id_default_status'		=> $account_id_default_status['account_default_status'],
                                'account_id_status'				=> 0,
                                'journal_voucher_item_token'	=> md5(rand()).$data_AcctReceiptitem['account_id'].'0'
                            );

                            AcctJournalVoucherItem::create($data_credit);

                            $username = User::select('name')->where('user_id','=',Auth::id())->first();

                            $this->set_log(Auth::id(), $username['name'],'1089','Application.cashAcctReceipt.cashAcctReceiptinsertprocess',$username['name'],'Add Cash Receipt');
   
                            continue;
                        } else {
                            $msg = "Tambah Data Penerimaan Kas Tidak Berhasil";
                            Session::forget('dataacctreceiptitem');
                            return redirect('/cash-receipt/add')->with('msg',$msg);
                            break;
                        }
                    }
                    Session::forget('dataacctreceiptitem');

                    $msg = "Tambah Data Penerimaan Kas Berhasil";
                    return redirect('/cash-receipt')->with('msg',$msg);
                }else{
                    $msg = "Tambah Data Penerimaan Kas Tidak Berhasil";
                    Session::forget('dataacctreceiptitem');
                    return redirect('/cash-receipt/add')->with('msg',$msg);
                }
            } else {
                $acctreceipt_last 		= AcctCashReceipt::select('cash_receipt_id', 'cash_receipt_no')
                ->where('created_id', '=', $data_AcctReceipt['created_id'])
                ->orderBy('cash_receipt_id', 'DESC')
                ->first();
                    
                $journal_voucher_period 	= date("Ym", strtotime($data_AcctReceipt['cash_receipt_date']));

                $data_journal = array(
                    'branch_id'						=> $branch_id['branch_id'],
                    'journal_voucher_period' 		=> $journal_voucher_period,
                    'journal_voucher_date'			=> $data_AcctReceipt['cash_receipt_date'],
                    'journal_voucher_title'			=> $data_AcctReceipt['cash_receipt_title'],
                    'journal_voucher_no'			=> $acctreceipt_last['cash_receipt_no'],
                    'journal_voucher_description'	=> $data_AcctReceipt['cash_receipt_description'],
                    'transaction_module_id'			=> $transaction_module_id['transaction_module_id'],
                    'transaction_module_code'		=> $transaction_module_code,
                    'transaction_journal_id' 		=> $acctreceipt_last['cash_receipt_id'],
                    'transaction_journal_no' 		=> $acctreceipt_last['cash_receipt_no'],
                    'created_id' 					=> $data_AcctReceipt['created_id'],
                    'journal_voucher_token' 		=> $data_AcctReceipt['cash_receipt_token'],
                    'created_on' 					=> $data_AcctReceipt['created_on']
                );

                $journal_voucher_token 	= AcctJournalVoucher::select('journal_voucher_token')
                ->where('journal_voucher_token', '=', $data_journal['journal_voucher_token'])
                ->get();

                if(count($journal_voucher_token) == 0){
                    AcctJournalVoucher::create($data_journal);	
                }   

                $journal_voucher_id 	= AcctJournalVoucher::select('journal_voucher_id')
                ->where('created_id', '=', $data_AcctReceipt['created_id'])
                ->orderBy('journal_voucher_id', 'DESC')
                ->first();

                $account_id_default_status 	= AcctAccount::select('account_default_status')
                ->where('account_id', '=', $data_AcctReceipt['account_id'])
                ->where('data_state', '=', 0)
                ->first();

                $data_debit = array (
                    'journal_voucher_id'			=> $journal_voucher_id['journal_voucher_id'],
                    'account_id'					=> $data_AcctReceipt['account_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($data_AcctReceipt['cash_receipt_amount_total']),
                    'journal_voucher_debit_amount'	=> ABS($data_AcctReceipt['cash_receipt_amount_total']),
                    'account_id_default_status'		=> $account_id_default_status['account_default_status'],
                    'account_id_status'				=> 1,
                    'journal_voucher_item_token'	=> $data_AcctReceipt['cash_receipt_token'].$data_AcctReceipt['account_id'].'1'
                );

                $journal_voucher_item_token 	= AcctJournalVoucherItem::select('journal_voucher_item_token')
                ->where('journal_voucher_item_token', '=', $data_debit['journal_voucher_item_token'])
                ->get();

                if(count($journal_voucher_item_token) == 0){
                    AcctJournalVoucherItem::create($data_debit);
                }

                $cash_receipt_id =AcctCashReceipt::select('cash_receipt_id')
                ->where('created_id', $data_AcctReceipt['created_id'])
                ->orderBy('cash_receipt_id', 'DESC')
                ->first();	
                
                foreach($session_AcctReceiptitem as $key=>$val){
                    $data_AcctReceiptitem = array(
                        'cash_receipt_id'			=> $cash_receipt_id['receipt_id'],
                        'account_id'			=> $val['account_id_item'],
                        'cash_receipt_item_title'	=> $val['cash_receipt_item_title'],
                        'cash_receipt_item_amount'	=> $val['cash_receipt_item_amount'],
                        'cash_receipt_item_token'	=> md5(rand()).$val['account_id_item']
                    );

                    $receipt_item_token = AcctCashReceiptItem::select('cash_receipt_item_token')
                    ->where('cash_receipt_item_token', '=', $data_AcctReceiptitem['cash_receipt_item_token'])
                    ->get();

                    if(count($receipt_item_token) == 0){
                        if(AcctCashReceiptItem::create($data_AcctReceiptitem)){	
                            $account_id_default_status 	= AcctAccount::select('account_default_status')
                            ->where('account_id', '=', $data_AcctReceiptitem['account_id'])
                            ->where('data_state', '=', 0)
                            ->first();

                            $data_credit = array (
                                'journal_voucher_id'			=> $journal_voucher_id['journal_voucher_id'],
                                'account_id'					=> $data_AcctReceiptitem['account_id'],
                                'journal_voucher_description'	=> $data_AcctReceiptitem['cash_receipt_item_title'],
                                'journal_voucher_amount'		=> ABS($data_AcctReceiptitem['cash_receipt_item_amount']),
                                'journal_voucher_credit_amount'	=> ABS($data_AcctReceiptitem['cash_receipt_item_amount']),
                                'account_id_default_status'		=> $account_id_default_status['account_default_status'],
                                'account_id_status'				=> 0,
                                'journal_voucher_item_token'	=> md5(rand()).$data_AcctReceiptitem['account_id'].'0'
                            );

                            $journal_voucher_item_token 	= AcctJournalVoucherItem::select('journal_voucher_item_token')
                            ->where('journal_voucher_item_token', '=', $data_credit['journal_voucher_item_token'])
                            ->get();

                            if(count($journal_voucher_item_token) == 0){
                                AcctJournalVoucherItem::create($data_credit);
                            }

                            $username = User::select('name')->where('user_id','=',Auth::id())->first();

                            $this->set_log(Auth::id(), $username['name'],'1089','Application.cashAcctReceipt.cashAcctReceiptinsertprocess',$username['name'],'Add Cash Receipt');

                            
                            continue;
                        } else {
                            $msg = "Tambah Data Penerimaan Kas Tidak Berhasil";
                            Session::forget('dataacctreceiptitem');
                            return redirect('/cash-receipt/add')->with('msg',$msg);
                            break;
                        }
                    }	
                }
                Session::forget('dataacctreceiptitem');
                $msg = "Tambah Data Penerimaan Kas Berhasil";
                return redirect('/cash-receipt')->with('msg',$msg);
            }
            
        } else {
            $msg = "Data Penerimaan Kas Kosong";
            Session::forget('dataacctreceiptitem');
            return redirect('/cash-receipt/add')->with('msg',$msg);
        }
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
}
