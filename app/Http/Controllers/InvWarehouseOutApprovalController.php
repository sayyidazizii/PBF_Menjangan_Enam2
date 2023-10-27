<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\CoreCity;
use App\Models\CoreGrade;
use App\Models\CoreProvince;
use App\Models\InvItem;
use App\Models\InvItemStock;
use App\Models\InvItemType;
use App\Models\InvItemUnit;
use App\Models\InvWarehouse;
use App\Models\InvWarehouseLocation;
use App\Models\InvWarehouseOut;
use App\Models\InvWarehouseOutItem;
use App\Models\InvWarehouseOutType;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class InvWarehouseOutApprovalController extends Controller
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
        $invwarehouseout = InvWarehouseOut::where('data_state','=',0)
        ->where('warehouse_out_status', 0)
        ->get();

        return view('content/InvWarehouseOutApproval/ListInvWarehouseOutApproval',compact('invwarehouseout'));
    }

    public function approveInvWarehouseOutApproval($warehouse_out_id)
    {
        $warehouseout = InvWarehouseOut::findOrFail($warehouse_out_id);

        $warehouseoutitem = InvWarehouseOutItem::where('data_state', 0)
        ->where('warehouse_out_id', $warehouse_out_id)
        ->get();

        return view('content/InvWarehouseOutApproval/FormApproveInvWarehouseOutApproval', compact('warehouseout', 'warehouseoutitem'));
    }

    public function processApproveInvWarehouseOutApproval($warehouse_out_id)
    {
        $warehouseout = InvWarehouseOut::findOrFail($warehouse_out_id);
        $warehouseout->warehouse_out_status = 1;

        if($warehouseout->save()){
            $warehouseoutitem = InvWarehouseOutItem::where('warehouse_out_id', $warehouse_out_id)
            ->where('data_state', 0)
            ->get();

            
            
            $transaction_module_code 	= "WHO";
        
            $transactionmodule 		    = PreferenceTransactionModule::where('transaction_module_code', $transaction_module_code)
            ->first();
    
            $transaction_module_id 		= $transactionmodule['transaction_module_id'];

            $journal_voucher_period 	= date("Ym", strtotime($warehouseout['warehouse_out_date']));

            $data_journal = array(
                'branch_id'						=> 1,
                'journal_voucher_period' 		=> $journal_voucher_period,
                'journal_voucher_date'			=> $warehouseout['warehouse_out_date'],
                'journal_voucher_title'			=> 'Pengeluaran Barang Gudang '.$warehouseout['warehouse_out_no'],
                'journal_voucher_no'			=> $warehouseout['warehouse_out_no'],
                'journal_voucher_description'	=> $warehouseout['warehouse_out_remark'],
                'transaction_module_id'			=> $transaction_module_id,
                'transaction_module_code'		=> $transaction_module_code,
                'transaction_journal_id' 		=> $warehouseout['warehouse_out_id'],
                'transaction_journal_no' 		=> $warehouseout['warehouse_out_no'],
                'created_id' 					=> Auth::id(),
            );
            
            AcctJournalVoucher::create($data_journal);

            foreach($warehouseoutitem as $val){
                $itemstock = InvItemStock::where('item_stock_id', $val['item_stock_id'])
                ->first();

                $itemunitfirst = InvItemUnit::where('data_state', 0)
                ->where('item_unit_id', $itemstock['item_unit_id'])
                ->first();

                $itemunitsecond = InvItemUnit::where('data_state', 0)
                ->where('item_unit_id', $val['item_unit_id'])
                ->first();

                $item_total = $itemstock['item_total'] - ($val['quantity'] * $itemunitsecond['item_unit_default_quantity'] / $itemunitfirst['item_unit_default_quantity']);

                $itemstock->item_total = $item_total;
                if($item_total <= 0){
                    $itemstock->data_state = 1;
                }
                $itemstock->save();

//--------------------------------------------------------------Journal Voucher---------------------------------------------------------------//
                
                $preferencecompany 			= PreferenceCompany::first();

                $purchaseorder              = InvWarehouseOutItem::select('purchase_order_item.item_unit_cost', 'purchase_order_item.item_unit_id')
                ->join('inv_item_stock', 'inv_item_stock.item_stock_id', 'inv_warehouse_out_item.item_stock_id')
                ->join('inv_goods_received_note_item', 'inv_goods_received_note_item.goods_received_note_item_id', 'inv_item_stock.goods_received_note_item_id')
                ->join('purchase_order_item', 'purchase_order_item.purchase_order_item_id', 'inv_goods_received_note_item.purchase_order_item_id')
                ->where('inv_warehouse_out_item.warehouse_out_item_id', $val['warehouse_out_item_id'])
                ->first();

                if($itemstock['item_id'] == 0){
                    $item = InvItemType::where('item_type_id', $itemstock['item_type_id'])
                    ->first();
                }else{
                    $item = InvItem::where('item_id', $itemstock['item_id'])
                    ->first();
                }
                
                $itemunitfirst = InvItemUnit::where('data_state', 0)
                ->where('item_unit_id', $purchaseorder['item_unit_id'])
                ->first();

                $itemunitsecond = InvItemUnit::where('data_state', 0)
                ->where('item_unit_id', $val['item_unit_id'])
                ->first();

                $total_amount               = $val['quantity'] * ($purchaseorder['item_unit_cost'] * $itemunitsecond['item_unit_default_quantity'] / $itemunitfirst['item_unit_default_quantity']);

                
                $journalvoucher = AcctJournalVoucher::where('created_id', Auth::id())
                ->orderBy('journal_voucher_id', 'DESC')
                ->first();

                $journal_voucher_id 	= $journalvoucher['journal_voucher_id'];

                if($itemstock['item_id'] != 0){
                    
                    $account 		= AcctAccount::where('account_id', $item['hpp_account_id'])
                    ->where('data_state', 0)
                    ->first();

                    $account_id_default_status 		= $account['account_default_status'];

                    $data_debit = array (
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $item['hpp_account_id'],
                        'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                        'journal_voucher_amount'		=> ABS($total_amount),
                        'journal_voucher_debit_amount'	=> ABS($total_amount),
                        'account_id_default_status'		=> $account_id_default_status,
                        'account_id_status'				=> 1,
                    );

                    AcctJournalVoucherItem::create($data_debit);
                }

                $account 		= AcctAccount::where('account_id', $item['inv_account_id'])
                ->where('data_state', 0)
                ->first();

                $account_id_default_status 		= $account['account_default_status'];

                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $item['inv_account_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($total_amount),
                    'journal_voucher_credit_amount'	=> ABS($total_amount),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                );

                AcctJournalVoucherItem::create($data_credit);
            
//-------------------------------------------------------------End Journal Voucher------------------------------------------------------------//

            }

            $msg = 'Persetujuan Pengeluaran Gudang Berhasil';
            return redirect('/warehouse-out-approval')->with('msg',$msg);
        }else{
            $msg = 'Persetujuan Pengeluaran Gudang Gagal';
            return redirect('/warehouse-out-approval')->with('msg',$msg);
        }
    }

    public function processDisapproveInvWarehouseOutApproval(Request $request)
    {
        $warehouse_out_id   = $request->warehouse_out_id;

        $warehouseout = InvWarehouseOut::findOrFail($warehouse_out_id);
        $warehouseout->warehouse_out_status = 2;

        if($warehouseout->save()){
            $msg = 'Disapprove Pengeluaran Gudang Berhasil';
            return redirect('/warehouse-out-approval')->with('msg',$msg);
        }else{
            $msg = 'Disapprove Pengeluaran Gudang Gagal';
            return redirect('/warehouse-out-approval')->with('msg',$msg);
        }
    }

    public function elements_add(Request $request){
        $warehouseoutelements= Session::get('warehouseoutelements');
        if(!$warehouseoutelements || $warehouseoutelements == ''){
            $warehouseoutelements['warehouse_id'] = '';
            $warehouseoutelements['warehouse_out_type_id'] = '';
            $warehouseoutelements['warehouse_out_requisition_date'] = '';
            $warehouseoutelements['warehouse_out_remark'] = '';
        }
        $warehouseoutelements[$request->name] = $request->value;
        Session::put('warehouseoutelements', $warehouseoutelements);
    }

    public function getItemUnitName($item_unit_id){
        $itemunit = InvItemUnit::select('item_unit_name')
        ->where('data_state', 0)
        ->where('item_unit_id', $item_unit_id)
        ->first();

        return $itemunit['item_unit_name'];
    }

    public function getInvWarehouseName($warehouse_id){
        $warehouse = InvWarehouse::select('warehouse_name')
        ->where('data_state', 0)
        ->where('warehouse_id', $warehouse_id)
        ->first();

        if($warehouse == null){
            return '-';
        }

        return $warehouse['warehouse_name'];
    }

    public function getInvWarehouseOutTypeName($warehouse_out_type_id){
        $warehouse = InvWarehouseOutType::select('warehouse_out_type_name')
        ->where('data_state', 0)
        ->where('warehouse_out_type_id', $warehouse_out_type_id)
        ->first();

        if($warehouse == null){
            return '-';
        }

        return $warehouse['warehouse_out_type_name'];
    }

    public function getItemName($item_stock_id){
        $itemstock = InvItemStock::select('inv_item_stock.item_total', 'inv_item_stock.item_id', 'inv_item_stock.item_category_id', 'inv_item_unit.item_unit_name', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name) AS item_name'))
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_stock.item_category_id')
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
        ->join('inv_item_unit', 'inv_item_unit.item_unit_id', 'inv_item_stock.item_unit_id')
        ->where('inv_item_stock.data_state', 0)
        ->where('inv_item_stock.item_stock_id', $item_stock_id)
        ->first();

        if($itemstock['item_id'] != 0){
            $grade = InvItemStock::select('core_grade.grade_name')
            ->join('inv_item', 'inv_item.item_id', 'inv_item_stock.item_id')
            ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
            ->where('inv_item_stock.data_state', 0)
            ->where('inv_item_stock.item_stock_id', $item_stock_id)
            ->first();

            $item_name = $itemstock['item_name'].' '.$grade['grade_name'];
        }else{
            if($itemstock['item_category_id'] == 3){
                $item_name = $itemstock['item_name'];
            }else{
                $item_name = $itemstock['item_name']." No Grade";
            }
        }

        return $item_name;
    }
}
