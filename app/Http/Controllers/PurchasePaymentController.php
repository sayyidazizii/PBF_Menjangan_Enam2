<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\InvWarehouse;
use App\Models\CoreBank;
use App\Models\CoreSupplier;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\InvItemCategory;
use App\Models\InvItemUnit;
use App\Models\InvItemType;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\PurchaseInvoice;
use App\Models\PurchasePayment;
use App\Models\PurchasePaymentGiro;
use App\Models\PurchasePaymentItem;
use App\Models\PurchasePaymentTransfer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class PurchasePaymentController extends Controller
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
        Session::forget('purchasepaymentelements');
        Session::forget('datapurchasepaymenttransfer');

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

        $supplier_id        = Session::get('supplier_id');

        $coresupplier       = CoreSupplier::where('data_state', 0)
        ->pluck('supplier_name', 'supplier_id');

        $purchasepayment    = PurchasePayment::where('data_state', 0)
        ->join('purchase_payment_item','purchase_payment_item.payment_id','purchase_payment.payment_id')
        ->where('payment_date', '>=', $start_date)
        ->where('payment_date', '<=',$end_date);
        if(!$supplier_id||$supplier_id == ''||$supplier_id == null){
        }else{
            $purchasepayment = $purchasepayment->where('supplier_id', $supplier_id);
        }
        $purchasepayment    = $purchasepayment->get();

        return view('content/PurchasePayment/ListPurchasePayment',compact('coresupplier', 'purchasepayment', 'start_date', 'end_date', 'supplier_id'));
    }

    public function filterPurchasePayment(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $supplier_id    = $request->supplier_id;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('supplier_id', $supplier_id);

        return redirect('/purchase-payment');
    }

    public function searchCoreSupplier(){

        Session::forget('purchasepaymentelements');
        Session::forget('datapurchasepaymenttransfer');
        
        $coresupplier = PurchaseInvoice::select('purchase_invoice.supplier_id', 'core_supplier.supplier_name', 'core_supplier.supplier_address', DB::raw("SUM(purchase_invoice.owing_amount) as total_owing_amount"))
        ->join('core_supplier', 'core_supplier.supplier_id', 'purchase_invoice.supplier_id')
        ->where('purchase_invoice.data_state', 0)
        ->where('core_supplier.data_state', 0)
        ->groupBy('purchase_invoice.supplier_id')
        ->orderBy('core_supplier.supplier_name', 'ASC')
        ->get();
        
        return view('content/PurchasePayment/SearchCoreSupplier',compact('coresupplier'));
    }

    public function addPurchasePayment($supplier_id){
        
        $purchaseinvoiceowing = PurchaseInvoice::select('purchase_invoice.faktur_tax_no','purchase_invoice.purchase_invoice_id', 'purchase_invoice.supplier_id', 'purchase_invoice.owing_amount', 'purchase_invoice.purchase_invoice_date', 'purchase_invoice.paid_amount', 'purchase_invoice.purchase_invoice_no', 'purchase_invoice.subtotal_amount', 'purchase_invoice.discount_percentage', 'purchase_invoice.discount_amount', 'purchase_invoice.tax_amount', 'purchase_invoice.total_amount')
        ->where('purchase_invoice.supplier_id', $supplier_id)
        ->where('purchase_invoice.owing_amount', '>', 0)
        ->where('purchase_invoice.data_state', 0)
        ->get();
        
        $PPN = $this->getTotalPpn($supplier_id);

        $supplier = CoreSupplier::findOrfail($supplier_id);

        $acctaccount    = AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))
        ->where('acct_account.data_state', 0)
        ->where('parent_account_status', 0)
        ->pluck('full_name','account_id');

        $corebank = CoreBank::where('data_state', 0)
        ->pluck('bank_name', 'bank_id');

        $purchasepaymentelements = Session::get('purchasepaymentelements');
        $purchasepaymenttransfer = Session::get('datapurchasepaymenttransfer');
        
        return view('content/PurchasePayment/FormAddPurchasePayment',compact('supplier_id', 'purchaseinvoiceowing', 'corebank', 'supplier', 'acctaccount', 'purchasepaymentelements', 'purchasepaymenttransfer','PPN'));
    }

    public function detailPurchasePayment($payment_id){

        $purchasepayment = PurchasePayment::findOrFail($payment_id);

        $purchasepaymentitem = PurchasePaymentItem::select('purchase_payment_item.*', 'purchase_invoice.purchase_invoice_date', 'purchase_invoice.purchase_invoice_no', 'purchase_payment_item.shortover_amount AS shortover_value')
        ->join('purchase_invoice', 'purchase_invoice.purchase_invoice_id', 'purchase_payment_item.purchase_invoice_id')
        ->where('payment_id', $purchasepayment['payment_id'])
        ->get();

        $purchasepaymenttransfer = PurchasePaymentTransfer::where('payment_id', $purchasepayment['payment_id'])
        ->get();

        $supplier = CoreSupplier::where('data_state', 0)
        ->where('supplier_id', $purchasepayment['supplier_id'])
        ->first();
        
        return view('content/PurchasePayment/FormDetailPurchasePayment',compact('payment_id', 'purchasepayment', 'purchasepaymentitem', 'purchasepaymenttransfer',  'supplier'));
    }

    public function deletePurchasePayment($payment_id){

        $purchasepayment = PurchasePayment::findOrFail($payment_id);

        $purchasepaymentitem = PurchasePaymentItem::select('purchase_payment_item.*', 'purchase_invoice.purchase_invoice_date', 'purchase_invoice.purchase_invoice_no', 'purchase_payment_item.shortover_amount AS shortover_value')
        ->join('purchase_invoice', 'purchase_invoice.purchase_invoice_id', 'purchase_payment_item.purchase_invoice_id')
        ->where('payment_id', $purchasepayment['payment_id'])
        ->get();

        $purchasepaymenttransfer = PurchasePaymentTransfer::where('payment_id', $purchasepayment['payment_id'])
        ->get();

        $supplier = CoreSupplier::where('data_state', 0)
        ->where('supplier_id', $purchasepayment['supplier_id'])
        ->first();
        
        return view('content/PurchasePayment/FormDeletePurchasePayment',compact('payment_id', 'purchasepayment', 'purchasepaymentitem', 'purchasepaymenttransfer',  'supplier'));
    }

    public function elements_add(Request $request){
        $purchasepaymentelements= Session::get('purchasepaymentelements');
        if(!$purchasepaymentelements || $purchasepaymentelements == ''){
            $purchasepaymentelements['payment_date']                = '';
            $purchasepaymentelements['payment_remark']              = '';
            $purchasepaymentelements['cash_account_id']             = '';
            $purchasepaymentelements['payment_total_cash_amount']   = '';
        }
        $purchasepaymentelements[$request->name] = $request->value;
        Session::put('purchasepaymentelements', $purchasepaymentelements);
    }
    
    public function processAddTransferArray(Request $request)
    {
        $purchasepaymenttransfer = array(
            'bank_id'                       => $request->bank_id,
            'payment_transfer_account_name' => $request->payment_transfer_account_name,
            'payment_transfer_account_no'   => $request->payment_transfer_account_no,
            'payment_transfer_amount'       => $request->payment_transfer_amount,
        );

        $lastpurchasepaymenttransfer = Session::get('datapurchasepaymenttransfer');
        if($lastpurchasepaymenttransfer !== null){
            array_push($lastpurchasepaymenttransfer, $purchasepaymenttransfer);
            Session::put('datapurchasepaymenttransfer', $lastpurchasepaymenttransfer);
        }else{
            $lastpurchasepaymenttransfer = [];
            array_push($lastpurchasepaymenttransfer, $purchasepaymenttransfer);
            Session::push('datapurchasepaymenttransfer', $purchasepaymenttransfer);
        }
    }
    
    public function processAddPurchasePayment(Request $request)
    {
        $allrequest = $request->all();
        // dd($allrequest);
        $datapurchasepaymenttransfer = Session::get('datapurchasepaymenttransfer');

        $fields = $request->validate([
            'payment_date'                      => 'required',
        ]);

        $paymenttype = $request->payment_type;

        $payment_account_id = 5 ;
        if($paymenttype == 0){
            $payment_account_id = 5 ;
        }else{
            $payment_account_id = 8 ;
        }
        
        if(is_array($datapurchasepaymenttransfer) && !empty($datapurchasepaymenttransfer)){
            foreach ($datapurchasepaymenttransfer as $keyTransfer => $valTransfer) {
                $transfer_bank = CoreBank::where('bank_id', $valTransfer['bank_id'])
                ->first();

                $transfer_account_id = $transfer_bank['account_id'];
        $data = array (
            'payment_date'                      => $fields['payment_date'],
            'cash_account_id'				    => $transfer_account_id,
            'supplier_id'						=> $request->supplier_id,
            'payment_remark'					=> $request->payment_remark,
            'payment_amount'					=> $request->payment_amount,
            'payment_allocated'					=> $request->allocation_total,
            'payment_shortover'					=> $request->shortover_total,
            'payment_total_amount'				=> $request->payment_amount,
            'payment_total_cash_amount'			=> $request->payment_total_cash_amount,
            'payment_total_transfer_amount'		=> $request->payment_total_transfer_amount,
            'data_state'						=> 0,
            'created_on'						=> date("Y-m-d H:i:s"),
            'created_id'						=> Auth::id(),
            'branch_id'                         => Auth::user()->branch_id,
        );
    }
    }else{
        $data = array (
            'payment_date'                      => $fields['payment_date'],
            'cash_account_id'				    => $payment_account_id,
            'supplier_id'						=> $request->supplier_id,
            'payment_remark'					=> $request->payment_remark,
            'payment_amount'					=> $request->payment_amount,
            'payment_allocated'					=> $request->allocation_total,
            'payment_shortover'					=> $request->shortover_total,
            'payment_total_amount'				=> $request->payment_amount,
            'payment_total_cash_amount'			=> $request->payment_total_cash_amount,
            'payment_total_transfer_amount'		=> $request->payment_total_transfer_amount,
            'data_state'						=> 0,
            'created_on'						=> date("Y-m-d H:i:s"),
            'created_id'						=> Auth::id(),
            'branch_id'                         => Auth::user()->branch_id,
        );
    }
        //dd($data['cash_account_id']);

        $payment_total_amount = $data['payment_allocated'] + $data['payment_shortover'];

        $selisih_shortover = $data['payment_total_amount'] - $payment_total_amount;

        $transaction_module_code 	= "PP";

        $transactionmodule 		    = PreferenceTransactionModule::where('transaction_module_code', $transaction_module_code)
        ->first();

        $transaction_module_id 		= $transactionmodule['transaction_module_id'];

        $preferencecompany 			= PreferenceCompany::first();
        
        if(PurchasePayment::create($data)){
            $PurchasePayment_last 		= PurchasePayment::select('payment_id', 'payment_no')
            ->where('created_id', $data['created_id'])
            ->orderBy('payment_id', 'DESC')
            ->first();
            
            $journal_voucher_period 	= date("Ym", strtotime($data['payment_date']));

            $data_journal = array(
                'branch_id'						=> $data['branch_id'],
                'journal_voucher_period' 		=> $journal_voucher_period,
                'journal_voucher_date'			=> $data['payment_date'],
                'journal_voucher_title'			=> 'Pembayaran hutang '.$PurchasePayment_last['payment_no'],
                'journal_voucher_no'			=> $PurchasePayment_last['payment_no'],
                'journal_voucher_description'	=> $data['payment_remark'],
                'transaction_module_id'			=> $transaction_module_id,
                'transaction_module_code'		=> $transaction_module_code,
                'transaction_journal_id' 		=> $PurchasePayment_last['payment_id'],
                'transaction_journal_no' 		=> $PurchasePayment_last['payment_no'],
                'created_id' 					=> $data['created_id'],
                'created_on' 					=> $data['created_on']
            );
            
            AcctJournalVoucher::create($data_journal);		

            $journalvoucher = AcctJournalVoucher::where('created_id', $data['created_id'])
            ->orderBy('journal_voucher_id', 'DESC')
            ->first();

            $journal_voucher_id 	= $journalvoucher['journal_voucher_id'];

            $payment = PurchasePayment::where('created_id', $data['created_id'])
            ->orderBy('payment_id', 'DESC')
            ->first();

            $payment_id = $payment['payment_id'];

            for($i = 1; $i < $request->item_total; $i++){
                $data_paymentitem = array(
                    'payment_id'		 		=> $payment_id,
                    'purchase_invoice_id' 		=> $allrequest[$i.'_purchase_invoice_id'],
                    'purchase_invoice_no' 		=> $allrequest[$i.'_purchase_invoice_no'],
                    'purchase_invoice_date' 	=> $allrequest[$i.'_purchase_invoice_date'],
                    'purchase_invoice_amount'	=> $allrequest[$i.'_purchase_invoice_amount'],
                    'total_amount' 				=> $allrequest[$i.'_total_amount'],
                    'paid_amount' 				=> $allrequest[$i.'_paid_amount'],
                    'owing_amount' 				=> $allrequest[$i.'_owing_amount'],
                    'allocation_amount' 		=> $allrequest[$i.'_allocation'],
                    'shortover_amount'	 		=> $allrequest[$i.'_shortover'],
                    'last_balance' 				=> $allrequest[$i.'_last_balance']
                );

                if($data_paymentitem['allocation_amount'] > 0){
                    if(PurchasePaymentItem::create($data_paymentitem)){

                        $purchaseinvoice = PurchaseInvoice::where('data_state', 0)
                        ->where('purchase_invoice_id', $data_paymentitem['purchase_invoice_id'])
                        ->first();

                        $purchaseinvoice->paid_amount       = $purchaseinvoice['paid_amount'] + $data_paymentitem['allocation_amount'] + $data_paymentitem['shortover_amount'];
                        $purchaseinvoice->owing_amount      = $data_paymentitem['last_balance'];
                        $purchaseinvoice->shortover_amount  = $purchaseinvoice['shortover_amount'] + $data_paymentitem['shortover_amount'];
                        $purchaseinvoice->save();

                        $msg = "Tambah Pelunasan Hutang Berhasil";
                        continue;
                    }else{
                        $msg = "Tambah Pelunasan Hutang Gagal";
                        return redirect('/purchase-payment/add/'.$data['supplier_id'])->with('msg',$msg);
                    }
                }
                
            }


//-----------Hutang Supplier
            $account 		= AcctAccount::where('account_id', 205)
            ->where('data_state', 0)
            ->first();

            $account_id_default_status 		= $account['account_default_status'];

            $data_debit = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> 205,
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($payment_total_amount),
                'journal_voucher_debit_amount'	=> ABS($payment_total_amount),
                'account_id_default_status'		=> $account_id_default_status,
                'account_id_status'				=> 1,
            );

            AcctJournalVoucherItem::create($data_debit);

//-----------Hutang Supplier (sisa hutang jika dibayar > 1 kali)
            if($selisih_shortover > 0){

                $account 		= AcctAccount::where('account_id', 205)
                ->where('data_state', 0)
                ->first();

                $account_id_default_status 		= $account['account_default_status'];

                $data_debit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> 205,
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($selisih_shortover),
                    'journal_voucher_debit_amount'	=> ABS($selisih_shortover),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                );

                AcctJournalVoucherItem::create($data_debit);
            } else if($selisih_shortover < 0){

                $account 		= AcctAccount::where('account_id', 205)
                ->where('data_state', 0)
                ->first();

                $account_id_default_status 		= $account['account_default_status'];

                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> 205,
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($selisih_shortover),
                    'journal_voucher_credit_amount'	=> ABS($selisih_shortover),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                );

                AcctJournalVoucherItem::create($data_credit);
            }

//-----------PPN MASUKAN 
            $account 		= AcctAccount::where('account_id', 105)
            ->where('data_state', 0)
            ->first();
            //dd($account);
            $account_id_default_status 		= $account['account_default_status'];
        
//jika check PPN
        $cekPPN = $request->ppn_in;
        if($cekPPN == 1){
            $data_debit = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> 105,
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($request->ppn_in_amount),
                'journal_voucher_debit_amount'	=> ABS($request->ppn_in_amount),
                'account_id_default_status'		=> $account_id_default_status,
                'account_id_status'				=> 0,
            );
            AcctJournalVoucherItem::create($data_debit);
        }else{
            $data_debit = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> 105,
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS(0),
                'journal_voucher_debit_amount'	=> ABS(0),
                'account_id_default_status'		=> $account_id_default_status,
                'account_id_status'				=> 0,
            );
            AcctJournalVoucherItem::create($data_debit);
        }

//jika check Promosi
        $accountPromotion 		= AcctAccount::where('account_id', 50)
        ->where('data_state', 0)
        ->first();
        //dd($account);
        $account_id_default_status_promotion 		= $accountPromotion['account_default_status'];

        $cekPromosi = $request->promosi;
        if($cekPromosi == 1){
            $data_credit = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> 50,
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($request->promotion_amount),
                'journal_voucher_credit_amount'	=> ABS($request->promotion_amount),
                'account_id_default_status'		=> $account_id_default_status_promotion,
                'account_id_status'				=> 0,
            );
            AcctJournalVoucherItem::create($data_credit);
        }
        
//jika check Biaya kirim
        $accountAdmCost 		= AcctAccount::where('account_id', 58)
        ->where('data_state', 0)
        ->first();
        //dd($account);
        $account_id_default_status_adm_cost 		= $accountAdmCost['account_default_status'];

        $cekAdmCost = $request->adm_cost;
        if($cekAdmCost == 1){
            $data_credit = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> 58,
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($request->adm_cost_amount),
                'journal_voucher_credit_amount'	=> ABS($request->adm_cost_amount),
                'account_id_default_status'		=> $account_id_default_status_adm_cost,
                'account_id_status'				=> 0,
            );
            AcctJournalVoucherItem::create($data_credit);
        }                
//-----------Kas
            if($data['payment_total_cash_amount'] != '' || $data['payment_total_cash_amount'] != ''){

                $account 		= AcctAccount::where('account_id', 5)
                ->where('data_state', 0)
                ->first();
                //dd($account);
                $account_id_default_status 		= $account['account_default_status'];

                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> 5,
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($data['payment_total_cash_amount']),
                    'journal_voucher_credit_amount'	=> ABS($data['payment_total_cash_amount']),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                );
                AcctJournalVoucherItem::create($data_credit);
            }else if(is_array($datapurchasepaymenttransfer) && !empty($datapurchasepaymenttransfer)){
//-----------Bank                
                foreach ($datapurchasepaymenttransfer as $keyTransfer => $valTransfer) {
                    $transfer_bank = CoreBank::where('bank_id', $valTransfer['bank_id'])
                    ->first();

                    $transfer_account_id = $transfer_bank['account_id'];

                    $datatransfer = array(
                        'payment_id'							=> $payment_id,
                        'bank_id'							    => $valTransfer['bank_id'],
                        'account_id'							=> $transfer_account_id,
                        'payment_transfer_bank_name'			=> $transfer_bank['bank_name'],
                        'payment_transfer_amount'				=> $valTransfer['payment_transfer_amount'],
                        'payment_transfer_account_name'			=> $valTransfer['payment_transfer_account_name'],
                        'payment_transfer_account_no'			=> $valTransfer['payment_transfer_account_no'],
                    );

                    if(PurchasePaymentTransfer::create($datatransfer)){

                        $account 		= AcctAccount::where('account_id', $transfer_account_id)
                        ->where('data_state', 0)
                        ->first();
        
                        $account_id_default_status 		= $account['account_default_status'];

                        $data_credit = array (
                            'journal_voucher_id'			=> $journal_voucher_id,
                            'account_id'					=> $transfer_account_id,
                            'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                            'journal_voucher_amount'		=> ABS($datatransfer['payment_transfer_amount']),
                            'journal_voucher_credit_amount'	=> ABS($datatransfer['payment_transfer_amount']),
                            'account_id_default_status'		=> $account_id_default_status,
                            'account_id_status'				=> 0,
                        );

                        AcctJournalVoucherItem::create($data_credit);
                    }
                }
            }
//-----------PPN MASUKAN BELUM DITERIMA
             $account 		= AcctAccount::where('account_id', 106)
                ->where('data_state', 0)
                ->first();
                //dd($account);
                $account_id_default_status 		= $account['account_default_status'];
//jika check PPN
        $cekPPN = $request->ppn_in;
            if($cekPPN == 1){
                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> 106,
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($request->ppn_in_amount),
                    'journal_voucher_credit_amount'	=> ABS($request->ppn_in_amount),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                );
                AcctJournalVoucherItem::create($data_credit);
            }else{
                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> 106,
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS(0),
                    'journal_voucher_credit_amount'	=> ABS(0),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                );
                AcctJournalVoucherItem::create($data_credit);
            }

            $msg = "Tambah Pelunasan Hutang Berhasil";            
            return redirect('/purchase-payment')->with('msg',$msg);
        }else{
            $msg = "Tambah Pelunasan Hutang Gagal";
            return redirect('/purchase-payment')->with('msg',$msg);
        }
    }

    public function processVoidPurchasePayment(Request $request){

        $payment_no 			        = $request->payment_no;
        
        $purchasepayment                = PurchasePayment::findOrFail($request->payment_id);
        $purchasepayment->voided_remark = $request->voided_remark;
        $purchasepayment->voided_on     = date('Y-m-d H:i:s');
        $purchasepayment->voided_id     = Auth::id();
        $purchasepayment->data_state    = 2;
        

        if($purchasepayment->save()){
            $purchasepaymentitem 	= PurchasePaymentItem::where('payment_id', $request->payment_id)->get();

            foreach ($purchasepaymentitem as $ki => $vi){
                $purchaseinvoice = PurchaseInvoice::where('purchase_invoice_id', $vi['purchase_invoice_id'])->first();
                // print_r((float)$purchaseinvoice['paid_amount'] - ((float)$vi['allocation_amount'] + (float)$vi['shortover_amount']));
                // print_r("|||||||||");
                // print_r((float)$purchaseinvoice['owing_amount'] + ((float)$vi['allocation_amount'] + (float)$vi['shortover_amount']));
                // print_r("|||||||||");
                // print_r((float)$purchaseinvoice['shortover_amount'] - (float)$vi['shortover_amount']);exit;
                $purchaseinvoice->paid_amount       = $purchaseinvoice['paid_amount'] - ($vi['allocation_amount'] + $vi['shortover_amount']);
                $purchaseinvoice->owing_amount      = $purchaseinvoice['owing_amount'] + ($vi['allocation_amount'] + $vi['shortover_amount']);
                $purchaseinvoice->shortover_amount  = $purchaseinvoice['shortover_amount'] - $vi['shortover_amount'];

                $purchaseinvoice->save();
            }

            $journalvoucher 	    = AcctJournalVoucher::where('transaction_journal_no', $payment_no)->first();
            $journal_voucher_id 	= $journalvoucher['journal_voucher_id'];

            $acctjournalvoucheritem = AcctJournalVoucherItem::where('journal_voucher_id', $journal_voucher_id)->get();

            $journalvoucher 	            = AcctJournalVoucher::where('journal_voucher_id', $journal_voucher_id)->first();
            $journalvoucher->voided         = 1;
            $journalvoucher->voided_id      = Auth::id();
            $journalvoucher->voided_on      = date('Y-m-d H:i:s');
            $journalvoucher->voided_remark  = $request->voided_remark;
            $journalvoucher->data_state     = 2;

            if ($journalvoucher->save()){
                foreach ($acctjournalvoucheritem as $keyItem => $valItem) {
                    $journalvoucheritem = AcctJournalVoucherItem::where('journal_voucher_item_id', $valItem['journal_voucher_item_id'])->first();
                    $journalvoucheritem->data_state = 2;

                    $journalvoucheritem->save();
                }
            }

            $msg = "Pembatalan Pelunasan Hutang Berhasil";
            return redirect('/purchase-payment')->with('msg',$msg);
        }else{
            $msg = "Pembatalan Pelunasan Hutang Gagal";
            return redirect('/purchase-payment/delete/'.$request->payment_id)->with('msg',$msg);
        }
    }
    
    public function deleteTransferArray($record_id, $supplier_id)
    {
        $arrayBaru			= array();
        $dataArrayHeader	= Session::get('datapurchasepaymenttransfer');
        
        foreach($dataArrayHeader as $key=>$val){
            if($key != $record_id){
                $arrayBaru[$key] = $val;
            }
        }
        Session::forget('datapurchasepaymenttransfer');
        Session::put('datapurchasepaymenttransfer', $arrayBaru);

        return redirect('/purchase-payment/add/'.$supplier_id);
    }


    public function getTotalPpn($supplier_id){
        $ppn = PurchaseInvoice::where('data_state', 0)
        ->where('supplier_id', $supplier_id)
        ->sum('ppn_in_amount');
    
        return $ppn;
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

        return $account['account_name'];
    }
    public function getAccountBank($supplier_id){
        $account = CoreSupplier::where('data_state', 0)
        ->where('supplier_id', $supplier_id)
        ->first();

        return $account['supplier_bank_acct_name'];
    }

    public function getAccountNo($supplier_id){
        $account = CoreSupplier::where('data_state', 0)
        ->where('supplier_id', $supplier_id)
        ->first();

        return $account['supplier_bank_acct_no'];
    }

    public function getCoreBankName($bank_id){
        $bank = CoreBank::where('data_state', 0)
        ->where('bank_id', $bank_id)
        ->first();

        if($bank){

            return $bank['bank_name'];
        }else{
            return '';
        }
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
}
