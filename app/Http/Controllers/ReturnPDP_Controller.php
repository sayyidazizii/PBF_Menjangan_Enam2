<?php

namespace App\Http\Controllers;

use App\Models\InvItemStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\SalesOrderReturn;
use App\Models\PreferenceTransactionModule;
use App\Models\PreferenceCompany;
use App\Models\SalesDeliveryNote;
use App\Models\SalesDeliveryOrder;
use App\Models\SalesDeliveryOrderItem;
use App\Models\SalesOrder;
use App\Models\InvItemType;
use App\Models\InvWarehouse;
use App\Models\SalesDeliveryNoteItem;
use App\Models\SalesOrderReturnItem;
use App\Models\SalesOrderItem;
use App\Models\CoreExpedition;
use App\Models\ReturnPDP;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctJournalVoucher;
use App\Models\PurchaseOrderItem;
use App\Models\ReturnPDP_item;
use App\Models\SalesDeliveryNoteItemStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReturnPDP_Controller extends Controller
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

        $returnpdp = ReturnPDP::where('data_state', 0)
        ->where('return_pdp_date', '>=', $start_date)
        ->where('return_pdp_date', '<=', $end_date)
        ->get();

        // dd($returnpdp);
        return  view('content/CoreExpedition/ReturnPDP/ListReturnPDP',compact('returnpdp', 'end_date', 'start_date'));
    }

    public function filterReturnPDP(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/return-pdp');
    }

    public function resetFilterReturnPDP()
    {
        Session::forget('start_date');
        Session::forget('end_date');

        return redirect('/return-pdp');
    }

    public function searchSalesDeliveryNote()
    {
        Session::forget('purchaseorderitem');

        $salesdeliverynote= SalesDeliveryNote::where('data_state', 0)
        ->where('return_status', 0)
        ->get();

        // dd($salesdeliverynote);

        return view('content/CoreExpedition/ReturnPDP/SearchSalesDeliveryNote', compact('salesdeliverynote'));
    }

    public function addReturnPDP($sales_delivery_note_id){

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        $salesdeliverynote = SalesDeliveryNote::where('data_state', 0)
        ->where('sales_delivery_note_id', $sales_delivery_note_id)
        ->first();

        // dd($salesdeliverynote);

        $salesdeliverynoteitem = SalesDeliveryNoteItem::where('data_state', 0)
        ->where('sales_delivery_note_id', $sales_delivery_note_id)
        ->get();

        $salesdeliverynoteitemstock = SalesDeliveryNoteItemStock::where('data_state', 0)
        ->where('sales_delivery_note_id', $sales_delivery_note_id)
        ->get();

        //dd($salesdeliverynote,$salesdeliverynoteitem,$salesdeliverynoteitemstock);

        $salesorder = SalesOrder::select('sales_order.*')
        ->where('data_state', 0)
        ->first();

        return view('content/CoreExpedition/ReturnPDP/FormAddReturnPDP',compact('warehouse', 'salesdeliverynote', 'sales_delivery_note_id', 'salesdeliverynoteitem', 'salesorder'));
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
        $salesdeliveryorder = SalesDeliveryOrder::where('data_state', 0)
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
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

    public function getNomorSalesOrderId($sales_order_id){
        $so = SalesOrder::where('sales_order.data_state', 0)
        ->where('sales_order.sales_order_id', $sales_order_id)
        ->first();

        if($so == null){
            return "-";
        }

        return $so['sales_order_no'];
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

    public function getSalesDeliveryNoteDate($sales_delivery_note_id){
        $deliverynote = SalesDeliveryNote::where('sales_delivery_note_id', $sales_delivery_note_id)
        ->where('data_state', 0)
        ->first();

        if($deliverynote == null){
            return "-";
        }

        return $deliverynote['sales_delivery_note_date'];
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

    public function processAddReturnPDP(Request $request){
        $request->validate([
            'warehouse_id'                  => 'required',
            'return_pdp_date'       => 'required',
        ]);
        $returnpdp = array(
            'warehouse_id'                  => $request->warehouse_id,
            'sales_order_id'                => $request->sales_order_id,
            'sales_delivery_order_id'       => $request->sales_delivery_order_id,
            'sales_delivery_note_id'        => $request->sales_delivery_note_id,
            'return_pdp_date'               => $request->return_pdp_date,
            'return_pdp_remark'             => $request->return_pdp_remark,
            // 'branch_id'                     => Auth::user()->branch_id,
            'created_id'                    => Auth::id(),
        );

        if(ReturnPDP::create($returnpdp)){
            $return_pdp_id = ReturnPDP::select('return_pdp_id')
            ->orderBy('created_at', 'DESC')
            ->first();

            // $salesorderitem = SalesOrderItem::where('sales_order_id',$request->sales_order_id_1)
            // ->get();
            $salesdeliveryordernoteitemstock = SalesDeliveryNoteItemStock::where('sales_order_id',$request->sales_order_id)
            ->get();
            
            $no =1;

            $dataitem = $request->all();
            foreach($salesdeliveryordernoteitemstock as $item){
                $item = ReturnPDP_item::create([
                    'return_pdp_id'	                => $return_pdp_id['return_pdp_id'],
                    'sales_delivery_order_id'	    => $return_pdp_id['sales_delivery_order_id'],
                    'sales_delivery_note_id' 	    => $dataitem['sales_delivery_note_id'],
                    'sales_delivery_note_item_id'   => $dataitem['sales_delivery_note_item_id'],
                    'sales_order_id' 			    => $dataitem['sales_order_id'],
                    'sales_order_item_id' 		    => $item['sales_order_item_id'],
                    'item_id' 		                => $dataitem['item_id_'.$no],
                    'item_stock_id' 		        => $item['item_stock_id'],
                    'item_type_id' 		            => $dataitem['item_type_id_'.$no],
                    'item_unit_id' 		            => $dataitem['item_unit_id_'.$no],
                    'item_unit_price' 		        => $dataitem['item_unit_price_'.$no],
                    'subtotal_price' 		        => $dataitem['quantity_return_'.$no]*$dataitem['item_unit_price_'.$no],
                    'quantity'					    => $dataitem['quantity_return_'.$no],		
                    'quantity_return'		        => $dataitem['quantity_'.$no],	
                    'created_id'                    => Auth::id(),
                ]);

                // dd($item);

                $salesdeliverynote = SalesDeliveryNote::findOrFail($dataitem['sales_delivery_note_id']);
                $salesdeliverynote->return_status = 1;
                $salesdeliverynote->save();

                $salesdeliverynoteitem = SalesDeliveryNoteItem::findOrFail($dataitem['sales_delivery_note_item_id']);
                $salesdeliverynoteitem->return_item_status = 1;
                $salesdeliverynoteitem->save();



                $itemstock = InvItemStock::findOrfail($item['item_stock_id']);
                //dd($itemstock);

                if(!empty($itemstock)){
                    InvItemStock::create([
                        'goods_received_note_id'            =>   $itemstock['goods_received_note_id'],
                        'goods_received_note_item_id'       =>   $itemstock['goods_received_note_item_id'],
                        'item_stock_date'                   =>   \Carbon\Carbon::now(), # new \Datetime()
                        'item_stock_expired_date'           =>   $itemstock['item_stock_expired_date'],
                        'item_batch_number'                 =>   $itemstock['item_batch_number'],
                        'purchase_order_item_id'            =>   $itemstock['purchase_order_item_id'],
                        'warehouse_id'                      =>   7,
                        'item_category_id'                  =>   $itemstock['item_category_id'],
                        'item_type_id'                      =>   $dataitem['item_type_id_'.$no],
                        'item_id'                           =>   $itemstock['item_id'],
                        'item_unit_id'                      =>   $dataitem['item_unit_id_'.$no],
                        'item_total'                        =>   $itemstock['item_total'],
                        'item_unit_id_default'              =>   $itemstock['item_unit_id_default'],
                        'item_default_quantity_unit'        =>   $itemstock['item_default_quantity_unit'],
                        'quantity_unit'                     =>   $dataitem['quantity_return_'.$no],
                        'item_weight_default'               =>   $itemstock['item_weight_default'],
                        'item_weight_unit'                  =>   $itemstock['item_weight_unit'],
                        'package_id'                        =>   $itemstock['package_id'],
                        'package_total'                     =>   $itemstock['package_total'],
                        'package_unit_id'                   =>   $itemstock['package_unit_id'],
                        'package_price'                     =>   $itemstock['package_price'],
                        'data_state'                        =>   $itemstock['data_state'],
                        'created_id'                        =>   $itemstock['created_id'],
                        'created_at'                        =>   $itemstock['created_at'],
                    ]);              
                }
                else{
                    //dd($inv_stock_note);
                    InvItemStock::create([
                        'goods_received_note_id'            =>   $itemstock['goods_received_note_id'],
                        'goods_received_note_item_id'       =>   $itemstock['goods_received_note_item_id'],
                        'item_stock_date'                   =>   \Carbon\Carbon::now(), # new \Datetime()
                        'item_stock_expired_date'           =>   $itemstock['item_stock_expired_date'],
                        'item_batch_number'                 =>   $itemstock['item_batch_number'],
                        'purchase_order_item_id'            =>   $itemstock['purchase_order_item_id'],
                        'warehouse_id'                      =>   7,
                        'item_category_id'                  =>   $itemstock['item_category_id'],
                        'item_type_id'                      =>   $dataitem['item_type_id_'.$no],
                        'item_id'                           =>   $itemstock['item_id'],
                        'item_unit_id'                      =>   $dataitem['item_unit_id_'.$no],
                        'item_total'                        =>   $itemstock['item_total'],
                        'item_unit_id_default'              =>   $itemstock['item_unit_id_default'],
                        'item_default_quantity_unit'        =>   $itemstock['item_default_quantity_unit'],
                        'quantity_unit'                     =>   $dataitem['quantity_return_'.$no],
                        'item_weight_default'               =>   $itemstock['item_weight_default'],
                        'item_weight_unit'                  =>   $itemstock['item_weight_unit'],
                        'package_id'                        =>   $itemstock['package_id'],
                        'package_total'                     =>   $itemstock['package_total'],
                        'package_unit_id'                   =>   $itemstock['package_unit_id'],
                        'package_price'                     =>   $itemstock['package_price'],
                        'data_state'                        =>   $itemstock['data_state'],
                        'created_id'                        =>   $itemstock['created_id'],
                        'created_at'                        =>   $itemstock['created_at'],
                    ]);
                }
                



            
                //--------------------------------------------------------Start Journal Voucher-----------------------------------------------------------------//
                    
                $preferencecompany 			= PreferenceCompany::first();
            
                $transaction_module_code 	= "RPDP";
        
                $transactionmodule 		    = PreferenceTransactionModule::where('transaction_module_code', $transaction_module_code)
                ->first();
        
                $transaction_module_id 		= $transactionmodule['transaction_module_id'];

                $journal_voucher_period 	= date("Ym", strtotime($salesdeliverynote['sales_delivery_note_date']));

                $salesdeliverynote = SalesDeliveryNote::where('sales_delivery_note_id', $returnpdp['sales_delivery_note_id'])->first();

                // dd($salesdeliverynote);

                $data_journal = array(
                    'branch_id'						=> 1,
                    'journal_voucher_period' 		=> $journal_voucher_period,
                    'journal_voucher_date'			=> $returnpdp['return_pdp_date'],
                    'journal_voucher_title'			=> 'Return PDP '.$salesdeliverynote['sales_delivery_note_no'],
                    'journal_voucher_no'			=> $salesdeliverynote['sales_delivery_note_no'],
                    'journal_voucher_description'	=> $returnpdp['return_pdp_remark'],
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

                $account 		= AcctAccount::where('account_id', $preferencecompany['account_inventory_trade_id'])
                ->where('data_state', 0)
                ->first();

                $item_type_id = SalesOrderItem::select('item_type_id')
                ->where('sales_order_item_id', $item['sales_order_item_id'])
                ->first(); 

                
                $harga_beli = PurchaseOrderItem::where('data_state', 0)
                ->where('purchase_order_item.item_type_id', $item_type_id['item_type_id'])
                ->first();

                $total_amount               = $item['quantity_return'] * $harga_beli['item_unit_cost'];
                
                // dd($total_amount);

                $account_id_default_status 		= $account['account_default_status'];

                $data_debit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany['account_inventory_trade_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($total_amount),
                    'journal_voucher_debit_amount'	=> ABS($total_amount),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                );

                
                
                AcctJournalVoucherItem::create($data_debit);
                
                $account 		= AcctAccount::where('account_id', $preferencecompany['account_pdp_id'])
                ->where('data_state', 0)
                ->first();
                
                // dd($account);
                $account_id_default_status 		= $account['account_default_status'];

                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany['account_pdp_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($total_amount),
                    'journal_voucher_credit_amount'	=> ABS($total_amount),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                );

                AcctJournalVoucherItem::create($data_credit);

                $no++;

            }
        
//--------------------------------------------------------End Journal Voucher-----------------------------------------------------------------//





            
            $msg = 'Tambah Return PDP Berhasil';
            return redirect('/return-pdp')->with('msg',$msg);
        }else{
            $msg = 'Tambah Return PDP Gagal';
            return redirect('/return-pdp')->with('msg',$msg);
        }

    }

    public function detailReturnPDP($return_pdp_id){
        $returnpdpitem = ReturnPDP_Item::where('return_pdp_item.data_state', 0)
        ->where('return_pdp_id', $return_pdp_id)
        ->join('sales_order', 'sales_order.sales_order_id', '=', 'return_pdp_item.sales_order_id')
        ->get();



        $returnpdp = ReturnPDP::findOrFail($return_pdp_id);

        // dd( $salesorderreturn);

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        return view('content/CoreExpedition/ReturnPDP/FormDetailReturnPDP', compact('warehouse', 'returnpdp', 'returnpdpitem', 'return_pdp_id'));
    }
}
