<?php

namespace App\Http\Controllers;

use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\InvItemStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\SalesOrderReturn;
use App\Models\SalesDeliveryNote;
use App\Models\SalesDeliveryOrder;
use App\Models\SalesDeliveryOrderItem;
use App\Models\SalesOrder;
use App\Models\InvWarehouse;
use App\Models\SalesDeliveryNoteItem;
use App\Models\SalesOrderReturnItem;
use App\Models\SalesOrderItem;
use App\Models\CoreExpedition;
use App\Models\InvItemType;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\PurchaseOrderItem;
use App\Models\ReturnPDP_LostOnExpedition;
use App\Models\ReturnPDP_LostOnExpedition_Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReturnPDP_LostOnExpedition_Controller extends Controller
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

    public function index(){

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

        $returnpdp_lostonexpedition = ReturnPDP_LostOnExpedition::where('data_state', 0)
        ->where('return_pdp_lost_on_expedition_date', '>=', $start_date)
        ->where('return_pdp_lost_on_expedition_date', '<=', $end_date)
        ->get();
        return  view('content/CoreExpedition/ReturnPDP_LostOnExpedition/ListReturnPDP_LostOnExpedition',compact('returnpdp_lostonexpedition', 'end_date', 'start_date'));
    }

    public function filterReturnPDP_LostOnExpedition(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/return-pdp-lost-on-expedition');
    }

    public function resetFilterReturnPDP_LostOnExpedition()
    {
        Session::forget('start_date');
        Session::forget('end_date');

        return redirect('/return-pdp-lost-on-expedition');
    }

    public function searchSalesDeliveryNote()
    {
        Session::forget('purchaseorderitem');

        $salesdeliverynote= SalesDeliveryNote::where('data_state', 0)
        ->where('return_status', 0)
        ->get();

        return view('content/CoreExpedition/ReturnPDP_LostOnExpedition/SearchSalesDeliveryNote', compact('salesdeliverynote'));
    }

    public function addReturnPDP_LostOnExpedition($sales_delivery_note_id){

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        $salesdeliverynote = SalesDeliveryNote::where('data_state', 0)
        ->where('sales_delivery_note_id', $sales_delivery_note_id)
        ->first();

        $acctaccount = AcctAccount::where('acct_account.data_state','=','0')
        ->where('parent_account_status','=',0)
        ->select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS account_code'))
        ->pluck('account_code', 'account_id');

        $salesdeliverynoteitem = SalesDeliveryNoteItem::where('data_state', 0)
        ->where('sales_delivery_note_id', $sales_delivery_note_id)
        ->get();

        $salesorder = SalesOrder::select('sales_order.*')
        ->where('data_state', 0)
        ->first();

        return view('content/CoreExpedition/ReturnPDP_LostOnExpedition/FormAddReturnPDP_LostOnExpedition',compact('warehouse', 'salesdeliverynote', 'sales_delivery_note_id', 'salesdeliverynoteitem', 'salesorder', 'acctaccount'));
    }

    public function getCustomerName($sales_delivery_order_id){
        $salesdeliveryorder = SalesDeliveryOrderItem::select('core_customer.customer_name')
        ->join('core_customer', 'core_customer.customer_id', 'sales_delivery_order_item.customer_id')
        ->where('sales_delivery_order_item.sales_delivery_order_id', $sales_delivery_order_id)
        ->where('sales_delivery_order_item.data_state', 0)
        ->first();

        if($salesdeliveryorder == null){
            return "-";
        }

        return $salesdeliveryorder['customer_name'];
    }

    public function getItemStockName($item_stock_id){
        $invitemstock = InvItemStock::select('inv_item_stock.item_stock_id', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name, " - ", inv_item_stock.item_batch_number) AS item_name'))
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_stock.item_category_id')
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
        ->where('item_stock_id', $item_stock_id)
        ->where('inv_item_stock.data_state','=',0)
        ->first();

        if( $invitemstock == null){
            return "-";
        }
        return $invitemstock['item_name'];
    }

    public function getSalesOrderNo($sales_order_id){
        $salesorder = SalesOrder::select('sales_order_no')
        ->where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->first();

        if($salesorder == null){
            return "-";
        }

        return $salesorder['sales_order_no'];
    }

    public function getSalesOrderDate($sales_order_id){
        $salesorder = SalesOrder::select('sales_order_date')
        ->where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->first();

        if($salesorder == null){
            return "-";
        }

        return $salesorder['sales_order_date'];
    }

    public function getSalesDeliveryOrderDate($sales_delivery_order_id){
        $salesdeliveryorder = SalesDeliveryOrder::select('sales_delivery_order_date')
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('data_state', 0)
        ->first();

        if($salesdeliveryorder == null){
            return "-";
        }

        return $salesdeliveryorder['sales_delivery_order_date'];
    }

    public function getCustomerNameSalesOrderId($sales_order_id){
        $unit = SalesOrder::select('core_customer.customer_name')
        ->join('core_customer', 'core_customer.customer_id', 'sales_order.customer_id')
        ->where('sales_order_id', $sales_order_id)
        ->where('sales_order.data_state', 0)
        ->first();

        if($unit == null){
            return "-";
        }

        return $unit['customer_name'];
    }

    public function getWarehouseName($warehouse_id){
        $warehouse = InvWarehouse::where('warehouse_id', $warehouse_id)
        ->where('data_state', 0)
        ->first();

        if($warehouse == null){
            return "-";
        }

        return $warehouse['warehouse_name'];
    }

    public function getSalesDeliveryNoteNo($sales_delivery_note_id){
        $deliverynote = SalesDeliveryNote::where('sales_delivery_note_id', $sales_delivery_note_id)
        ->where('data_state', 0)
        ->first();

        if($deliverynote == null){
            return "-";
        }


        return $deliverynote['sales_delivery_note_no'];
    }

    public function getSalesDeliveryNoteDate($sales_delivery_note_id){
        $deliverynote = SalesDeliveryNote::where('sales_delivery_note_id', $sales_delivery_note_id)
        ->where('data_state', 0)
        ->first();

        if($deliverynote == null){
            return "-";
        }

        return $deliverynote['sales_delivery_note_date'];
    }

    public function getInvItemTypeName($item_type_id){
        $item = InvItemType::select('inv_item_type.item_type_id', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name) AS item_name'))
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->where('item_type_id', $item_type_id)
        ->where('inv_item_type.data_state', 0)
        ->first();

        if( $item == null){
            return "-";
        }

        return $item['item_name'];
    }

    public function getSalesOrderItem($sales_order_item_id){
        $orderitem = SalesOrderItem::select('sales_order_item.*','sales_order.customer_id','sales_order.sales_order_no','sales_order.sales_order_date', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name, " - ", inv_item_stock.item_batch_number) AS item_name'))
        ->where('sales_order_item_id', $sales_order_item_id)
        ->join('inv_item_stock', 'inv_item_stock.item_stock_id', 'sales_order_item.item_stock_id')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_stock.item_category_id')
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
        // ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
        ->join('sales_order', 'sales_order.sales_order_id', 'sales_order_item.sales_order_id')
        ->first();

        // dd($orderitem);

        return $orderitem;
    }

    public function processAddReturnPDP_LostOnExpedition(Request $request){
        $request->validate([
            // 'warehouse_id'                              => 'required',
            'account_id'                                => 'required',
            'return_pdp_lost_on_expedition_date'        => 'required',
        ]);
        $returnpdp = array(
            // 'warehouse_id'                              => $request->warehouse_id,
            'sales_order_id'                            => $request->sales_order_id_1,
            'account_id'                                => $request->account_id,
            'sales_delivery_order_id'                   => $request->sales_delivery_order_id,
            'sales_delivery_note_id'                    => $request->sales_delivery_note_id,
            'return_pdp_lost_on_expedition_date'        => $request->return_pdp_lost_on_expedition_date,
            'return_pdp_lost_on_expedition_remark'      => $request->return_pdp_lost_on_expedition_remark,
            // 'branch_id'                              => Auth::user()->branch_id,
            'created_id'                                => Auth::id(),
        );

        if(ReturnPDP_LostOnExpedition::create($returnpdp)){
            $return_pdp_lost_on_expedition_id = ReturnPDP_LostOnExpedition::select('return_pdp_lost_on_expedition_id')
            ->orderBy('created_at', 'DESC')
            ->first();

            $salesorderitem = SalesOrderItem::where('sales_order_id',$request->sales_order_id_1)
            ->get();
            
            $no =1;

            $dataitem = $request->all();
            foreach($salesorderitem as $item){
                $item = ReturnPDP_LostOnExpedition_Item::create([
                    'return_pdp_lost_on_expedition_id'	=> $return_pdp_lost_on_expedition_id['return_pdp_lost_on_expedition_id'],
                    'sales_delivery_order_id'	    => $return_pdp_lost_on_expedition_id['sales_delivery_order_id'],
                    'sales_delivery_note_id' 	    => $dataitem['sales_delivery_note_id_'.$no],
                    'sales_delivery_note_item_id'   => $dataitem['sales_delivery_note_item_id_'.$no],
                    'sales_order_id' 			    => $dataitem['sales_order_id_'.$no],
                    'sales_order_item_id' 		    => $dataitem['sales_order_item_id_'.$no],
                    'customer_id' 				    => $dataitem['customer_id_'.$no],
                    'item_type_id' 		             => $dataitem['item_type_id_'.$no],
                    'item_stock_id' 		        => $dataitem['item_stock_id_'.$no],
                    'item_unit_id' 		            => $dataitem['item_unit_id_'.$no],
                    'item_unit_price' 		        => $dataitem['item_unit_price_'.$no],
                    'subtotal_price' 		        => $dataitem['quantity_'.$no]*$dataitem['item_unit_price_'.$no],
                    'quantity'					    => $dataitem['quantity_'.$no],		
                    // 'quantity_return'		        => $dataitem['quantity_'.$no],	
                    'created_id'                    => Auth::id(),
                ]);

                $salesdeliverynote = SalesDeliveryNote::findOrFail($dataitem['sales_delivery_note_id']);
                $salesdeliverynote->pdp_lost_on_expedition_status = 1;
                $salesdeliverynote->save();

                $salesdeliverynoteitem = SalesDeliveryNoteItem::findOrFail($dataitem['sales_delivery_note_item_id_'.$no]);
                $salesdeliverynoteitem->return_item_status = 1;
                $salesdeliverynoteitem->save();

                //--------------------------------------------------------Start Journal Voucher-----------------------------------------------------------------//
                    
                $preferencecompany 			= PreferenceCompany::first();
            
                $transaction_module_code 	= "PDP_LOE";
        
                $transactionmodule 		    = PreferenceTransactionModule::where('transaction_module_code', $transaction_module_code)
                ->first();
        
                $transaction_module_id 		= $transactionmodule['transaction_module_id'];

                $journal_voucher_period 	= date("Ym", strtotime($salesdeliverynote['sales_delivery_note_date']));

                $salesdeliverynote = SalesDeliveryNote::where('sales_delivery_note_id', $returnpdp['sales_delivery_note_id'])->first();

                // dd($salesdeliverynote);

                $data_journal = array(
                    'branch_id'						=> 1,
                    'journal_voucher_period' 		=> $journal_voucher_period,
                    'journal_voucher_date'			=> $returnpdp['return_pdp_lost_on_expedition_date'],
                    'journal_voucher_title'			=> 'PDP Hilang Di Expedisi '.$salesdeliverynote['sales_delivery_note_no'],
                    'journal_voucher_no'			=> $salesdeliverynote['sales_delivery_note_no'],
                    'journal_voucher_description'	=> $returnpdp['return_pdp_lost_on_expedition_remark'],
                    'transaction_module_id'			=> $transaction_module_id,
                    'transaction_module_code'		=> $transaction_module_code,
                    'transaction_journal_id' 		=> $salesdeliverynote['sales_delivery_note_id'],
                    'transaction_journal_no' 		=> $salesdeliverynote['sales_delivery_note_no'],
                    'created_id' 					=> Auth::id(),
                );

                // dd($data_journal);
                
                AcctJournalVoucher::create($data_journal);

                $journalvoucher = AcctJournalVoucher::where('created_id', Auth::id())
                ->orderBy('journal_voucher_id', 'DESC')
                ->first();

                $journal_voucher_id 	= $journalvoucher['journal_voucher_id'];

                $account 		= AcctAccount::where('account_id', $returnpdp['account_id'])
                ->where('data_state', 0)
                ->first();
                
                $item_type_id = SalesOrderItem::select('item_type_id')
                ->where('sales_order_item_id', $item['sales_order_item_id'])
                ->first(); 
                
                
                $harga_beli = PurchaseOrderItem::where('data_state', 0)
                ->where('purchase_order_item.item_type_id', $item_type_id['item_type_id'])
                ->first();
                
                $total_amount               = $item['quantity'] * $harga_beli['item_unit_cost'];
                
                
                $account_id_default_status 		= $account['account_default_status'];
                // dd($account_id_default_status);

                $data_debit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $returnpdp['account_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($total_amount),
                    'journal_voucher_debit_amount'	=> ABS($total_amount),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                );

                // dd($data_debit);
                
                AcctJournalVoucherItem::create($data_debit);

                $preferencecompany 			= PreferenceCompany::first();
                // dd($preferencecompany);
                
                $account 		= AcctAccount::where('account_id', $preferencecompany['account_inventory_trade_id'])
                ->where('data_state', 0)
                ->first();
                
                $account_id_default_status 		= $account['account_default_status'];

                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany['account_inventory_trade_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($total_amount),
                    'journal_voucher_credit_amount'	=> ABS($total_amount),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                );

                // dd($data_credit);


                AcctJournalVoucherItem::create($data_credit);

                $no++;

            }
//--------------------------------------------------------End Journal Voucher-----------------------------------------------------------------//


                $no++;
            
            
            $msg = 'Tambah Return PDP Hilang Di Expedisi Berhasil';
            return redirect('/return-pdp-lost-on-expedition')->with('msg',$msg);
        }else{
            $msg = 'Tambah Return PDP Hilang Di Expedisi Gagal';
            return redirect('/return-pdp-lost-on-expedition')->with('msg',$msg);
        }

    }

    public function detailReturnPDP_LostOnExpedition($return_pdp_lost_on_expedition_id){
        $returnpdpitem = ReturnPDP_LostOnExpedition_Item::where('return_pdp_lost_on_expedition_item.data_state', 0)
        
        ->where('return_pdp_lost_on_expedition_id', $return_pdp_lost_on_expedition_id)
        ->join('sales_order', 'sales_order.sales_order_id', '=', 'return_pdp_lost_on_expedition_item.sales_order_id')
        ->get();
        
        
        // dd($returnpdpitem);
        
        $returnpdp = ReturnPDP_LostOnExpedition::where('return_pdp_lost_on_expedition_id', $return_pdp_lost_on_expedition_id)
        ->select('return_pdp_lost_on_expedition.*', 'acct_account.account_code', 'acct_account.account_name')
        ->join('acct_account', 'return_pdp_lost_on_expedition.account_id', '=', 'acct_account.account_id')
        ->where('return_pdp_lost_on_expedition_id', $return_pdp_lost_on_expedition_id)
        ->first();


        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        return view('content/CoreExpedition/ReturnPDP_LostOnExpedition/FormDetailReturnPDP_LostOnExpedition', compact('warehouse', 'returnpdp', 'returnpdpitem', 'return_pdp_lost_on_expedition_id'));
    }
}
