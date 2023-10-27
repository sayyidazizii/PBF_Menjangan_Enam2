<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicController;
use App\Providers\RouteServiceProvider;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\InvWarehouse;
use App\Models\CoreSupplier;
use App\Models\InvItem;
use App\Models\InvItemUnit;
use App\Models\CoreGrade;
use App\Models\CorePackage;
use App\Models\InvItemCategory;
use App\Models\InvItemType;
use App\Models\InvItemStock;
use App\Models\SalesDeliveryOrder;
use App\Models\SalesDeliveryOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\CoreCustomer;
use App\Models\InvGoodsReceivedNote;
use App\Models\InvGoodsReceivedNoteItem;
use App\Models\SalesDeliveryOrderItemStock;
use App\Models\SalesDeliveryOrderItemStockTemporary;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\type;

class SalesDeliveryOrderController extends Controller
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

        Session::forget('dataarraysalesdeliveryorder');
        Session::forget('datacheckboxdeliveryorder');
        Session::forget('dataarrayinvitemstock');
        Session::forget('salesdeliveryorderelements');
    
        $salesdeliveryorder = SalesDeliveryOrder::where('data_state','=',0)
        ->where('sales_delivery_order_date', '>=', $start_date)
        ->where('sales_delivery_order_date', '<=', $end_date)
        ->get();

        return view('content/SalesDeliveryOrder/ListSalesDeliveryOrder',compact('salesdeliveryorder', 'start_date', 'end_date'));
    }

    public function filterSalesDeliveryOrder(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/sales-delivery-order');
    }

    // public function search()
    // {
    //     Session::forget('dataarrayinvitemstock');
    //     Session::forget('datacheckboxdeliveryorder');

    //     $salesorderitem = SalesOrderItem::select('sales_order.','sales_order_item.')
    //     ->join('sales_order','sales_order.sales_order_id','sales_order_item.sales_order_id')
    //     ->where('sales_order.data_state','=',0)
    //     ->where('sales_order_item.data_state','=',0)
    //     ->where('sales_order_item.quantity_resulted','>',0)
    //     ->where('approved','=',1)
    //     ->get();

    //     return view('content/SalesDeliveryOrder/SearchSalesOrder',compact('salesorderitem'));
    // }

    // public function addSalesDeliveryOrder()
    // {
    //     $datacheckboxdeliveryorder = Session::get('datacheckboxdeliveryorder');

    //     $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
    //     ->where('data_state', 0)
    //     ->pluck('warehouse_name', 'warehouse_id');

    //     return view('content/SalesDeliveryOrder/FormAddSalesDeliveryOrder',compact('warehouse', 'datacheckboxdeliveryorder'));
    // }

    public function search()
    {
        $salesorder = SalesOrder::select('sales_order.*')
        ->where('sales_order.data_state','=',0)
        ->where('sales_order.sales_order_status', 0)
        ->where('sales_order.sales_delivery_order_status', 0)
        ->where('sales_order.approved', 1)
        ->get();

        Session::forget('dataarrayinvitemstock');
        Session::forget('salesdeliveryorderelements');

        return view('content/SalesDeliveryOrder/SearchSalesOrder',compact('salesorder'));
    }


    public function getType($sales_order_id){

        $first_salesorderitem = SalesOrderItem::where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->first();


        $type2 = SalesOrderItem::select('inv_item_type.item_type_id')
        ->join('inv_item_type', 'sales_order_item.item_type_id', '=', 'inv_item_type.item_type_id')
        ->where('sales_order_item.data_state', 0)
        ->where('sales_order_item.sales_order_id', $first_salesorderitem['sales_order_id'])
        ->get();
        return($type2);
    }


    public function getTypeStock($sales_order_id){
        
        $data_type_id = $this->getType($sales_order_id);

          $type_id = array("item_type_id");
          foreach ($data_type_id as $data_item) {
              $type_id[] = array(
                'item_type_id' => $data_item->item_type_id,
                'datastock' => $this->getTypeStockid($data_item->item_type_id),
              );

          }
        return ($type_id);
    }


    public function getTypeStockid($type_id){
        

            $stock = InvItemStock::select('inv_item_stock.item_stock_id', DB::raw('CONCAT(inv_item_type.item_type_name, " - ", inv_item_stock.item_batch_number) AS item_name'))
            ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
            ->join('inv_item_unit', 'inv_item_unit.item_unit_id', 'inv_item_stock.item_unit_id')
            ->orderby('inv_item_stock.item_stock_expired_date', 'ASC')
            ->where('inv_item_stock.quantity_unit', '>', 0)
            ->where('inv_item_stock.data_state', 0)
            ->where('inv_item_stock.warehouse_id', 6)
            ->where('inv_item_stock.item_type_id' ,$type_id)
            ->get();
            // ->pluck('item_name', 'item_stock_id');
            // $datastockitem = array(
            //     'item_stock_id' => $stock['item_stock_id'],
            //     'item_stock_name' => $stock['item_name']
            // );

        return ($stock);
    }



    public function getItemtemp($sales_order_id){
        
        $datasdo = $this->getSOid($sales_order_id);

          $data = array("sales_order_item_id");
          foreach ($datasdo as $data_item) {
              $data[] = array(
                'sales_order_item_id' => $data_item->sales_order_item_id,
                'data' => $this->getSOid2($data_item->sales_order_item_id),
              );

          }
        return ($data);
    }

    public function getSOid2($sales_order_item_id){

        $salesorderitem = SalesDeliveryOrderItemStockTemporary::select('*')
        ->where('sales_order_item_id', $sales_order_item_id)
        ->where('data_state', 0)
        ->get();
        

    return ($salesorderitem);
    }

    public function getSOid($sales_order_id){

        $salesorderitem = SalesDeliveryOrderItemStockTemporary::select('*')
        ->where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->get();

    return ($salesorderitem);
    }

    public function getPpnOut($sales_order_id){

        $salesorder = SalesOrder::select('ppn_out_amount')
        ->where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->first();

        return $salesorder['ppn_out_amount'];
    }



    public function addSalesDeliveryOrder($sales_order_id)
    {
        //dd($this->getItemtemp($sales_order_id));
        $salesdeliveryorderelements  = Session::get('salesdeliveryorderelements');
        $inv_item_stock_temporary = Session::get('dataarrayinvitemstock');
        //dd($salesdeliveryorderelements);
        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        $salesorderitem = SalesOrderItem::select('sales_order_item.*')
        ->where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->get();


        $first_salesorderitem = SalesOrderItem::where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->first();

        $salesorder = SalesOrder::select('sales_order.*')
        ->where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->first();

        $type = SalesOrderItem::select('sales_order_item.sales_order_item_id', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name) AS item_name'))
        ->join('inv_item_type', 'sales_order_item.item_type_id', '=', 'inv_item_type.item_type_id')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->where('sales_order_item.data_state', 0)
        ->where('sales_order_item.sales_order_id', $first_salesorderitem['sales_order_id'])
        ->get()
        ->pluck('item_name', 'sales_order_item_id');

        $item_stock_id = $this->getTypeStock($sales_order_id);
        // dd($item_stock_id);

        // $salesorderitemstocktemp=$this->getItemtemp($sales_order_id);
        //dd($salesorderitemstocktemp);
        $salesorderitemstocktemp = SalesDeliveryOrderItemStockTemporary::select('*')
        ->where('sales_order_id', $sales_order_id)
        // ->whereIn('sales_order_item_id', $salesorderitem['sales_order_item_id'])
        ->where('data_state', 0)
        ->get();

        //dd($item_stock_id);   
        $stock = collect($inv_item_stock_temporary);

        $null_warehouse_id = Session::get('warehouse_id');
        $null_sales_delivery_order_remark = Session::get('sales_delivery_order_remark');
        $null_sales_delivery_order_date = Session::get('sales_delivery_order_date');
       // dd($salesdeliveryorderelements);
        // $filteredItems = $stock->where('sales_order_item_id', $sales_order_item_id)
        //                             ->where('sales_order_id', $sales_order_id);

        // dd($inv_item_stock_temporary);

        // Session::forget('dataarrayinvitemstock');
        return view('content/SalesDeliveryOrder/FormAddSalesDeliveryOrder',compact('salesorderitemstocktemp','item_stock_id','salesdeliveryorderelements','null_warehouse_id','warehouse','null_sales_delivery_order_date','null_sales_delivery_order_remark', 'sales_order_id', 'salesorderitem', 'salesorder', 'type', 'inv_item_stock_temporary', 'stock'));
    }

    public function elements_add(Request $request){
        $salesdeliveryorderelements= Session::get('salesdeliveryorderelements');
        if(!$salesdeliveryorderelements || $salesdeliveryorderelements == ''){
            $salesdeliveryorderelements['warehouse_id'] = '';
            $salesdeliveryorderelements['sales_delivery_order_date'] = '';
            $salesdeliveryorderelements['sales_delivery_order_remark'] = '';
        }
        $salesdeliveryorderelements[$request->name] = $request->value;
        Session::put('salesdeliveryorderelements', $salesdeliveryorderelements);
    }




    public function editSalesDeliveryOrder($sales_delivery_order_id)
    {
        $salesdeliveryorderitem = SalesDeliveryOrderItem::select('sales_delivery_order_item.*')
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('data_state', 0)
        ->get();

        $salesdeliveryorder = SalesDeliveryOrder::findOrFail($sales_delivery_order_id);

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        return view('content/SalesDeliveryOrder/FormEditSalesDeliveryOrder',compact('warehouse', 'salesdeliveryorderitem', 'salesdeliveryorder', 'sales_delivery_order_id'));
    }

    public function detailSalesDeliveryOrder($sales_delivery_order_id)
    {
        $salesdeliveryorderitem = SalesDeliveryOrderItem::select('sales_delivery_order_item.*')
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('data_state', 0)
        ->get();    

        $salesdeliveryorder = SalesDeliveryOrder::findOrFail($sales_delivery_order_id);

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        return view('content/SalesDeliveryOrder/FormDetailSalesDeliveryOrder',compact('warehouse', 'salesdeliveryorderitem', 'salesdeliveryorder', 'sales_delivery_order_id'));
    }

    public function voidSalesDeliveryOrder($sales_delivery_order_id)
    {
        $salesdeliveryorderitem = SalesDeliveryOrderItem::select('sales_delivery_order_item.*')
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('data_state', 0)
        ->get();

        $salesdeliveryorder = SalesDeliveryOrder::findOrFail($sales_delivery_order_id);

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        return view('content/SalesDeliveryOrder/FormVoidSalesDeliveryOrder',compact('warehouse', 'salesdeliveryorderitem', 'salesdeliveryorder', 'sales_delivery_order_id'));
    }


    public function addArrayInvItemStock(Request $request)
    {

        $dataarrayinvitemstock = array(
            'sales_order_id'        => $request->sales_order_id,
            'sales_order_item_id'   => $request->sales_order_item_id,
            'item_stock_id'			=> $request->item_stock_id,
            'item_stock_quantity'   => $request->item_stock_quantity,
        );

        // dd($dataarrayinvitemstock['item_stock_quantity']);

        $lastdataarrayinvitemstock = Session::get('dataarrayinvitemstock');
        if($lastdataarrayinvitemstock !== null){
            array_push($lastdataarrayinvitemstock, $dataarrayinvitemstock);
            Session::put('dataarrayinvitemstock', $lastdataarrayinvitemstock);
        }else{
            $lastdataarrayinvitemstock = [];
            array_push($lastdataarrayinvitemstock, $dataarrayinvitemstock);
            Session::push('dataarrayinvitemstock', $dataarrayinvitemstock);
        }
        
        // Session::forget('dataprocessinvitemstock');
        // Session::put('dataprocessinvitemstock', $dataarrayinvitemstock);

        if($lastdataarrayinvitemstock){
            $msg = 'Tambah Sales Delivery Order Berhasil';
        return redirect()->back()->with('msg',$msg);
        }else{
            $msg = 'Tambah Sales Delivery Order Gagal';
            return redirect()->back()->with('msg',$msg);
        }

        
    }


    public function deleteArrayInvItemStock($record_id, $item_stock_id)
    {
        $arrayBaru			= array();
        $dataArrayHeader	= Session::get('dataarrayinvitemstock');
        
        foreach($dataArrayHeader as $key=>$val){
            if($key != $record_id){
                $arrayBaru[$key] = $val;
            }
        }

        Session::forget('dataarrayinvitemstock');
        Session::put('dataarrayinvitemstock', $arrayBaru);

        return redirect('/grading/add/'.$item_stock_id);
    }


    public function resetArrayInvItemStock($item_stock_id)
    {
        Session::forget('dataarrayinvitemstock');

        return redirect('/grading/add/'.$item_stock_id);
    }

    public function processAddSalesDeliveryOrder(Request $request)
    {
        $fields = $request->validate([
            'warehouse_id'                    => 'required',
            'sales_delivery_order_date'       => 'required',
        ]);
        $salesdeliveryorder = array(
            'warehouse_id'                  => $request->warehouse_id,
            'sales_order_id'                => $request->sales_order_id_1,
            'sales_delivery_order_date'     => $request->sales_delivery_order_date,
            'sales_delivery_order_remark'   => $request->sales_delivery_order_remark,
            'ppn_out_amount'                => $request->ppn_out_amount,
            'branch_id'                     => Auth::user()->branch_id,
            'created_id'                    => Auth::id(),
        );

        if(SalesDeliveryOrder::create($salesdeliveryorder)){
            $sales_delivery_order_id = SalesDeliveryOrder::select('sales_delivery_order_id')
            ->orderBy('created_at', 'DESC')
            ->first();

            $salesorderitem = SalesOrderitem::where('sales_order_id',$request->sales_order_id_1)
            ->get();
            
            $no =1;

            $dataitem = $request->all();

            // dd($dataitem);

            foreach($salesorderitem as $item){
                $item = SalesDeliveryOrderItem::create([
                    'sales_delivery_order_id'	=> $sales_delivery_order_id['sales_delivery_order_id'],
                    'sales_order_id' 			=> $dataitem['sales_order_id_'.$no],
                    'sales_order_item_id' 		=> $dataitem['sales_order_item_id_'.$no],
                    'customer_id' 				=> $dataitem['customer_id_'.$no],
                    'item_type_id' 		        => $dataitem['item_type_id_'.$no],
                    'item_unit_id' 		        => $dataitem['item_unit_id_'.$no],
                    'item_unit_price' 		    => $dataitem['item_unit_price_'.$no],
                    'subtotal_price' 		    => $dataitem['subtotal_after_discount_item_'.$no],
                    'quantity'					=> $dataitem['quantity_delivered_'.$no],		
                    'quantity_ordered'		    => $dataitem['quantity_'.$no],	
                    'created_id'                => Auth::id(),
                ]);

                $salesorder = SalesOrder::findOrFail($dataitem['sales_order_id_'.$no]);
                $salesorder->sales_delivery_order_status = 1;
                $salesorder->save();

                $salesorderitem = SalesOrderItem::findOrFail($dataitem['sales_order_item_id_'.$no]);
                $salesorderitem->sales_delivery_order_status = 1;
                $salesorderitem->quantity_delivered = $salesorderitem->quantity_delivered + $dataitem['quantity_delivered_'.$no];
                $salesorderitem->save();

                $no++;
            }
            
            
        $sdo_item_stock = SalesDeliveryOrderItemStockTemporary::where('sales_order_id', $request->sales_order_id_1)
        ->get();

        // dd($sdo_item_stock);

        foreach($sdo_item_stock as $itemstock){

            $sales_delivery_order_item_id = SalesDeliveryOrderItem::select('sales_delivery_order_item_id')
            ->where('sales_order_item_id', $itemstock['sales_order_item_id'])
            ->where('sales_order_id', $itemstock['sales_order_id'])
            ->orderBy('sales_delivery_order_item_id', 'DESC')
            ->first()
            ->sales_delivery_order_item_id;

            $data = SalesDeliveryOrderItemStock::create([
                'sales_delivery_order_id'	    => $sales_delivery_order_id['sales_delivery_order_id'],
                'sales_delivery_order_item_id'	=> $sales_delivery_order_item_id,
                'sales_order_id' 			    => $itemstock['sales_order_id'],
                'sales_order_item_id' 		    => $itemstock['sales_order_item_id'],
                'item_unit_id' 		            => $itemstock['item_unit_id'],
                'item_stock_id' 		        => $itemstock['item_stock_id'],
                'item_total_stock' 		        => $itemstock['item_stock_quantity'],
                'created_id'                    => Auth::id(),
            ]);


        }
        
        if($data){
            SalesDeliveryOrderItemStockTemporary::where('sales_order_id', $data['sales_order_id'])->delete();
        }
            
            $msg = 'Tambah Sales Delivery Order Berhasil';
            return redirect('/sales-delivery-order')->with('msg',$msg);
        }else{
            $msg = 'Tambah Sales Delivery Order Gagal';
            return redirect('/sales-delivery-order')->with('msg',$msg);
        }

    }

    public function processEditSalesDeliveryOrder(Request $request)
    {
        $salesdeliveryorder = SalesDeliveryOrder::findOrFail($request->sales_delivery_order_id);
        $salesdeliveryorder->warehouse_id = $request->warehouse_id;
        $salesdeliveryorder->sales_delivery_order_date = $request->sales_delivery_order_date;
        $salesdeliveryorder->sales_delivery_order_remark = $request->sales_delivery_order_remark;

        if($salesdeliveryorder->save()){
            $salesdeliveryorderitem = SalesDeliveryOrderItem::where('sales_delivery_order_id', $request->sales_delivery_order_id)
            ->where('data_state', 0)
            ->get();

            $no = 1;

            $dataitem = $request->all();
            foreach($salesdeliveryorderitem as $orderitem){
                $item = SalesDeliveryOrderitem::findOrFail($orderitem['sales_delivery_order_item_id']);		
                $item->quantity         = $dataitem['quantity_delivered_'.$no];		
                $item->subtotal_price   = $dataitem['quantity_delivered_'.$no]*$dataitem['item_unit_price_'.$no];
                if($item->save()){	
                    // $salesorderitem = SalesOrderItem::findOrFail($item['sales_order_item_id']);
                    // $salesorderitem->quantity_resulted = $salesorderitem->quantity_resulted - $dataitem['quantity_delivered_'.$no] + $orderitem['quantity'];
                    // $salesorderitem->quantity_delivered = $salesorderitem->quantity_delivered + $dataitem['quantity_delivered_'.$no] - $orderitem['quantity'];
                    // $salesorderitem->save();
                }

                $no++;
            }
            
            $msg = 'Edit Sales Delivery Order Berhasil';
            return redirect('/sales-delivery-order')->with('msg',$msg);
        }else{
            $msg = 'Edit Sales Delivery Order Gagal';
            return redirect('/sales-delivery-order')->with('msg',$msg);
        }

    }


    public function processVoidSalesDeliveryOrder(Request $request)
    {
        $salesdeliveryorder = SalesDeliveryOrder::findOrFail($request->sales_delivery_order_id);
        $salesdeliveryorder->data_state = 1;

        if($salesdeliveryorder->save()){
            $salesdeliveryorderitem = SalesDeliveryOrderItem::where('sales_delivery_order_id', $request->sales_delivery_order_id)
            ->where('data_state', 0)
            ->get();

            $no = 1;

            foreach($salesdeliveryorderitem as $orderitem){
                $item = SalesDeliveryOrderItem::findOrFail($orderitem['sales_delivery_order_item_id']);		
                $item->data_state = 1;
                if($item->save()){	
                    // $salesorderitem = SalesOrderItem::findOrFail($item['sales_order_item_id']);
                    // $salesorderitem->quantity_resulted = $salesorderitem->quantity_resulted + $orderitem['quantity'];
                    // $salesorderitem->quantity_delivered = $salesorderitem->quantity_delivered - $orderitem['quantity'];
                    // $salesorderitem->save();
                }

                $no++;
            }

            $salesdeliveryorderitemstock = SalesDeliveryOrderItemStock::where('sales_delivery_order_id', $request->sales_delivery_order_id)
            ->where('data_state', 0)
            ->get();

            foreach($salesdeliveryorderitemstock as $orderitemstock){
                $item2 = SalesDeliveryOrderItemStock::findOrFail($orderitemstock['sales_delivery_order_item_stock_id']);		
                $item2->data_state = 1;
                $item2->save();
            }
            
            $msg = 'Hapus Sales Delivery Order Berhasil';
            return redirect('/sales-delivery-order')->with('msg',$msg);
        }else{
            $msg = 'Hapus Sales Delivery Order Gagal';
            return redirect('/sales-delivery-order')->with('msg',$msg);
        }

    }

    public function addSalesOrderItemCheckbox(Request $request){
        $sales_order_item_id = $request->sales_order_item_id;
        
        $datacheckboxdeliveryorder = array(
            'sales_order_item_id'	=> $request->sales_order_item_id,
        );

        $lastdatacheckboxdeliveryorder = Session::get('datacheckboxdeliveryorder');
        if($lastdatacheckboxdeliveryorder !== null){
            array_push($lastdatacheckboxdeliveryorder, $datacheckboxdeliveryorder);
            Session::put('datacheckboxdeliveryorder', $lastdatacheckboxdeliveryorder);
        }else{
            $lastdatacheckboxdeliveryorder = [];
            array_push($lastdatacheckboxdeliveryorder, $datacheckboxdeliveryorder);
            Session::push('datacheckboxdeliveryorder', $datacheckboxdeliveryorder);
        }
    }

    public function getInvItemUnitName($item_unit_id){
        $unit = InvItemUnit::select('item_unit_name')
        ->where('item_unit_id', $item_unit_id)
        ->where('data_state', 0)
        ->first();

        return $unit['item_unit_name'];
    }

    public function getInvItemBatchName($item_stock_id){
        $batch = InvItemStock::select('item_batch_number')
        ->where('item_stock_id', $item_stock_id)
        ->where('data_state', 0)
        ->first();

        return $batch['item_batch_number'];
    }

    public function getSalesOrderItemStock($sales_order_item_id){
        $orderitem = SalesOrderItem::select('sales_order_item.*','sales_order.customer_id','sales_order.sales_order_no','sales_order.sales_order_date', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name, " ", core_grade.grade_name) AS item_name'))
        ->where('sales_order_item_id', $sales_order_item_id)
        ->join('inv_item_stock', 'inv_item_stock.item_stock_id', 'sales_order_item.item_stock_id')
        ->join('inv_item', 'inv_item.item_id', 'inv_item_stock.item_id')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_stock.item_category_id')
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
        ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
        ->join('sales_order', 'sales_order.sales_order_id', 'sales_order_item.sales_order_id')
        ->first();

        return $orderitem;
    }

    public function getSalesOrderItem($sales_order_item_id){
        $orderitem = SalesOrderItem::select('sales_order_item.*','sales_order.customer_id','sales_order.sales_order_no','sales_order.sales_order_date', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name) AS item_name'))
        ->where('sales_order_item_id', $sales_order_item_id)
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'sales_order_item.item_type_id')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->join('sales_order', 'sales_order.sales_order_id', 'sales_order_item.sales_order_id')
        ->first();

        return $orderitem;
    }

    public function getCorePackageName($package_id){
        $unit = CorePackage::select('package_name')
        ->where('package_id', $package_id)
        ->where('data_state', 0)
        ->first();

        return $unit['package_name'];
    }

    public function getCustomerName($customer_id){
        $unit = CoreCustomer::select('customer_name')
        ->where('customer_id', $customer_id)
        ->where('data_state', 0)
        ->first();

        return $unit['customer_name'];
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

    public function getInvWarehouseName($warehouse_id){
        $warehouse = InvWarehouse::select('warehouse_name')
        ->where('warehouse_id', $warehouse_id)
        ->where('data_state', 0)
        ->first();

        if($warehouse == null){
            return "-";
        }

        return $warehouse['warehouse_name'];
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

    public function getInvItemCategoryName($item_category_id){
        $item = InvItemCategory::select('item_category_name')
        ->where('item_category_id', $item_category_id)
        ->where('data_state', 0)
        ->first();

        if( $item == null){
            return "-";
        }

        return $item['item_category_name'];
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

    public function getCoreGradeName($item_id){
        $item = InvItem::select('core_grade.grade_name')
        ->join('core_grade','core_grade.grade_id','inv_item.grade_id')
        ->where('inv_item.item_id', $item_id)
        ->where('inv_item.data_state', 0)
        ->first();

        if( $item == null){
            return "-";
        }

        return $item['grade_name'];
    }

    public function getItemName($item_id){
        $invitem = InvItem::select('inv_item.item_id', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name, " ", core_grade.grade_name) AS item_name'))
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item.item_category_id')
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item.item_type_id')
        ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
        ->where('item_id', $item_id)
        ->where('inv_item.data_state','=',0)
        ->first();

        if( $invitem == null){
            return "-";
        }
        return $invitem['item_name'];
    }

    public function getSelectDataStock(Request $request){
       $data= '';

        $stock = InvItemStock::select('inv_item_stock.item_stock_id', DB::raw('CONCAT(inv_item_type.item_type_name, " - ", inv_item_stock.item_batch_number) AS item_name'))
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
        ->join('inv_item_unit', 'inv_item_unit.item_unit_id', 'inv_item_stock.item_unit_id')
        ->orderby('inv_item_stock.item_stock_expired_date', 'ASC')
        ->where('inv_item_stock.item_total', '>', 0)
        ->where('inv_item_stock.data_state', 0)
        ->where('inv_item_stock.item_type_id', $request->item_type_id)
        ->get();

        // dd($data);
        $data .= "<option value=''>--Choose One--</option>";
        foreach ($stock as $val){
            $data .= "<option value='$val[item_stock_id]'>$val[item_name]</option>\n";	
        }
        return $data;
    }

    public function detailArraySDOtemStock($sales_order_id, $sales_order_item_id){

        $inv_item_stock_temporary = Session::get('dataarrayinvitemstock');

        $collection = collect($inv_item_stock_temporary);
        $filteredItems = $collection->where('sales_order_item_id', $sales_order_item_id)
                                    ->where('sales_order_id', $sales_order_id);

        // dd($filteredItems);
        

        return view('content/SalesDeliveryOrder/FormDetailArrayInvItemStock',compact('collection', 'filteredItems', 'sales_order_id'));
    }

    public function getSalesOrderItemTypeName($sales_order_item_id){
        $type = SalesOrderItem::select('sales_order_item.sales_order_item_id', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name) AS item_name'))
        ->join('inv_item_type', 'sales_order_item.item_type_id', '=', 'inv_item_type.item_type_id')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->where('sales_order_item.data_state', 0)
        ->where('sales_order_item.sales_order_item_id', $sales_order_item_id)
        ->first();

        return $type['item_name'];
    }

    public function getSelectInvItemStock($item_stock_id){
        $stock = InvItemStock::select('inv_item_stock.item_stock_id', DB::raw('CONCAT(inv_item_type.item_type_name, " - ", inv_item_stock.item_batch_number, " - ", inv_item_stock.quantity_unit, " ", inv_item_unit.item_unit_name) AS item_name'))
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
        ->join('inv_item_unit', 'inv_item_unit.item_unit_id', 'inv_item_stock.item_unit_id')
        ->orderby('inv_item_stock.item_stock_expired_date', 'ASC')
        ->where('inv_item_stock.quantity_unit', '>', 0)
        ->where('inv_item_stock.data_state', 0)
        ->where('inv_item_stock.item_stock_id', $item_stock_id)
        ->first();

        return $stock['item_name'];
    }

    public function getSelectInvItemStock2($item_stock_id){
        $stock = InvItemStock::select('inv_item_stock.item_stock_id', DB::raw('CONCAT(inv_item_type.item_type_name, " - ", inv_item_stock.item_batch_number) AS item_name'))
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
        // ->join('inv_item_unit', 'inv_item_unit.item_unit_id', 'inv_item_stock.item_unit_id')
        ->orderby('inv_item_stock.item_stock_expired_date', 'ASC')
        // ->where('inv_item_stock.item_total', '>', 0)
        // ->where('inv_item_stock.data_state', 0)
        ->where('inv_item_stock.item_stock_id', $item_stock_id)
        ->first();

        return $stock['item_name'];
    }


    public function addItemStockToSalesOrder(Request $request){
        $fields = $request->validate([
            'item_stock_id'             => 'required',
            'item_unit_id'              => 'required',
            'item_stock_quantity'       => 'required',
        ]);

        $data = array(
            'item_type_id'          => $request->item_type_id,
            'sales_order_id'        => $request->sales_order_id,
            'sales_order_item_id'   => $request->sales_order_item_id,
            'item_stock_id'			=> $request->item_stock_id,
            'item_unit_id'			=> $request->item_unit_id,
            'item_stock_quantity'   => $request->item_stock_quantity,
            'created_id'            => Auth::id(),
        );
        //dd($request->all());
        if(SalesDeliveryOrderItemStockTemporary::create($data)){
            $msg = 'Pilih Stock Sales Delivery Order Berhasil';
        }else{
            $msg = 'Pilih Stock Sales Delivery Order Gagal';
        }
        return redirect()->back()->with('msg',$msg);
    }


    

    public function deleteItemStockToSalesOrder($sales_delivery_order, $sales_delivery_order_item, $sdo_item_stock_id){

        $item = SalesDeliveryOrderItemStockTemporary::findOrFail($sdo_item_stock_id);
        // dd($item);
        $item->data_state = 1;

        if($item->save()){
            $msg = 'Hapus Stock Sales Delivery Order Berhasil';
        }else{
            $msg = 'Hapus Stock Sales Delivery Order Gagal';
        }
        return redirect()->back()->with('msg',$msg);
    }

    public function deleteItemStockSalesDeliveryOrderTemp(Request $request){

        $item = SalesDeliveryOrderItemStockTemporary::where('sales_delivery_order_item_stock_temporary_id', $request->sales_delivery_order_item_stock_temporary_id);
        //dd($request->sales_delivery_order_item_stock_temporary_id);

        if($item->delete()){
            $msg = 'Hapus Stock Sales Delivery Order Berhasil';
        }else{
            $msg = 'Hapus Stock Sales Delivery Order Gagal';
        }
        return redirect()->back()->with('msg',$msg);
    }



    
    public function detailItemStockToSalesOrder($sales_order_id, $sales_order_item_id){

        $filteredItems = SalesDeliveryOrderItemStockTemporary::where('data_state', 0)
        ->where('sales_order_id', $sales_order_id)
        ->where('sales_order_item_id', $sales_order_item_id)
        ->get();

        // dd($filteredItems);
        

        return view('content/SalesDeliveryOrder/FormDetailInvItemStocktoSDO',compact('filteredItems', 'sales_order_id'));
    }

    public function detailStockSalesDeliveryOrder($sales_delivery_order_id, $sales_delivery_order_item_id){
        $detail_stock_sdo = SalesDeliveryOrderItemStock::where('data_state', 0)
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('sales_delivery_order_item_id', $sales_delivery_order_item_id)
        ->get();

        // dd($filteredItems);
        

        return view('content/SalesDeliveryOrder/FormDetailStockSalesDeliveryOrder',compact('detail_stock_sdo', 'sales_delivery_order_id'));
    }

    public function editDetailStockSalesDeliveryOrder($sales_delivery_order_id, $sales_delivery_order_item_id){
        $detail_stock_sdo = SalesDeliveryOrderItemStock::where('data_state', 0)
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('sales_delivery_order_item_id', $sales_delivery_order_item_id)
        ->get();

        $sales_order_item_id = SalesDeliveryOrderItemStock::where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('sales_delivery_order_item_id', $sales_delivery_order_item_id)
        ->first();

        // foreach($sales_order_item_id as $val){
        //     dd($val['sales_delivery_order_item_stock_id']);
        // }

        // dd($detail_stock_sdo);
        
        
        
        // dd($edit_stock_sdo);

        $type = SalesOrderItem::select('sales_order_item.sales_order_item_id', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name) AS item_name'))
        ->join('inv_item_type', 'sales_order_item.item_type_id', '=', 'inv_item_type.item_type_id')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->where('sales_order_item.data_state', 0)
        ->where('sales_order_item.sales_order_item_id', $sales_order_item_id['sales_order_item_id'])
        ->get()
        ->pluck('item_name', 'sales_order_item_id');

        

        // dd($stock);
        

        return view('content/SalesDeliveryOrder/FormEditDetailStockSalesDeliveryOrder',compact('type', 'detail_stock_sdo', 'sales_delivery_order_id', 'sales_delivery_order_item_id', 'sales_order_item_id'));

    }

    public function changeItemStockSalesDeliveryOrder(Request $request){
        $param = $request->all();

        // dd($param);

        // $item = SalesDeliveryOrderItemStock::findOrFail();
        $fields = $request->validate([
            'item_stock_id'             => 'required',
            'item_stock_quantity'       => 'required',
        ]);

        $data = array(
            'sales_delivery_order_id'        => $request->sales_delivery_order_id,
            'sales_delivery_order_item_id'   => $request->sales_delivery_order_item_id,
            'sales_order_id'                 => $request->sales_order_id,
            'sales_order_item_id'            => $request->sales_order_item_id,
            'item_unit_id'			         => $request->item_unit_id,
            'item_stock_id'			         => $request->item_stock_id,
            'item_total_stock'            => $request->item_stock_quantity,
            'created_id'                     => Auth::id(),
        );

        if(SalesDeliveryOrderItemStock::create($data)){
            $msg = 'Ubah Stock Sales Delivery Order Berhasil';
        }else{
            $msg = 'Ubah Stock Sales Delivery Order Gagal';
        }
        return redirect()->back()->with('msg',$msg);
    }

    public function deleteItemStockSalesDeliveryOrder($sales_delivery_order, $sales_delivery_order_item, $sdo_item_stock_id){

        $item = SalesDeliveryOrderItemStock::findOrFail($sdo_item_stock_id);
        // dd($item);
        $item->data_state = 1;

        if($item->save()){
            $msg = 'Hapus Stock Sales Delivery Order Berhasil';
        }else{
            $msg = 'Hapus Stock Sales Delivery Order Gagal';
        }
        return redirect()->back()->with('msg',$msg);
    }

    public function voidDetailStockSalesDeliveryOrder($sales_delivery_order_id, $sales_delivery_order_item_id){
        $detail_stock_sdo = SalesDeliveryOrderItemStock::where('data_state', 0)
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('sales_delivery_order_item_id', $sales_delivery_order_item_id)
        ->get();

        $sales_order_item_id = SalesDeliveryOrderItemStock::where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('sales_delivery_order_item_id', $sales_delivery_order_item_id)
        ->first();

        $type = SalesOrderItem::select('sales_order_item.sales_order_item_id', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name) AS item_name'))
        ->join('inv_item_type', 'sales_order_item.item_type_id', '=', 'inv_item_type.item_type_id')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->where('sales_order_item.data_state', 0)
        ->where('sales_order_item.sales_order_item_id', $sales_order_item_id['sales_order_item_id'])
        ->get()
        ->pluck('item_name', 'sales_order_item_id');

        return view('content/SalesDeliveryOrder/FormVoidDetailStockSalesDeliveryOrder',compact('type', 'detail_stock_sdo', 'sales_delivery_order_id', 'sales_delivery_order_item_id', 'sales_order_item_id'));
    }

    public function getSelectDataUnit(Request $request){
        $sales_order_item_id   = $request->sales_order_item_id;

        $inv_item_type= InvItemType::join('sales_order_item', 'sales_order_item.item_type_id', 'inv_item_type.item_type_id')
        ->where('sales_order_item.sales_order_item_id', $sales_order_item_id)
        ->first();
        
        $data= '';

        if($inv_item_type != null){
            $unit1 = InvItemType::select('inv_item_type.item_unit_1','inv_item_unit.*')
            ->join('inv_item_unit', 'inv_item_unit.item_unit_id', '=', 'inv_item_type.item_unit_1')
            ->where('inv_item_type.item_unit_1', $inv_item_type['item_unit_1'])
            // ->where('inv_item_type.item_unit_2', $inv_item_type['item_unit_2'])
            // ->where('inv_item_type.item_unit_3', $inv_item_type['item_unit_3'])
            ->first();
            
            // return $unit1;
            $unit2 = InvItemType::select('inv_item_type.item_unit_2','inv_item_unit.*')
            ->join('inv_item_unit', 'inv_item_unit.item_unit_id', '=', 'inv_item_type.item_unit_2')
            ->where('inv_item_type.item_unit_2', $inv_item_type['item_unit_2'])
            ->first();

            $unit3 = InvItemType::select('inv_item_type.item_unit_3','inv_item_unit.*')
            ->join('inv_item_unit', 'inv_item_unit.item_unit_id', '=', 'inv_item_type.item_unit_3')
            ->where('inv_item_type.item_unit_3', $inv_item_type['item_unit_3'])
            ->first();
        

        $array = [];
        if($unit1){
            array_push($array, $unit1);
        }
        if($unit2){
            array_push($array, $unit2);
        }
        if($unit3){
            array_push($array, $unit3);
        }
        // $unit = array_merge($unit1, $unit2);
        // $unit4 = array_merge($unit, $unit3);
        
        
        $data .= "<option value=''>--Choose One--</option>";
        foreach ($array as $val){
            print_r($val['item_unit_id']);
            
            $data .= "<option value='$val[item_unit_id]'>$val[item_unit_name]</option>\n";	
        }
        return $data;
        }
    }
    
}