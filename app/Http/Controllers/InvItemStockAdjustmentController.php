<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CoreGrade;
use App\Models\InvItem;
use App\Models\InvItemCategory;
use App\Models\InvItemStock;
use App\Models\InvItemUnit;
use App\Models\InvItemType;
use App\Models\InvItemStockAdjustment;
use App\Models\InvItemStockAdjustmentItem;
use App\Models\InvWarehouse;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InvItemStockAdjustmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        Session::forget('stockadjustmentelement');
        Session::forget('itemstock');

        if(!$start_date = Session::get('start_date')){
            $start_date = date('Y-m-d');
        } else {
            $start_date = Session::get('start_date');
        }
        if(!$end_date = Session::get('end_date')){
            $end_date = date('Y-m-d');
        } else {
            $end_date = Session::get('end_date');
        }

        $data  = InvItemStockAdjustment::join('inv_item_stock_adjustment_item','inv_item_stock_adjustment.stock_adjustment_id','=','inv_item_stock_adjustment_item.stock_adjustment_id')
        ->where('inv_item_stock_adjustment.stock_adjustment_date', '>=', $start_date)
        ->where('inv_item_stock_adjustment.stock_adjustment_date', '<=', $end_date)
        ->where('inv_item_stock_adjustment.data_state',0)
        ->get(); 

        return view('content.InvItemStockAdjustment.ListInvItemStockAdjustment',compact('data','start_date','end_date'));
    }

    public function filterList(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/item-stock-adjustment');
    }

    public function add()
    {
        $stockadjustmentelement     = Session::get('stockadjustmentelement');
        $itemstock                  = Session::get('itemstock');

        if(!$stockadjustmentelement){
            $stockadjustmentelement = array(
                'stock_adjustment_date'    => date('d-m-Y'),
                'warehouse_id'             => '',
                'item_category_id'         => '',
                'item_type_id'             => '',
            );
        }

        $warehouse  = InvWarehouse::where('data_state',0)
        ->get()
        ->pluck('warehouse_name','warehouse_id');

        $itemcategory  = InvItemCategory::where('data_state',0)
        ->get()
        ->pluck('item_category_name','item_category_id');

        $itemtype = array();
        if($stockadjustmentelement['item_category_id']){
            $itemtype = InvItemType::where('data_state', 0)
            ->where('item_category_id', $stockadjustmentelement['item_category_id'])
            ->get()
            ->pluck('item_type_name', 'item_type_id');
        }

        if(!$itemstock){
            $itemstock = array();
        }
      //  dd($itemstock);
        
        return view('content.InvItemStockAdjustment.FormAddInvItemStockAdjustment', compact('warehouse', 'itemcategory', 'itemtype', 'stockadjustmentelement', 'itemstock'));
    }

    public function elements_add(Request $request){
        $stockadjustmentelement= Session::get('stockadjustmentelement');
        if(!$stockadjustmentelement || $stockadjustmentelement == ''){
            $stockadjustmentelement['stock_adjustment_date']    = date('d-m-Y');
            $stockadjustmentelement['warehouse_id']             = '';
            $stockadjustmentelement['item_category_id']         = '';
            $stockadjustmentelement['item_type_id']             = '';
        }
        $stockadjustmentelement[$request->name] = $request->value;
        Session::put('stockadjustmentelement', $stockadjustmentelement);
    }

    public function getListItemStock(Request $request)
    {
        $warehouse_id       = $request->warehouse_id;
        $item_category_id   = $request->item_category_id;
        $item_type_id       = $request->item_type_id;

        $itemstock = InvItemStock::where('data_state', 0)
        ->where('warehouse_id', $warehouse_id)
        ->where('item_category_id', $item_category_id)
        ->where('item_type_id', $item_type_id)
        ->get();

        Session::put('itemstock', $itemstock);

        return redirect('/item-stock-adjustment/add');
    }

    public function getItemName($item_category_id, $item_type_id, $item_id)
    {
        if($item_id == 0){
            $item_category_name = InvItemCategory::where('item_category_id', $item_category_id)
            ->first()
            ->item_category_name;
            
            $item_type_name = InvItemType::where('item_type_id', $item_type_id)
            ->first()
            ->item_type_name;

            $item_name = $item_category_name.' '.$item_type_name.' No Grade';
        }else{
            $item_category_name = InvItemCategory::where('item_category_id', $item_category_id)
            ->first()
            ->item_category_name;
            
            $item_type_name = InvItemType::where('item_type_id', $item_type_id)
            ->first()
            ->item_type_name;

            $grade_name = InvItem::select('core_grade.grade_name')
            ->join('core_grade', 'core_grade.grade_id', '=', 'inv_item.grade_id')
            ->where('inv_item.item_id', $item_id)
            ->first()
            ->grade_name;

            $item_name = $item_category_name.' '.$item_type_name.' '.$grade_name;
        }

        return $item_name;
    }

    public function getItemNameStock($item_stock_id)
    {
        $itemstock = InvItemStock::where('item_stock_id', $item_stock_id)
        ->first();

        $item_id            = $itemstock['item_id'] ?? '';
        $item_category_id   = $itemstock['item_category_id'] ?? '';
        $item_type_id       = $itemstock['item_type_id'] ?? '';

        if($item_id == 0){
            $item_category_name = InvItemCategory::where('item_category_id', $item_category_id)
            ->first()
            ->item_category_name;
            
            $item_type_name = InvItemType::where('item_type_id', $item_type_id)
            ->first()
            ->item_type_name;

            $item_name = $item_category_name.' '.$item_type_name.' No Grade';
        }else{
            $item_category_name = InvItemCategory::select('item_category_name')
            ->where('item_category_id', $item_category_id)
            ->first();
            
            $item_type_name = InvItemType::select('item_type_name')
            ->where('item_type_id', $item_type_id)
            ->first();

            $grade_name = InvItem::select('core_grade.grade_name')
            ->join('core_grade', 'core_grade.grade_id', '=', 'inv_item.grade_id')
            ->where('inv_item.item_id', $item_id)
            ->first();

            $item_name = $item_category_name.' '.$item_type_name.' '.$grade_name;
        }

        return $item_name;
    }

    public function getWarehouseName($warehouse_id)
    {
        $data   = InvWarehouse::where('warehouse_id', $warehouse_id)->first();

        return $data['warehouse_name']?? '';
    }

    public function getItemUnitName($item_unit_id)
    {
        $data   = InvItemUnit::where('item_unit_id', $item_unit_id)->first();

        return $data['item_unit_name']?? '';
    }

    public function getItemStock($item_id, $item_unit_id, $item_category_id, $warehouse_id)
    {
        $data = InvItemStock::where('item_id',$item_id)
        ->where('warehouse_id', $warehouse_id)
        ->where('item_category_id',$item_category_id)
        ->where('item_unit_id', $item_unit_id)
        ->first();
        return $data['last_balance'];
    }

    public function processAdd(Request $request)
    {
        $stockadjustmentelement     = Session::get('stockadjustmentelement');
        $itemstock                  = Session::get('itemstock');

        $data = array(
            'stock_adjustment_date' => $stockadjustmentelement['stock_adjustment_date'],
            'warehouse_id'          => $stockadjustmentelement['warehouse_id'],
            'created_id'            => Auth::id(),
        );

        if(InvItemStockAdjustment::create($data)){
            $stock_adjustment_id = InvItemStockAdjustment::select('stock_adjustment_id')
            ->where('created_id', Auth::id())
            ->first()
            ->stock_adjustment_id;

            $allrequest = request()->all();
            //dd($allrequest);
            foreach($itemstock as $key => $val){
                if($allrequest['adjustment_difference_amount_'.$val['item_stock_id']] != '' && $allrequest['adjustment_difference_amount_'.$val['item_stock_id']] != 0){
                    $data_item = array(
                        'stock_adjustment_id'       => $stock_adjustment_id,
                        'item_stock_id'             => $val['item_stock_id'],
                        'item_unit_id'              => $val['item_unit_id'],
                        'item_first_amount'         => $allrequest['item_total_'.$val['item_stock_id']],
                        'item_last_amount'          => $allrequest['adjustment_amount_'.$val['item_stock_id']],
                        'item_adjustment_amount'    => $allrequest['adjustment_difference_amount_'.$val['item_stock_id']],
                        'item_adjustment_remark'    => $allrequest['stock_adjustment_item_remark_'.$val['item_stock_id']],
                        'item_batch_number'         => $allrequest['item_batch_number_'.$val['item_stock_id']],
                        // 'item_batch_number'         => $val['item_batch_number'],
                    );
                    //dd($data_item);
                    if(InvItemStockAdjustmentItem::create($data_item)){
                        $item_stock = InvItemStock::findOrFail($data_item['item_stock_id']);
                        $item_stock->item_batch_number = $data_item['item_batch_number'];
                        $item_stock->quantity_unit = $data_item['item_last_amount'];
                        $item_stock->save();
                    }

               }
            }
        } else {
            $msg = 'Penyesuaian Stock Gagal';
            return redirect('/item-stock-adjustment/add')->with('msg',$msg);
        }

        $msg = 'Penyesuaian Stock Berhasil';
        return redirect('/item-stock-adjustment/add')->with('msg',$msg);
    }

    public function addReset(){
        Session::forget('category_id');
        Session::forget('item_id');
        Session::forget('unit_id');
        Session::forget('warehouse_id');
        Session::forget('date');
        Session::forget('datases');

        return redirect('/item-stock-adjustment/add');
    }

    public function listReset()
    {
        Session::forget('start_date');
        Session::forget('end_date');

        return redirect('/item-stock-adjustment');
    }

    public function getInvItemType(Request $request){
        $item_category_id = $request->item_category_id;
        $data='';

        $type = InvItemType::where('item_category_id', $item_category_id)
        ->where('data_state', 0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($type as $mp){
            $data .= "<option value='$mp[item_type_id]'>$mp[item_type_name]</option>\n";	
        }

        return $data;
    }
}

