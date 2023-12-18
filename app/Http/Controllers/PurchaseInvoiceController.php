<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\AcctJournalVoucher;
use App\Models\CoreSupplier;
use App\Models\InvGoodsReceivedNote;
use App\Models\InvGoodsReceivedNoteItem;
use App\Models\InvItemCategory;
use App\Models\InvItemType;
use App\Models\InvItemUnit;
use App\Models\InvWarehouse;
use App\Models\PreferenceTransactionModule;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PreferenceCompany;
use App\Models\User;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucherItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PurchaseInvoiceController extends Controller
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

        $supplier_id = Session::get('supplier_id');

        Session::forget('purchaseinvoiceitem');
        Session::forget('purchaseinvoiceelements');

        $purchaseinvoice = PurchaseInvoice::join('purchase_order', 'purchase_order.purchase_order_id', 'purchase_invoice.purchase_order_id')
        ->where('purchase_invoice.data_state','=',0)
        ->where('purchase_invoice.purchase_invoice_date', '>=', $start_date)
        ->where('purchase_invoice.purchase_invoice_date', '<=', $end_date);
        if($supplier_id||$supplier_id!=null||$supplier_id!=''){
            $purchaseinvoice   = $purchaseinvoice->where('supplier_id', $supplier_id);
        }
        $purchaseinvoice       = $purchaseinvoice->get();

        $supplier = CoreSupplier::select('supplier_id', 'supplier_name')
        ->where('data_state', 0)
        ->pluck('supplier_name', 'supplier_id');

        return view('content/PurchaseInvoice/ListPurchaseInvoice',compact('purchaseinvoice', 'start_date', 'end_date', 'supplier_id', 'supplier'));
    }
    
    public function filterPurchaseInvoice(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $supplier_id       = $request->supplier_id;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('supplier_id', $supplier_id);

        return redirect('/purchase-invoice');
    }

    public function resetFilterPurchaseInvoice(){
        Session::forget('start_date');
        Session::forget('end_date');
        Session::forget('supplier_id');

        return redirect('/purchase-invoice');

    }

    public function search()
    {
        $purchaseorder = PurchaseOrder::where('data_state', 0)
        ->where('approved', 1)
        ->where('purchase_invoice_status', 0)
        ->get();


        return view('content/PurchaseInvoice/SearchPurchaseOrder',compact('purchaseorder'));
    }

    public function searchGoodsReceivedNote()
    {
        $goodsreceivednote = InvGoodsReceivedNote::where('inv_goods_received_note.data_state', 0)
        ->join('purchase_order', 'purchase_order.purchase_order_id', 'inv_goods_received_note.purchase_order_id')
        ->where('inv_goods_received_note.goods_received_note_status_invoice', 0)
        // ->where('purchase_order.purchase_order_type_id', 2)
        ->get();


        return view('content/PurchaseInvoice/SearchGoodsReceivedNote',compact('goodsreceivednote'));
    }

    public function addPurchaseInvoicePurchaseOrder($purchase_order_id)
    {
        $purchaseorder = PurchaseOrder::findOrFail($purchase_order_id);
        
        $purchaseorderitem = PurchaseOrderItem::select('purchase_order_item.*')
        ->where('data_state', 0)
        ->where('purchase_order_id', $purchase_order_id)
        ->get();

        $invgoodsreceivednote = InvGoodsReceivedNote::select('inv_goods_received_note.*')
        ->where('data_state', 0)
        ->where('purchase_order_id', $purchase_order_id)
        ->first();

        // print_r($invgoodsreceivednote);exit;

        return view('content/PurchaseInvoice/FormAddPurchaseInvoice',compact('purchaseorder', 'purchaseorderitem', 'invgoodsreceivednote', 'purchase_order_id'));
    }

    public function addPurchaseInvoice($goods_received_note_id)
    {
        $goodsreceivednote = InvGoodsReceivedNote::findOrFail($goods_received_note_id);
        
        $goodsreceivednoteitem = InvGoodsReceivedNoteItem::select('inv_goods_received_note_item.*')
        ->where('data_state', 0)
        ->where('goods_received_note_id', $goods_received_note_id)
        ->get();

        $purchaseorder = PurchaseOrder::where('purchase_order_id', $goodsreceivednote['purchase_order_id'])
        ->where('data_state', 0)
        ->first();

        // print_r($invgoodsreceivednote);exit;

        return view('content/PurchaseInvoice/FormAddPurchaseInvoice',compact('goodsreceivednote', 'goodsreceivednoteitem', 'goods_received_note_id', 'purchaseorder'));
    }

    public function editPurchaseInvoice($purchase_invoice_id)
    {
        $purchaseinvoice = PurchaseInvoice::findOrFail($purchase_invoice_id);

        $purchaseorder = PurchaseOrder::where('purchase_order_id', $purchaseinvoice['purchase_order_id'])
        ->where('data_state', 0)
        ->first();
        
        $purchaseinvoiceitem = PurchaseInvoiceItem::select('purchase_invoice_item.*')
        ->where('data_state', 0)
        ->where('purchase_invoice_id', $purchase_invoice_id)
        ->get();

        $invgoodsreceivednote = InvGoodsReceivedNote::select('inv_goods_received_note.*')
        ->where('data_state', 0)
        ->where('purchase_order_id', $purchaseinvoice['purchase_order_id'])
        ->first();

        return view('content/PurchaseInvoice/FormEditPurchaseInvoice',compact('purchaseinvoice', 'purchaseinvoiceitem', 'invgoodsreceivednote', 'purchaseorder', 'purchase_invoice_id'));
    }

    public function detailPurchaseInvoice($purchase_invoice_id)
    {
        $purchaseinvoice = PurchaseInvoice::findOrFail($purchase_invoice_id);

        $purchaseorder = PurchaseOrder::where('purchase_order_id', $purchaseinvoice['purchase_order_id'])
        ->where('data_state', 0)
        ->first();
        
        $purchaseinvoiceitem = PurchaseInvoiceItem::select('purchase_invoice_item.*')
        ->where('data_state', 0)
        ->where('purchase_invoice_id', $purchase_invoice_id)
        ->get();

        $invgoodsreceivednote = InvGoodsReceivedNote::select('inv_goods_received_note.*')
        ->where('data_state', 0)
        ->where('purchase_order_id', $purchaseinvoice['purchase_order_id'])
        ->first();

        return view('content/PurchaseInvoice/FormDetailPurchaseInvoice',compact('purchaseinvoice', 'purchaseinvoiceitem', 'invgoodsreceivednote', 'purchaseorder', 'purchase_invoice_id'));
    }

    public function voidPurchaseInvoice($purchase_invoice_id)
    {
        $purchaseinvoice = PurchaseInvoice::findOrFail($purchase_invoice_id);

        $purchaseorder = PurchaseOrder::where('purchase_order_id', $purchaseinvoice['purchase_order_id'])
        ->where('data_state', 0)
        ->first();
        
        $purchaseinvoiceitem = PurchaseInvoiceItem::select('purchase_invoice_item.*')
        ->where('data_state', 0)
        ->where('purchase_invoice_id', $purchase_invoice_id)
        ->get();

        $invgoodsreceivednote = InvGoodsReceivedNote::select('inv_goods_received_note.*')
        ->where('data_state', 0)
        ->where('purchase_order_id', $purchaseinvoice['purchase_order_id'])
        ->first();

        return view('content/PurchaseInvoice/FormVoidPurchaseInvoice',compact('purchaseinvoice', 'purchaseinvoiceitem', 'invgoodsreceivednote', 'purchaseorder', 'purchase_invoice_id'));
    }

    public function processAddPurchaseInvoice(Request $request){
        $fields = $request->validate([
            'purchase_invoice_no' => 'required',
            'purchase_invoice_date' => 'required',
            'purchase_invoice_due_date' => 'required',
        ]);

        $purchaseorderitem = PurchaseOrderItem::select('purchase_order_item.*')
        ->where('data_state', 0)
        ->where('purchase_order_id', $request->purchase_order_id)
        ->get();

        $goodsreceivednoteitem = InvGoodsReceivedNoteItem::select('inv_goods_received_note_item.*')
        ->where('data_state', 0)
        ->where('goods_received_note_id', $request->goods_received_note_id)
        ->get();

        $purchaseinvoice = array(
            'purchase_order_id'	        => $request->purchase_order_id,
            'purchase_invoice_no'	    => $request->purchase_invoice_no,
            'goods_received_note_id'	=> $request->goods_received_note_id,
            'purchase_invoice_date'	    => $request->purchase_invoice_date,
            'purchase_invoice_remark'	=> $request->purchase_invoice_remark,
            'subtotal_item'	            => $request->total_item,
            'subtotal_amount'	        => $request->total_amount + $request->ppn_in_amount,
            'total_amount'	            => $request->total_amount + $request->ppn_in_amount,
            'owing_amount'	            => $request->total_amount + $request->ppn_in_amount,
            'ppn_in_amount'             => $request->ppn_in_amount,
            'supplier_id'	            => $request->supplier_id,
            'warehouse_id'	            => $request->warehouse_id,
            'purchase_invoice_due_date' => $request->purchase_invoice_due_date,
            'branch_id'	                => Auth::user()->branch_id,
            'created_id'                => Auth::id(),
        );
        //dd($purchaseinvoice);

        if(PurchaseInvoice::create($purchaseinvoice)){
            $goodsreceivednote = InvGoodsReceivedNote::findOrFail($request->goods_received_note_id);
            $goodsreceivednote->goods_received_note_status_invoice = 1;
            $goodsreceivednote->save();

            $purchase_invoice_id = PurchaseInvoice::select('*')
            ->orderBy('created_at', 'DESC')
            ->first();
            $no = 0;
            foreach($goodsreceivednoteitem as $val){
                $purchaseorderitem = PurchaseOrderItem::where('purchase_order_item_id', $val['purchase_order_item_id'])
                ->first();

                $purchaseinvoiceitem = array(
                    'purchase_invoice_id'           => $purchase_invoice_id['purchase_invoice_id'],
                    'goods_received_note_item_id'   => $val['goods_received_note_item_id'],
                    'item_id'                       => $val['item_id'],
                    'item_category_id'              => $val['item_category_id'],
                    'item_type_id'                  => $val['item_type_id'],
                    'item_unit_id'                  => $val['item_unit_id'],
                    'item_unit_cost'                => $purchaseorderitem['item_unit_cost'],
                    'quantity'                      => $val['quantity'],
                    'subtotal_amount'               => $val['quantity']*$purchaseorderitem['item_unit_cost'],
                    'created_id'                    => Auth::id(),
                );

                PurchaseInvoiceItem::create($purchaseinvoiceitem);

                $no++;
            }


//----------------------------------------------------------Journal Voucher-------------------------------------------------------------------//
            
// $preferencecompany 			= PreferenceCompany::first();
        
// $transaction_module_code 	= "PI";

// $transactionmodule 		    = PreferenceTransactionModule::where('transaction_module_code', $transaction_module_code)
// ->first();

// $transaction_module_id 		= $transactionmodule['transaction_module_id'];

// $journal_voucher_period 	= date("Ym", strtotime($purchase_invoice_id['purchase_invoice_date']));

// $data_journal = array(
//     'branch_id'						=> 1,
//     'journal_voucher_period' 		=> $journal_voucher_period,
//     'journal_voucher_date'			=> $purchase_invoice_id['purchase_invoice_date'],
//     'journal_voucher_title'			=> 'Invoice Pembelian '.$purchase_invoice_id['purchase_invoice_no'],
//     'journal_voucher_no'			=> $purchase_invoice_id['purchase_invoice_no'],
//     'journal_voucher_description'	=> $purchase_invoice_id['purchase_invoice_remark'],
//     'transaction_module_id'			=> $transaction_module_id,
//     'transaction_module_code'		=> $transaction_module_code,
//     'transaction_journal_id' 		=> $purchase_invoice_id['purchase_invoice_id'],
//     'transaction_journal_no' 		=> $purchase_invoice_id['purchase_invoice_no'],
//     'created_id' 					=> Auth::id(),
// );

// AcctJournalVoucher::create($data_journal);
//---------------------------------------------------------End Journal Voucher----------------------------------------------------------------//


//----------------------------------------------------------Journal Voucher Item-------------------------------------------------------------------//


$total_amount               = $request->total_amount;

// $journalvoucher = AcctJournalVoucher::where('created_id', Auth::id())
// ->orderBy('journal_voucher_id', 'DESC')
// ->first();


// $journal_voucher_id 	= $journalvoucher['journal_voucher_id'];


// //------account_id Persediaan Barang Dagang------//
// $preference_company = PreferenceCompany::first();

// $account = AcctAccount::where('account_id', $preference_company['account_inventory_trade_id'])
// ->where('data_state', 0)
// ->first();

// $account_id_default_status 		= $account['account_default_status'];


// $data_debit1 = array (
//     'journal_voucher_id'			=> $journal_voucher_id,
//     'account_id'					=> $account['account_id'],
//     'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
//     'journal_voucher_amount'		=> ABS($total_amount),
//     'journal_voucher_debit_amount'	=> ABS($total_amount),
//     'account_id_default_status'		=> $account_id_default_status,
//     'account_id_status'				=> 1,
// );

// // dd($data_debit1);

// AcctJournalVoucherItem::create($data_debit1);

// //------account_id PPN Masukan------//
// $account = AcctAccount::where('account_id', $preference_company['account_vat_in_id'])
// ->where('data_state', 0)
// ->first();

// $ppn_in_amount = $request->ppn_in_amount;

// $account_id_default_status 		= $account['account_default_status'];



// $data_debit2 = array (
//     'journal_voucher_id'			=> $journal_voucher_id,
//     'account_id'					=> $account['account_id'],
//     'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
//     'journal_voucher_amount'		=> ABS((int)$ppn_in_amount),
//     'journal_voucher_debit_amount'	=> ABS((int)$ppn_in_amount),
//     'account_id_default_status'		=> $account_id_default_status,
//     'account_id_status'				=> 1,
// );

// // dd($data_debit2);

// AcctJournalVoucherItem::create($data_debit2);


// $account 		= AcctAccount::where('account_id', $preferencecompany['account_payable_id'])
// ->where('data_state', 0)
// ->first();

// $subtotal_after_ppn_in = $request->subtotal_after_ppn_in;

// // dd($account);

// $account_id_default_status 		= $account['account_default_status'];

// $data_credit = array (
//     'journal_voucher_id'			=> $journal_voucher_id,
//     'account_id'					=> $preferencecompany['account_payable_id'],
//     'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
//     'journal_voucher_amount'		=> ABS((int)$subtotal_after_ppn_in),
//     'journal_voucher_credit_amount'	=> ABS((int)$subtotal_after_ppn_in),
//     'account_id_default_status'		=> $account_id_default_status,
//     'account_id_status'				=> 0,
// );
// // dd($data_credit);


// AcctJournalVoucherItem::create($data_credit);


//--------------------------------------------------------End Journal Voucher-----------------------------------------------------------------//

            $msg = 'Tambah Purchase Invoice Berhasil';
            return redirect('/purchase-invoice')->with('msg',$msg);
        }else{
            $msg = 'Tambah Purchase Invoice Gagal';
            return redirect('/purchase-invoice/add/'.$fields['purchase_order_id'])->with('msg',$msg);
        }
    }

    public function processEditPurchaseInvoice(Request $request){
        $fields = $request->validate([
            'purchase_invoice_no'   => 'required',
            'purchase_invoice_id'   => 'required',
            'purchase_invoice_date' => 'required',
        ]);
        
        $purchaseinvoice = PurchaseInvoice::findOrFail($request->purchase_invoice_id);
        $purchaseinvoice->purchase_invoice_no = $request->purchase_invoice_no;
        $purchaseinvoice->purchase_invoice_date = $request->purchase_invoice_date;
        $purchaseinvoice->purchase_invoice_due_date = $request->purchase_invoice_due_date;
        $purchaseinvoice->faktur_tax_no = $request->faktur_tax_no;
        $purchaseinvoice->purchase_invoice_remark = $request->purchase_invoice_remark;

        if($purchaseinvoice->save()){
            $msg = 'Edit Purchase Invoice Berhasil';
            return redirect('/purchase-invoice')->with('msg',$msg);
        }else{
            $msg = 'Edit Purchase Invoice Gagal';
            return redirect('/purchase-invoice/add/'.$fields['purchase_order_id'])->with('msg',$msg);
        }
    }

    public function processVoidPurchaseInvoice(Request $request){
        $fields = $request->validate([
            'purchase_invoice_id'   => 'required',
        ]);
        
        $purchaseinvoice = PurchaseInvoice::findOrFail($request->purchase_invoice_id);
        $purchaseinvoice->voided_id     = Auth::id();
        $purchaseinvoice->voided_on     = date('Y-m-d');
        $purchaseinvoice->data_state    = 1;

        if($purchaseinvoice->save()){
            $goodsreceivednote = InvGoodsReceivedNote::where('goods_received_note_id', $purchaseinvoice['goods_received_note_id'])
            ->first();

            $goodsreceivednote->goods_received_note_status_invoice = 0;
            $goodsreceivednote->save();

            $purchaseinvoiceitem = PurchaseInvoiceItem::where('purchase_invoice_id', $request->purchase_invoice_id)->get();
            foreach($purchaseinvoiceitem as $val){
                $dataitem = PurchaseInvoiceItem::where('purchase_invoice_item_id', $val['purchase_invoice_item_id'])->first();
                $dataitem->data_state = 1;
                $dataitem->save();
            }

            $msg = 'Void Purchase Invoice Berhasil';
            return redirect('/purchase-invoice')->with('msg',$msg);
        }else{
            $msg = 'Void Purchase Invoice Gagal';
            return redirect('/purchase-invoice/add/'.$fields['purchase_order_id'])->with('msg',$msg);
        }
    }

    public function getSupplierName($supplier_id){
        $supplier = CoreSupplier::select('supplier_name')
        ->where('data_state', 0)
        ->where('supplier_id', $supplier_id)
        ->first();

        return $supplier['supplier_name'];
    }

    public function getPurchaseOrderItem($goods_received_note_item_id){
        $goodsreceivednoteitem = InvGoodsReceivedNoteItem::select('purchase_order_item.*')
        ->join('purchase_order_item', 'purchase_order_item.purchase_order_item_id', 'inv_goods_received_note_item.purchase_order_item_id')
        ->where('inv_goods_received_note_item.goods_received_note_item_id', $goods_received_note_item_id)
        ->first();

        return $goodsreceivednoteitem;
    }

    public function getWarehouseName($warehouse_id){
        $warehouse = InvWarehouse::select('warehouse_name')
        ->where('data_state', 0)
        ->where('warehouse_id', $warehouse_id)
        ->first();

        return $warehouse['warehouse_name'];
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

        return $item['item_unit_name'];
    }

}
