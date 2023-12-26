<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Providers\RouteServiceProvider;
use App\Models\CoreCustomer;
use App\Models\CoreExpedition;
use App\Models\InvGoodsReceivedNote;
use App\Models\InvItem;
use App\Models\InvItemCategory;
use App\Models\InvItemType;
use App\Models\InvItemUnit;
use App\Models\InvItemStock;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\SalesCollection;
use App\Models\SalesCollectionDiscount;
use App\Models\SalesCollectionItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesCollectionPiece;
use App\Models\SalesCollectionTransfer;
use App\Models\SalesDeliveryNote;
use App\Models\SalesDeliveryNoteItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SystemLogUser;
use App\Models\SalesDeliveryNoteItemStock;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PhpParser\Node\Expr\Cast\Object_;
use stdClass;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalesCollectionDiscountController extends Controller
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

        $customer_id = Session::get('customer_id');
        $sales_collection_piece_type_id = Session::get('sales_collection_piece_type_id');

        Session::forget('salesinvoiceitem');
        Session::forget('salesinvoiceelements');

        $salescolectionpiece = SalesCollectionDiscount::select('sales_collection_discount.*','sales_invoice.sales_invoice_date')
        ->join('sales_invoice', 'sales_invoice.sales_invoice_id', 'sales_collection_discount.sales_invoice_id')
        ->where('sales_collection_discount.data_state','=',0)
        ->where('sales_collection_discount.claim_status', 1)
        ->where('sales_collection_discount.claim_date', '>=', $start_date)
        ->where('sales_collection_discount.claim_date', '<=', $end_date);
        if($customer_id||$customer_id!=null||$customer_id!=''){
            $salescolectionpiece   = $salescolectionpiece->where('sales_collection_piece.customer_id', $customer_id);
        }

        if($sales_collection_piece_type_id||$sales_collection_piece_type_id!=null||$sales_collection_piece_type_id!=''){
            $salescolectionpiece   = $salescolectionpiece->where('sales_collection_piece.sales_collection_piece_type_id', $sales_collection_piece_type_id);
        }

        $salescolectionpiece       = $salescolectionpiece->get();

        $customer = CoreCustomer::select('customer_id', 'customer_name')
        ->where('data_state', 0)
        ->pluck('customer_name', 'customer_id');
        $sales_collection_piece_type = array(
            '1' => 'promosi',
            '2' => 'biasa'
        );
        return view('content/SalesCollectionDiscount/ListSalesCollectionDiscount',compact('sales_collection_piece_type_id','sales_collection_piece_type','salescolectionpiece', 'start_date', 'end_date', 'customer_id', 'customer'));
    }
    
    public function filterSalesCollectionPiece(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $customer_id    = $request->customer_id;
        $sales_collection_piece_type_id    = $request->sales_collection_piece_type_id;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('customer_id', $customer_id);
        Session::put('sales_collection_piece_type_id', $sales_collection_piece_type_id);

        return redirect('/sales-collection-piece');
    }

    public function resetFilterSalesCollectionPiece(){
        Session::forget('start_date');
        Session::forget('end_date');
        Session::forget('customer_id');
        Session::forget('sales_collection_piece_type_id');

        return redirect('/sales-collection-piece');

    }

   
    public function searchCoreCustomer(){

        Session::forget('salescollectionelements');
        Session::forget('datasalescollectiontransfer');

        $corecustomer = SalesInvoice::select('sales_invoice.customer_id', 'core_customer.customer_name', 'core_customer.customer_address', DB::raw("SUM(sales_invoice.owing_discount_amount) as total_owing_amount"))
        ->join('core_customer', 'core_customer.customer_id', 'sales_invoice.customer_id')
        ->where('sales_invoice.data_state', 0)
        ->where('core_customer.data_state', 0)
        ->groupBy('sales_invoice.customer_id')
        ->orderBy('core_customer.customer_name', 'ASC')
        ->get();
        
        return view('content/SalesCollectionDiscount/searchCoreCustomer',compact('corecustomer'));
    }


    public function getPiece($sales_invoice_id){
        $salescollectionpiece = SalesCollectionPiece::select('*')
        ->where('sales_invoice_id', $sales_invoice_id)
        ->where('data_state', 0)
        ->get();  
       // dd($salescollectionpiece);
        return ($salescollectionpiece);
    }

    public function addSalesCollectionDiscount($customer_id){

        $salesinvoiceowing = SalesInvoice::select('sales_invoice.sales_invoice_id', 'sales_invoice.customer_id', 'sales_invoice.owing_amount', 'sales_invoice.sales_invoice_date', 'sales_invoice.paid_amount','sales_invoice.buyers_acknowledgment_no', 'sales_invoice.sales_invoice_no', 'sales_invoice.subtotal_amount', 'sales_invoice.discount_percentage', 'sales_invoice.discount_amount', 'sales_invoice.total_amount', 'sales_invoice.goods_received_note_no','sales_invoice.total_discount_amount', 'sales_invoice.paid_discount_amount','sales_invoice.owing_discount_amount')
        ->where('sales_invoice.customer_id', $customer_id)
        ->where('sales_invoice.owing_discount_amount', '>', 0)
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
        
        return view('content.SalesCollectionDiscount.FormAddSalesCollectionDiscount',compact('select','customer_id', 'salesinvoiceowing', 'customer', 'acctaccount', 'salescollectionelements', 'salescollectiontransfer'));
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
                    'cash_account_id'				    => 8,
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
        $transaction_module_code 	= "SCD";

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

                $account = AcctAccount::where('account_id', $data['cash_account_id'])
                ->where('data_state', 0)
                ->first();

                $account_id_default_status = $account['account_default_status'];

                $data_debet = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $data['cash_account_id'],
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

            }
        }



    public function getItemStock($sales_delivery_note_item_id){
        $item = SalesDeliveryNoteItemStock::select('item_stock_id')
        ->where('data_state', 0)
        ->where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
        ->first();

        return $item['item_stock_id'];
    }


    public function getCustomerName($customer_id){
        $customer = CoreCustomer::select('customer_name')
        ->where('data_state', 0)
        ->where('customer_id', $customer_id)
        ->first();

        return $customer['customer_name']??'';
    }

    public function getCustomerNameSalesOrderId($sales_order_id){
        $customer = SalesOrder::select('core_customer.customer_name')
        ->join('core_customer', 'core_customer.customer_id', 'sales_order.customer_id')
        ->where('sales_order.data_state', 0)
        ->where('sales_order.sales_order_id', $sales_order_id)
        ->first();

        if($customer == null){
            return "-";
        }

        return $customer['customer_name'];
    }

    public function getExpeditionName($expedition_id){
        $expedition = CoreExpedition::select('expedition_name')
        ->where('data_state', 0)
        ->where('expedition_id', $expedition_id)
        ->first();

        return $expedition['expedition_name'];
    }

    public function getSalesDeliveryNoteItem($sales_delivery_note_item_id){
        $salesdeliverynoteitem = SalesDeliveryNoteItem::where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
        ->first();

        return $salesdeliverynoteitem;
    }

    public function getItemCategoryName($item_category_id){
        $item = InvItemCategory::select('item_category_name')
        ->where('data_state', 0)
        ->where('item_category_id', $item_category_id)
        ->first();

        return $item['item_category_name'];
    }

    public function getItemTypeName($item_type_id){
        $item = InvItemType::select('item_type_name')
        ->where('data_state', 0)
        ->where('item_type_id', $item_type_id)
        ->first();

        return $item['item_type_name'];
    }

    public function getItemUnitName($item_unit_id){
        $item = InvItemUnit::select('item_unit_name')
        ->where('data_state', 0)
        ->where('item_unit_id', $item_unit_id)
        ->first();

        if($item == null){
            return '-';
        }

        return $item['item_unit_name'];
    }

    public function getItemName($item_type_id){
        $invitem = InvItemType::select('inv_item_type.item_type_id', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name) AS item_name'))
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->where('item_type_id', $item_type_id)
        ->where('inv_item_type.data_state','=',0)
        ->first();

        //dd($invitem);
        if($invitem == null){
            return '-';
        }


        return $invitem['item_name'];
    }


}
