<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\InvWarehouse;
use App\Models\CoreBank;
use App\Models\CoreBranch;
use App\Models\CoreCustomer;
use App\Models\CoreSupplier;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\InvItemCategory;
use App\Models\InvItemUnit;
use App\Models\InvItemType;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\SalesInvoice;
use App\Models\SalesCollection;
use App\Models\SalesCollectionGiro;
use App\Models\SalesCollectionItem;
use App\Models\SalesCollectionPiece;
use App\Models\SalesCollectionPieceType;
use App\Models\SalesCollectionTransfer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Arr;

class SalesCollectionController extends Controller
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
        Session::forget('salescollectionelements');
        Session::forget('datasalescollectiontransfer');

        if(!Session::get('start_date')){
            $start_date     = date('Y-m-d');
        }else{
            $start_date     = Session::get('start_date');
        }

        if(!Session::get('end_date')){
            $end_date       = date('Y-m-d');
        }else{
            $end_date       = Session::get('end_date');
        }

        $customer_id        = Session::get('customer_id');

        $corecustomer       = CoreCustomer::where('data_state', 0)
        ->pluck('customer_name', 'customer_id');

        $salescollection    = SalesCollection::where('data_state', 0)
        ->where('collection_date', '>=', $start_date)
        ->where('collection_date', '<=',$end_date);
        if(!$customer_id||$customer_id == ''||$customer_id == null){
        }else{
            $salescollection = $salescollection->where('customer_id', $customer_id);
        }
        $salescollection    = $salescollection->get();

        return view('content/SalesCollection/ListSalesCollection',compact('corecustomer', 'salescollection', 'start_date', 'end_date', 'customer_id'));
    }

    public function filterSalesCollection(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $customer_id    = $request->customer_id;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('customer_id', $customer_id);

        return redirect('/sales-collection');
    }

    public function searchCoreCustomer(){

        Session::forget('salescollectionelements');
        Session::forget('datasalescollectiontransfer');
        
        $corecustomer = SalesInvoice::select('sales_invoice.customer_id', 'core_customer.customer_name', 'core_customer.customer_address', DB::raw("SUM(sales_invoice.owing_amount) as total_owing_amount"))
        ->join('core_customer', 'core_customer.customer_id', 'sales_invoice.customer_id')
        ->where('sales_invoice.data_state', 0)
        ->where('core_customer.data_state', 0)
        ->groupBy('sales_invoice.customer_id')
        ->orderBy('core_customer.customer_name', 'ASC')
        ->get();
        
        return view('content/SalesCollection/SearchCoreCustomer',compact('corecustomer'));
    }


    public function getPiece($sales_invoice_id){
        $salescollectionpiece = SalesCollectionPiece::select('*')
        ->where('sales_invoice_id', $sales_invoice_id)
        ->where('data_state', 0)
        ->get();  
       // dd($salescollectionpiece);
        return ($salescollectionpiece);
    }

    public function addSalesCollection($customer_id){

        $salesinvoiceowing = SalesInvoice::select('sales_invoice.sales_invoice_id', 'sales_invoice.customer_id', 'sales_invoice.owing_amount', 'sales_invoice.sales_invoice_date', 'sales_invoice.paid_amount','sales_invoice.buyers_acknowledgment_no', 'sales_invoice.sales_invoice_no', 'sales_invoice.subtotal_amount', 'sales_invoice.discount_percentage', 'sales_invoice.discount_amount', 'sales_invoice.total_amount', 'sales_invoice.goods_received_note_no')
        // ->join('sales_invoice_item', 'sales_invoice_item.sales_invoice_id', 'sales_invoice.sales_invoice_id')
        // ->join('sales_delivery_note_item_stock', 'sales_delivery_note_item_stock.item_stock_id', 'sales_invoice_item.item_stock_id')
        // ->join('inv_item_stock', 'inv_item_stock.item_stock_id', 'sales_delivery_note_item_stock.item_stock_id')
        // ->join('inv_goods_received_note', 'inv_goods_received_note.goods_received_note_id', 'inv_item_stock.goods_received_note_id')
        ->where('sales_invoice.customer_id', $customer_id)
        ->where('sales_invoice.owing_amount', '>', 0)
        ->where('sales_invoice.data_state', 0)
        ->get(); 

        $customer = CoreCustomer::findOrfail($customer_id);


        $acctaccount    = AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))
        ->where('acct_account.data_state', 0)
        ->where('parent_account_status', 0)
        ->pluck('full_name','account_id');

        $select = array(
            '1' => 'biasa',
            '2' => 'promosi'
        );

        $salescollectionelements = Session::get('salescollectionelements');
        $salescollectiontransfer = Session::get('datasalescollectiontransfer');
        
        return view('content/SalesCollection/FormAddSalesCollection',compact('select','customer_id', 'salesinvoiceowing', 'customer', 'acctaccount', 'salescollectionelements', 'salescollectiontransfer'));
    }

    public function detailSalesCollection($collection_id){

        $salescollection = SalesCollection::findOrFail($collection_id);

        $salescollectionitem = SalesCollectionItem::select('sales_collection_item.*', 'sales_invoice.sales_invoice_date', 'sales_invoice.sales_invoice_no', 'sales_collection_item.shortover_amount AS shortover_value')
        ->join('sales_invoice', 'sales_invoice.sales_invoice_id', 'sales_collection_item.sales_invoice_id')
        ->where('collection_id', $salescollection['collection_id'])
        ->get();

        $salescollectiontransfer = SalesCollectionTransfer::where('collection_id', $salescollection['collection_id'])
        ->get();

        $customer = CoreCustomer::where('data_state', 0)
        ->where('customer_id', $salescollection['customer_id'])
        ->first();
        
        return view('content/SalesCollection/FormDetailSalesCollection',compact('collection_id', 'salescollection', 'salescollectionitem', 'salescollectiontransfer',  'customer'));
    }

    public function deleteSalesCollection($collection_id){

        $salescollection = SalesCollection::findOrFail($collection_id);

        $salescollectionitem = SalesCollectionItem::select('sales_collection_item.*', 'sales_invoice.sales_invoice_date', 'sales_invoice.sales_invoice_no', 'sales_collection_item.shortover_amount AS shortover_value')
        ->join('sales_invoice', 'sales_invoice.sales_invoice_id', 'sales_collection_item.sales_invoice_id')
        ->where('collection_id', $salescollection['collection_id'])
        ->get();

        $salescollectiontransfer = SalesCollectionTransfer::where('collection_id', $salescollection['collection_id'])
        ->get();

        $customer = CoreCustomer::where('data_state', 0)
        ->where('customer_id', $salescollection['customer_id'])
        ->first();
        
        return view('content/SalesCollection/FormDeleteSalesCollection',compact('collection_id', 'salescollection', 'salescollectionitem', 'salescollectiontransfer',  'customer'));
    }

    public function elements_add(Request $request){
        $salescollectionelements= Session::get('salescollectionelements');
        if(!$salescollectionelements || $salescollectionelements == ''){
            $salescollectionelements['collection_date']                = '';
            $salescollectionelements['collection_remark']              = '';
            $salescollectionelements['cash_account_id']             = '';
            $salescollectionelements['collection_total_cash_amount']   = '';
        }
        $salescollectionelements[$request->name] = $request->value;
        Session::put('salescollectionelements', $salescollectionelements);
    }
    
    public function processAddTransferArray(Request $request)
    {
        $salescollectiontransfer = array(
            'transfer_account_id'              => $request->transfer_account_id,
            'collection_transfer_bank_name'    => $request->collection_transfer_bank_name,
            'collection_transfer_account_name' => $request->collection_transfer_account_name,
            'collection_transfer_account_no'   => $request->collection_transfer_account_no,
            'collection_transfer_amount'       => $request->collection_transfer_amount,
        );

        $lastsalescollectiontransfer = Session::get('datasalescollectiontransfer');
        if($lastsalescollectiontransfer !== null){
            array_push($lastsalescollectiontransfer, $salescollectiontransfer);
            Session::put('datasalescollectiontransfer', $lastsalescollectiontransfer);
        }else{
            $lastsalescollectiontransfer = [];
            array_push($lastsalescollectiontransfer, $salescollectiontransfer);
            Session::push('datasalescollectiontransfer', $salescollectiontransfer);
        }
    }
    
    public function processAddSalesCollection(Request $request)
    {
        $allrequest = $request->all();
        $datasalescollectiontransfer = Session::get('datasalescollectiontransfer');
        $fields = $request->validate([
            'collection_date'                   => 'required',
        ]);

        $paymenttype = $request->payment_type;

        $payment_account_id = 5 ;
        if($paymenttype == 0){
            $payment_account_id = 5 ;
        }else{
            $payment_account_id = 8 ;
        }
//----------Type Transfer
        if(is_array($datasalescollectiontransfer) && !empty($datasalescollectiontransfer)){
            foreach ($datasalescollectiontransfer as $keyTransfer => $valTransfer) {
                $transfer_account_id = $valTransfer['transfer_account_id'];
                $data = array (
                    'collection_date'                   => $fields['collection_date'],
                    'cash_account_id'				    => $payment_account_id,
                    'customer_id'						=> $request->customer_id,
                    'collection_remark'					=> $request->collection_remark,
                    'collection_amount'					=> $request->collection_amount,
                    'collection_allocated'			    => $request->allocation_total,
                    'collection_shortover'			    => $request->shortover_total,
                    'collection_total_amount'		    => $request->collection_amount,
                    'collection_total_cash_amount'	    => $request->collection_total_cash_amount,
                    'collection_total_transfer_amount'  => $request->collection_total_transfer_amount,
                    'data_state'						=> 0,
                    'created_on'						=> date("Y-m-d H:i:s"),
                    'created_id'						=> Auth::id(),
                    'branch_id'                         => Auth::user()->branch_id,
                );
            }
        }else{
//----------Type Tunai            
            $data = array (
                'collection_date'                   => $fields['collection_date'],
                'cash_account_id'				    => $payment_account_id,
                'customer_id'						=> $request->customer_id,
                'collection_remark'					=> $request->collection_remark,
                'collection_amount'					=> $request->collection_amount,
                'collection_allocated'			    => $request->allocation_total,
                'collection_shortover'			    => $request->shortover_total,
                'collection_total_amount'		    => $request->collection_amount,
                'collection_total_cash_amount'	    => $request->collection_total_cash_amount,
                'collection_total_transfer_amount'  => $request->collection_total_transfer_amount,
                'data_state'						=> 0,
                'created_on'						=> date("Y-m-d H:i:s"),
                'created_id'						=> Auth::id(),
                'branch_id'                         => Auth::user()->branch_id,
            );
        }

        $collection_total_amount = $data['collection_allocated'] + $data['collection_shortover'];
        $selisih_shortover = $data['collection_total_amount'] - $collection_total_amount;
        $transaction_module_code 	= "SC";

        $transactionmodule 		    = PreferenceTransactionModule::where('transaction_module_code', $transaction_module_code)
        ->first();
        $transaction_module_id 		= $transactionmodule['transaction_module_id'];
        $preferencecompany 			= PreferenceCompany::first();
        
        if(SalesCollection::create($data)){
            $SalesCollection_last 		= SalesCollection::select('collection_id', 'collection_no')
            ->where('created_id', $data['created_id'])
            ->orderBy('collection_id', 'DESC')
            ->first();
            
//----------Header Journal Voucher
            $journal_voucher_period 	= date("Ym", strtotime($data['collection_date']));
            $data_journal = array(
                'branch_id'						=> $data['branch_id'],
                'journal_voucher_period' 		=> $journal_voucher_period,
                'journal_voucher_date'			=> $data['collection_date'],
                'journal_voucher_title'			=> 'Pelunasan Piutang '.$SalesCollection_last['collection_no'],
                'journal_voucher_no'			=> $SalesCollection_last['collection_no'],
                'journal_voucher_description'	=> $data['collection_remark'],
                'transaction_module_id'			=> $transaction_module_id,
                'transaction_module_code'		=> $transaction_module_code,
                'transaction_journal_id' 		=> $SalesCollection_last['collection_id'],
                'transaction_journal_no' 		=> $SalesCollection_last['collection_no'],
                'created_id' 					=> $data['created_id'],
                'created_on' 					=> $data['created_on']
            );
            AcctJournalVoucher::create($data_journal);		

            $journalvoucher = AcctJournalVoucher::where('created_id', $data['created_id'])
            ->orderBy('journal_voucher_id', 'DESC')
            ->first();

            $journal_voucher_id 	= $journalvoucher['journal_voucher_id'];

            $collection = SalesCollection::where('created_id', $data['created_id'])
            ->orderBy('collection_id', 'DESC')
            ->first();

            $collection_id = $collection['collection_id'];

            for($i = 1; $i < $request->item_total; $i++){
                $data_collectionitem = array(
                    'collection_id'		 		=> $collection_id,
                    'sales_invoice_id' 		    => $allrequest[$i.'_sales_invoice_id'],
                    'sales_invoice_no' 		    => $allrequest[$i.'_sales_invoice_no'],
                    'sales_invoice_date' 	    => $allrequest[$i.'_sales_invoice_date'],
                    'sales_invoice_amount'	    => $allrequest[$i.'_sales_invoice_amount'],
                    'total_amount' 				=> $allrequest[$i.'_total_amount'],
                    'paid_amount' 				=> $allrequest[$i.'_paid_amount'],
                    'owing_amount' 				=> $allrequest[$i.'_owing_amount'],
                    'allocation_amount' 		=> $allrequest[$i.'_allocation'],
                    'shortover_amount'	 		=> $allrequest[$i.'_shortover'],
                    'last_balance' 				=> $allrequest[$i.'_last_balance']
                );

                if($data_collectionitem['allocation_amount'] > 0){
                    if(SalesCollectionItem::create($data_collectionitem)){

                        $salesinvoice = SalesInvoice::where('data_state', 0)
                        ->where('sales_invoice_id', $data_collectionitem['sales_invoice_id'])
                        ->first();

                        $salesinvoice->paid_amount       = $salesinvoice['paid_amount'] + $data_collectionitem['allocation_amount'] + $data_collectionitem['shortover_amount'];
                        $salesinvoice->owing_amount      = $data_collectionitem['last_balance'];
                        $salesinvoice->shortover_amount  = $salesinvoice['shortover_amount'] + $data_collectionitem['shortover_amount'];
                        $salesinvoice->save();

                        $msg = "Tambah Pelunasan Piutang Berhasil";
                        continue;
                    }else{
                        $msg = "Tambah Pelunasan Piutang Gagal";
                        return redirect('/sales-collection/add/'.$data['customer_id'])->with('msg',$msg);
                    }
                }
                
            }
//----------Kas
            if($data['collection_total_cash_amount'] != '' || $data['collection_total_cash_amount'] != 0){

                $account = AcctAccount::where('account_id', 5)
                ->where('data_state', 0)
                ->first();

                $account_id_default_status = $account['account_default_status'];

                $data_debet = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> 5,
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($data['collection_total_cash_amount']),
                    'journal_voucher_debit_amount'	=> ABS($data['collection_total_cash_amount']),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                );
                AcctJournalVoucherItem::create($data_debet);
            }
//----------Bank
            if(is_array($datasalescollectiontransfer) && !empty($datasalescollectiontransfer)){
                foreach ($datasalescollectiontransfer as $keyTransfer => $valTransfer) {
                    $transfer_account_id = $valTransfer['transfer_account_id'];
                    $datatransfer = array(
                        'collection_id'							=> $collection_id,
                        'account_id' 							=> $transfer_account_id,
                        'collection_transfer_bank_name'			=> $valTransfer['collection_transfer_bank_name'],
                        'collection_transfer_amount'			=> $valTransfer['collection_transfer_amount'],
                        'collection_transfer_account_name'		=> $valTransfer['collection_transfer_account_name'],
                        'collection_transfer_account_no'		=> $valTransfer['collection_transfer_account_no'],
                    );
                    if(SalesCollectionTransfer::create($datatransfer)){
                        $account = AcctAccount::where('account_id', $transfer_account_id)
                        ->where('data_state', 0)
                        ->first();
        
                        $account_id_default_status = $account['account_default_status'];
                        $data_debet = array (
                            'journal_voucher_id'			=> $journal_voucher_id,
                            'account_id'					=> $transfer_account_id,
                            'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                            'journal_voucher_amount'		=> ABS($datatransfer['collection_transfer_amount']),
                            'journal_voucher_debit_amount'	=> ABS($datatransfer['collection_transfer_amount']),
                            'account_id_default_status'		=> $account_id_default_status,
                            'account_id_status'				=> 1,
                        );
                        AcctJournalVoucherItem::create($data_debet);	
                    }
                }
            }
//jika check Piutang Retur
        $accountReturn 		= AcctAccount::where('account_id', 48)
        ->where('data_state', 0)
        ->first();
        $account_id_default_status_return 		= $accountReturn['account_default_status'];

        $cekReturn = $request->piutang;
        if($cekReturn == 1){
            $data_credit = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> 48,
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($request->piutang_amount),
                'journal_voucher_credit_amount'	=> ABS($request->piutang_amount),
                'account_id_default_status'		=> $account_id_default_status_return,
                'account_id_status'				=> 0,
            );
            AcctJournalVoucherItem::create($data_credit);
        }
//jika check Piutang Promosi
        $accountReturn 		= AcctAccount::where('account_id', 50)
        ->where('data_state', 0)
        ->first();
        $account_id_default_status_promosi 		= $accountReturn['account_default_status'];

        $cekPromosi = $request->promosi;
        if($cekPromosi == 1){
            $data_credit = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> 50,
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($request->promotion_amount),
                'journal_voucher_credit_amount'	=> ABS($request->promotion_amount),
                'account_id_default_status'		=> $account_id_default_status_promosi,
                'account_id_status'				=> 0,
            );
            AcctJournalVoucherItem::create($data_credit);
        }
//jika check Adm Bank
        $accountReturn 		= AcctAccount::where('account_id', 528)
        ->where('data_state', 0)
        ->first();
        $account_id_default_status_adm_cost 		= $accountReturn['account_default_status'];

        $cekAdmCost = $request->adm_cost;
        if($cekAdmCost == 1){
            $data_credit = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> 528,
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($request->adm_cost_amount),
                'journal_voucher_credit_amount'	=> ABS($request->adm_cost_amount),
                'account_id_default_status'		=> $account_id_default_status_adm_cost,
                'account_id_status'				=> 0,
            );
            AcctJournalVoucherItem::create($data_credit);
        }
//----------Piutang Piutang Non Anggota
            $account = AcctAccount::where('account_id', 42)
                ->where('data_state', 0)
                ->first();
                $account_id_default_status = $account['account_default_status'];
                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> 42,
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($selisih_shortover),
                    'journal_voucher_credit_amount'	=> ABS($selisih_shortover),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                );
                AcctJournalVoucherItem::create($data_credit);

//----------Piutang Piutang Non Anggota (sisa piutang jika dibayar > 1 kali)
            if($selisih_shortover > 0){
                $account = AcctAccount::where('account_id', 42)
                ->where('data_state', 0)
                ->first();
                $account_id_default_status = $account['account_default_status'];
                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> 42,
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($selisih_shortover),
                    'journal_voucher_credit_amount'	=> ABS($selisih_shortover),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                );
                AcctJournalVoucherItem::create($data_credit);

            } else if($selisih_shortover < 0){
                $account = AcctAccount::where('account_id', 42)
                ->where('data_state', 0)
                ->first();
                $account_id_default_status = $account['account_default_status'];
                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> 42,
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($selisih_shortover),
                    'journal_voucher_credit_amount'	=> ABS($selisih_shortover),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                );
                AcctJournalVoucherItem::create($data_credit);
            }

            $msg = "Tambah Pelunasan Piutang Berhasil";
            return redirect('/sales-collection')->with('msg',$msg);
        }else{
            $msg = "Tambah Pelunasan Piutang Gagal";
            return redirect('/sales-collection/add/'.$data['customer_id'])->with('msg',$msg);
        }
    }

    public function processVoidSalesCollection(Request $request){

        $collection_no 			        = $request->collection_no;
        
        $salescollection                = SalesCollection::findOrFail($request->collection_id);
        $salescollection->voided_remark = $request->voided_remark;
        $salescollection->voided_on     = date('Y-m-d H:i:s');
        $salescollection->voided_id     = Auth::id();
        $salescollection->data_state    = 2;
        

        if($salescollection->save()){
            $salescollectionitem 	= SalesCollectionItem::where('collection_id', $request->collection_id)->get();

            foreach ($salescollectionitem as $ki => $vi){
                $salesinvoice = SalesInvoice::where('sales_invoice_id', $vi['sales_invoice_id'])->first();
                $salesinvoice->paid_amount       = $salesinvoice['paid_amount'] - ($vi['allocation_amount'] + $vi['shortover_amount']);
                $salesinvoice->owing_amount      = $salesinvoice['owing_amount'] + ($vi['allocation_amount'] + $vi['shortover_amount']);
                $salesinvoice->shortover_amount  = $salesinvoice['shortover_amount'] - $vi['shortover_amount'];

                $salesinvoice->save();
            }

            $journalvoucher = AcctJournalVoucher::where('transaction_journal_no', $collection_no)
            ->first();

            $journal_voucher_id = $journalvoucher['journal_voucher_id'];

            $acctjournalvoucheritem = AcctJournalVoucherItem::where('journal_voucher_id', $journal_voucher_id)
            ->get();

            $data_journal = array (
                "journal_voucher_id"			=> $journal_voucher_id,
                "voided"						=> 1,
                "voided_id"						=> Auth::id(),
                "voided_on"						=> date('Y-m-d H:i:s'),
                "voided_remark" 				=> $request->voided_remark,
                'data_state'					=> 2,
            );

            $data_journal = AcctJournalVoucher::where('journal_voucher_id', $journal_voucher_id)->first();
            $data_journal->voided           = 1;
            $data_journal->voided_id        = Auth::id();
            $data_journal->voided_on        = date('Y-m-d H:i:s');
            $data_journal->voided_remark    = $request->voided_remark;
            $data_journal->data_state       = 2;

            if ($data_journal->save()){
                foreach ($acctjournalvoucheritem as $keyItem => $valItem) {
                    $dataupdate_acctjournalvoucheritem = array (
                        'journal_voucher_item_id'			=> $valItem['journal_voucher_item_id'],
                        'journal_voucher_id'				=> $valItem['journal_voucher_id'],
                        'account_id'						=> $valItem['account_id'],
                        'journal_voucher_amount'			=> $valItem['journal_voucher_amount'],
                        'account_id_status'					=> $valItem['account_id_status'],
                        'data_state'						=> 2
                    );

                    $dataupdate_acctjournalvoucheritem = AcctJournalVoucherItem::where('journal_voucher_item_id', $valItem['journal_voucher_item_id'])
                    ->first();
                    $dataupdate_acctjournalvoucheritem->data_state = 2;
                    $dataupdate_acctjournalvoucheritem->save();

                }
            }

            $msg = "Pembatalan Pelunasan Piutang Berhasil";
            return redirect('/sales-collection')->with('msg',$msg);
        }else{
            $msg = "Pembatalan Pelunasan Piutang Gagal";
            return redirect('/sales-collection/delete/'.$request->collection_id)->with('msg',$msg);
        }
    }
    
    public function deleteTransferArray($record_id, $supplier_id)
    {
        $arrayBaru			= array();
        $dataArrayHeader	= Session::get('datasalescollectiontransfer');
        
        foreach($dataArrayHeader as $key=>$val){
            if($key != $record_id){
                $arrayBaru[$key] = $val;
            }
        }
        Session::forget('datasalescollectiontransfer');
        Session::put('datasalescollectiontransfer', $arrayBaru);

        return redirect('/sales-collection/add/'.$supplier_id);
    }

    public function getItemCategoryName($item_category_id){
        $itemcategory = InvItemCategory::where('data_state', 0)
        ->where('item_category_id', $item_category_id)
        ->first();

        return $itemcategory['item_category_name'];
    }

    public function getItemTypeName($item_type_id){
        $itemtype = InvItemType::where('data_state', 0)
        ->where('item_type_id', $item_type_id)
        ->first();

        return $itemtype['item_type_name'];
    }

    public function getItemUnitName($item_unit_id){
        $itemunit = InvItemUnit::where('data_state', 0)
        ->where('item_unit_id', $item_unit_id)
        ->first();

        return $itemunit['item_unit_name'];
    }

    public function getCoreSupplierName($supplier_id){
        $supplier = CoreSupplier::where('data_state', 0)
        ->where('supplier_id', $supplier_id)
        ->first();

        return $supplier['supplier_name'];
    }

    public function getCoreCustomerName($customer_id){
        $customer = CoreCustomer::where('data_state', 0)
        ->where('customer_id', $customer_id)
        ->first();

        return $customer['customer_name'];
    }

    public function getInvWarehouseName($warehouse_id){
        $warehouse = InvWarehouse::where('data_state', 0)
        ->where('warehouse_id', $warehouse_id)
        ->first();

        return $warehouse['warehouse_name'];
    }

    public function getAccountName($account_id){
        $account = AcctAccount::where('data_state', 0)
        ->where('account_id', $account_id)
        ->first();

        return $account['account_name']??'';
    }

    public function getCoreBankName($bank_id){
        $bank = CoreBank::where('data_state', 0)
        ->where('bank_id', $bank_id)
        ->first();

        return $bank['bank_name'];
    }
    

    public function addCoreBank(Request $request){
        $bank_code          = $request->bank_code;
        $bank_name          = $request->bank_name;
        $account_id         = $request->account_id;
        $bank_remark        = $request->bank_remark;
        $data               = '';
        
        $corebank = CoreBank::create([  
            'bank_code'     => $bank_code,
            'bank_name'     => $bank_name,
            'account_id'    => $account_id,
            'bank_remark'   => $bank_remark,
            'created_id'    => Auth::id()
        ]);

        $corebank = CoreBank::where('data_state', 0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($corebank as $mp){
            $data .= "<option value='$mp[bank_id]'>$mp[bank_name]</option>\n";	
        }

        return $data;
    }


	function doone2($onestr) {
	    $tsingle = array("","satu ","dua ","tiga ","empat ","lima ",
		"enam ","tujuh ","delapan ","sembilan ");
	      return strtoupper($tsingle[$onestr]);
	}	
	 
	function doone($onestr) {
	    $tsingle = array("","se","dua ","tiga ","empat ","lima ", "enam ","tujuh ","delapan ","sembilan ");
	      return strtoupper($tsingle[$onestr]);
	}	

	function dotwo($twostr) {
	    $tdouble = array("","puluh ","dua puluh ","tiga puluh ","empat puluh ","lima puluh ", "enam puluh ","tujuh puluh ","delapan puluh ","sembilan puluh ");
	    $teen = array("sepuluh ","sebelas ","dua belas ","tiga belas ","empat belas ","lima belas ", "enam belas ","tujuh belas ","delapan belas ","sembilan belas ");
	    if ( substr($twostr,1,1) == '0') {
			$ret = $this->doone2(substr($twostr,0,1));
	    } else if (substr($twostr,1,1) == '1') {
			$ret = $teen[substr($twostr,0,1)];
	    } else {
			$ret = $tdouble[substr($twostr,1,1)] . $this->doone2(substr($twostr,0,1));
	    }
	    return strtoupper($ret);
	}
    

	function numtotxt($num) {
		$tdiv 	= array("","","ratus ","ribu ", "ratus ", "juta ", "ratus ","miliar ");
		$divs 	= array( 0,0,0,0,0,0,0);
		$pos 	= 0; // index into tdiv;
		// make num a string, and reverse it, because we run through it backwards
		// bikin num ke string dan dibalik, karena kita baca dari arah balik
		$num 	= strval(strrev(number_format($num, 2, '.',''))); 
		$answer = ""; // mulai dari sini
		while (strlen($num)) {
			if ( strlen($num) == 1 || ($pos >2 && $pos % 2 == 1))  {
				$answer = $this->doone(substr($num, 0, 1)) . $answer;
				$num 	= substr($num,1);
			} else {
				$answer = $this->dotwo(substr($num, 0, 2)) . $answer;
				$num 	= substr($num,2);
				if ($pos < 2)
					$pos++;
			}

			if (substr($num, 0, 1) == '.') {
				if (! strlen($answer)){
					$answer = "";
				}

				$answer = "" . $answer . "";
				$num 	= substr($num,1);
				// kasih tanda "nol" jika tidak ada
				if (strlen($num) == 1 && $num == '0') {
					$answer = "" . $answer;
					$num 	= substr($num,1);
				}
			}
		    // add separator
		    if ($pos >= 2 && strlen($num)) {
				if (substr($num, 0, 1) != 0  || (strlen($num) >1 && substr($num,1,1) != 0
					&& $pos %2 == 1)  ) {
					// check for missed millions and thousands when doing hundreds
					// cek kalau ada yg lepas pada juta, ribu dan ratus
					if ( $pos == 4 || $pos == 6 ) {
						if ($divs[$pos -1] == 0)
							$answer = $tdiv[$pos -1 ] . $answer;
					}
					// standard
					$divs[$pos] = 1;
					$answer 	= $tdiv[$pos++] . $answer;
				} else {
					$pos++;
				}
			}
	    }
	    return strtoupper($answer.'rupiah');
	}
    
    public function processPrintingSalescollection($collection_id){
        $preference_company = PreferenceCompany::first();

        $salescollection = SalesCollection::select('sales_collection.*', 'core_customer.customer_name', 'core_customer.customer_address')
        ->join('core_customer', 'core_customer.customer_id', 'sales_collection.customer_id')
        ->where('collection_id', $collection_id)
        ->first();

        $city = CoreBranch::where('branch_id', Auth::user()->branch_id)
        ->first();

        // create new PDF document
        $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(7, 7, 7, 7);
        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        $pdf::SetFont('helvetica', 'B', 20);

        // add a page
        $pdf::AddPage();

        /*$pdf::Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);*/

        $pdf::SetFont('helvetica', '', 12);

        // -----------------------------------------------------------------------------

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">BUKTI PERMBAYARAN</div></td>
            </tr>
            <tr>
                <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl, true, false, false, false, '');
        

        $tbl1 = "
        Telah diterima uang dari :
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$salescollection['customer_name']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$salescollection['customer_address']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$this->numtotxt($salescollection['collection_total_amount'])."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: PEMBAYARAN PIUTANG</div></td>
            </tr>
                <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($salescollection['collection_total_amount'], 2)."</div></td>
            </tr>				
        </table>";

        $tbl2 = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">".$city['branch_address'].", ".date('d-m-Y')."</div></td>
            </tr>
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\">Penyetor</div></td>
                <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">Penerima</div></td>
            </tr>				
        </table>";

        $pdf::writeHTML($tbl1.$tbl2, true, false, false, false, '');


        // ob_clean();

        // -----------------------------------------------------------------------------
        $js = '';
        //Close and output PDF document
        $filename = 'Nota.pdf';

        // force print dialog
        $js .= 'print(true);';

        // set javascript
        $pdf::IncludeJS($js);
        
        $pdf::Output($filename, 'I');

        //============================================================+
        // END OF FILE
        //============================================================+
    }

}
