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
use App\Models\CoreCity;
use App\Models\CoreExpedition;
use App\Models\InvItemCategory;
use App\Models\InvItemType;
use App\Models\InvItemStock;
use App\Models\SalesDeliveryOrder;
use App\Models\SalesDeliveryOrderItem;
use App\Models\SalesDeliveryNote;
use App\Models\SalesDeliveryNoteItem;
use App\Models\SalesDeliveryNoteItemStock;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\CoreCustomer;
use App\Models\InvGoodsReceivedNote;
use App\Models\InvGoodsReceivedNoteItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalesDeliveryNoteController extends Controller
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
     
        $salesdeliverynote = SalesDeliveryNote::where('data_state','=',0)
        ->where('sales_delivery_note_date', '>=', $start_date)
        ->where('sales_delivery_note_date', '<=', $end_date)
        ->get();

        return view('content/SalesDeliveryNote/ListSalesDeliveryNote', compact('salesdeliverynote', 'start_date', 'end_date'));
    }

    public function filterSalesDeliveryNote(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/sales-delivery-note');
    }

    public function search()
    {
        $salesdeliveryorder = SalesDeliveryOrder::select('sales_delivery_order.*')
        ->where('sales_delivery_order.data_state','=',0)
        ->where('sales_delivery_note_status','=',0)
        ->get();

        return view('content/SalesDeliveryNote/SearchSalesDeliveryOrder',compact('salesdeliveryorder'));
    }

    public function addSalesDeliveryNote($sales_delivery_order_id)
    {
        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        $salesdeliveryorder     = SalesDeliveryOrder::findOrFail($sales_delivery_order_id);
        
        $salesdeliveryorderitem = SalesDeliveryOrderItem::select('sales_delivery_order_item.*')
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('data_state', 0)
        ->get();

        $expedition = CoreExpedition::select('expedition_name', 'expedition_id')
        ->where('data_state', 0)
        ->pluck('expedition_name', 'expedition_id');

        $city = CoreCity::where('data_state', 0)
        ->pluck('city_name', 'city_id');

        $status = array(
            1 => 'Active',
            2 => 'Non Active',
            3 => 'All',
        );

        return view('content/SalesDeliveryNote/FormAddSalesDeliveryNote',compact('warehouse', 'salesdeliveryorder', 'salesdeliveryorderitem', 'sales_delivery_order_id', 'expedition', 'city', 'status'));
    }

    public function editSalesDeliveryNote($sales_delivery_note_id)
    {
        $salesdeliverynote = SalesDeliveryNote::findOrFail($sales_delivery_note_id);

        $salesdeliveryorderitem = SalesDeliveryOrderItem::select('sales_delivery_order_item.*')
        ->where('sales_delivery_order_id', $salesdeliverynote['sales_delivery_order_id'])
        ->where('data_state', 0)
        ->get();

        $salesdeliveryorder = SalesDeliveryOrder::findOrFail($salesdeliverynote['sales_delivery_order_id']);

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        $expedition = CoreExpedition::select('expedition_name', 'expedition_id')
        ->where('data_state', 0)
        ->pluck('expedition_name', 'expedition_id');

        $city = CoreCity::where('data_state', 0)
        ->pluck('city_name', 'city_id');

        $status = array(
            1 => 'Active',
            2 => 'Non Active',
            3 => 'All',
        );

        return view('content/SalesDeliveryNote/FormEditSalesDeliveryNote',compact('warehouse', 'expedition', 'salesdeliveryorderitem', 'salesdeliveryorder', 'sales_delivery_note_id', 'salesdeliverynote', 'city', 'status'));
    }

    public function detailSalesDeliveryNote($sales_delivery_note_id)
    {
        $salesdeliverynote = SalesDeliveryNote::findOrFail($sales_delivery_note_id);

        $salesdeliveryorderitem = SalesDeliveryOrderItem::select('sales_delivery_order_item.*')
        ->where('sales_delivery_order_id', $salesdeliverynote['sales_delivery_order_id'])
        ->where('data_state', 0)
        ->get();

        $salesdeliveryorder = SalesDeliveryOrder::findOrFail($salesdeliverynote['sales_delivery_order_id']);

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        $expedition = CoreExpedition::select('expedition_name', 'expedition_id')
        ->where('data_state', 0)
        ->pluck('expedition_name', 'expedition_id');

        return view('content/SalesDeliveryNote/FormDetailSalesDeliveryNote',compact('warehouse', 'expedition', 'salesdeliveryorderitem', 'salesdeliveryorder', 'sales_delivery_note_id', 'salesdeliverynote'));
    }

    public function voidSalesDeliveryNote($sales_delivery_note_id)
    {
        $salesdeliverynote = SalesDeliveryNote::findOrFail($sales_delivery_note_id);

        $salesdeliveryorderitem = SalesDeliveryOrderItem::select('sales_delivery_order_item.*')
        ->where('sales_delivery_order_id', $salesdeliverynote['sales_delivery_order_id'])
        ->where('data_state', 0)
        ->get();

        $salesdeliveryorder = SalesDeliveryOrder::findOrFail($salesdeliverynote['sales_delivery_order_id']);

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        $expedition = CoreExpedition::select('expedition_name', 'expedition_id')
        ->where('data_state', 0)
        ->pluck('expedition_name', 'expedition_id');

        return view('content/SalesDeliveryNote/FormVoidSalesDeliveryNote',compact('warehouse', 'expedition', 'salesdeliveryorderitem', 'salesdeliveryorder', 'sales_delivery_note_id', 'salesdeliverynote'));
    }


    public function addArrayInvItemStock(Request $request)
    {
        $dataarrayinvitemstock = array(
            'item_id'				=> $request->item_id,
            'item_quantity'			=> $request->item_quantity,
            'package_id'			=> $request->package_id,
            'package_price'			=> $request->package_price,
        );

        $lastdataarrayinvitemstock = Session::get('dataarrayinvitemstock');
        if($lastdataarrayinvitemstock !== null){
            array_push($lastdataarrayinvitemstock, $dataarrayinvitemstock);
            Session::put('dataarrayinvitemstock', $lastdataarrayinvitemstock);
        }else{
            $lastdataarrayinvitemstock = [];
            array_push($lastdataarrayinvitemstock, $dataarrayinvitemstock);
            Session::push('dataarrayinvitemstock', $dataarrayinvitemstock);
        }
        
        Session::forget('dataprocessinvitemstock');
        Session::put('dataprocessinvitemstock', $dataarrayinvitemstock);
        // return redirect('/grading/add');
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

    public function processAddSalesDeliveryNote(Request $request)
    {
        $sales_order_status_cek = 0;

        $salesdeliveryorder = SalesDeliveryOrder::findOrFail($request->sales_delivery_order_id);
        $salesdeliveryorder->sales_delivery_note_status = 1;
        $salesdeliveryorder->save();

        $salesdeliverynote = array(
            'sales_delivery_order_id'       => $request->sales_delivery_order_id,
            'sales_order_id'                => $request->sales_order_id_1,
            'warehouse_id'                  => $salesdeliveryorder['warehouse_id'],
            'expedition_id'                 => $request->expedition_id,
            'driver_name'                   => $request->driver_name,
            'fleet_police_number'           => $request->fleet_police_number,
            'sales_delivery_note_remark'    => $request->sales_delivery_note_remark,
            'sales_delivery_note_date'      => $request->sales_delivery_note_date,
            'sales_delivery_note_cost'      => $request->sales_delivery_note_cost,
            'branch_id'                     => Auth::user()->branch_id,
            'created_id'                    => Auth::id(),
        );

        if(SalesDeliveryNote::create($salesdeliverynote)){
            $salesdeliverynote = SalesDeliveryNote::select('sales_delivery_note_id', 'sales_order_id')
            ->orderBy('created_at', 'DESC')
            ->first();

            $salesdeliveryorderitem = SalesDeliveryOrderItem::select('sales_delivery_order_item.*')
            ->where('sales_delivery_order_id', $request->sales_delivery_order_id)
            ->where('data_state', 0)
            ->get();

            $no =1;

            $dataitem = $request->all();
            foreach($salesdeliveryorderitem as $item){
                $temp_quantity = $dataitem['quantity_delivered_'.$no];
                $item_stocks = '';

                $item = SalesDeliveryNoteItem::create([
                    'sales_delivery_note_id'	=> $salesdeliverynote['sales_delivery_note_id'],
                    'warehouse_id'              => $salesdeliveryorder['warehouse_id'],
                    'sales_order_id' 			=> $dataitem['sales_order_id_'.$no],
                    'sales_order_item_id' 		=> $dataitem['sales_order_item_id_'.$no],
                    'customer_id' 				=> $dataitem['customer_id_'.$no],
                    'item_id' 		            => $dataitem['item_id_'.$no],
                    'item_unit_id' 		        => $dataitem['item_unit_id_'.$no],
                    'item_unit_price' 		    => $dataitem['item_unit_price_'.$no],
                    'subtotal_price' 		    => $dataitem['subtotal_price_'.$no],
                    'item_stock_id' 		    => '',
                    'quantity'					=> $dataitem['quantity_delivered_'.$no],		
                    'quantity_ordered'		    => $dataitem['quantity_'.$no],	
                    'created_id'                => Auth::id(),
                ]);

                $salesdeliverynoteitem = SalesDeliveryNoteItem::select('sales_delivery_note_item_id')
                ->orderBy('created_at', 'DESC')
                ->first();
                
                while($temp_quantity > 0){
                    $itemstock = InvItemStock::where('item_id', $dataitem['item_id_'.$no])
                    ->orderBy('item_stock_date', 'DESC')
                    ->where('data_state', 0)
                    ->first();
                    
                    // $itemunitfirst  = InvItemUnit::where('item_unit_id', $itemstock['item_unit_id'])->first();
                    // $itemunitsecond = InvItemUnit::where('item_unit_id', $item['item_unit_id'])->first();
                    
                    // $item_total = $itemstock['item_total'] - ($temp_quantity * $itemunitsecond['item_unit_default_quantity'] / $itemunitfirst['item_unit_default_quantity']);

                    if($itemstock['item_total']>=$temp_quantity){
                        $min_quantity = $temp_quantity;

                        $itemstock->item_total = $itemstock['item_total']-$min_quantity;
                    }else{
                        $min_quantity = $itemstock['item_total'];
                        
                        $itemstock->item_total = 0;
                        $itemstock->data_state = 1;
                    }

                    $itemstock->save();

                    $item = SalesDeliveryNoteItemStock::create([
                        'sales_delivery_note_item_id'	=> $salesdeliverynoteitem['sales_delivery_note_item_id'],
                        'sales_delivery_note_id'        => $salesdeliverynote['sales_delivery_note_id'],
                        'item_stock_id' 			    => $itemstock['item_stock_id'],
                        'quantity' 		                => $min_quantity,
                        'created_id'                    => Auth::id(),
                    ]);

                    $temp_quantity = $temp_quantity - $min_quantity;
                }

                $salesorderitem = SalesOrderItem::findOrFail($dataitem['sales_order_item_id_'.$no]);
                $salesorderitem->quantity_resulted = $salesorderitem->quantity_resulted - $dataitem['quantity_delivered_'.$no];
                $salesorderitem->quantity_delivered = $salesorderitem->quantity_delivered + $dataitem['quantity_delivered_'.$no];
                $salesorderitem->save();

                if(($salesorderitem->quantity_resulted - $dataitem['quantity_delivered_'.$no]) != 0){
                    $sales_order_status_cek = 1;
                }

                $no++;
            }

            $salesorder = SalesOrder::where('sales_order_id', $salesdeliverynote['sales_order_id'])->first();

            if($sales_order_status_cek == 0){
                $salesorder->sales_order_status = 2;
            }else if($sales_order_status_cek == 1){
                $salesorder->sales_order_status = 1;
            }
            
            $msg = 'Tambah Sales Delivery Note Berhasil';
            return redirect('/sales-delivery-note')->with('msg',$msg);
        }else{
            $msg = 'Tambah Sales Delivery Note Gagal';
            return redirect('/sales-delivery-note')->with('msg',$msg);
        }

    }

    public function processEditSalesDeliveryNote(Request $request)
    {
        $salesdeliverynote = SalesDeliveryNote::findOrFail($request->sales_delivery_note_id);
        $salesdeliverynote->expedition_id               = $request->expedition_id;
        $salesdeliverynote->driver_name                 = $request->driver_name;
        $salesdeliverynote->fleet_police_number         = $request->fleet_police_number;
        $salesdeliverynote->sales_delivery_note_remark  = $request->sales_delivery_note_remark;
        $salesdeliverynote->sales_delivery_note_date    = $request->sales_delivery_note_date;
        $salesdeliverynote->sales_delivery_note_cost    = $request->sales_delivery_note_cost;

        if($salesdeliverynote->save()){
            $msg = 'Edit Sales Delivery Order Berhasil';
            return redirect('/sales-delivery-note')->with('msg',$msg);
        }else{
            $msg = 'Edit Sales Delivery Order Gagal';
            return redirect('/sales-delivery-note')->with('msg',$msg);
        }

    }


    public function processVoidSalesDeliveryNote(Request $request)
    {
        $salesdeliverynote = SalesDeliveryNote::findOrFail($request->sales_delivery_note_id);
        $salesdeliverynote->data_state = 1;

        if($salesdeliverynote->save()){
            $salesdeliveryorder = SalesDeliveryOrder::findOrFail($salesdeliverynote['sales_delivery_order_id']);
            $salesdeliveryorder->sales_delivery_note_status = 0;
            $salesdeliveryorder->save();

            $salesdeliverynoteitem = SalesDeliveryNoteItem::where('sales_delivery_note_id', $request->sales_delivery_note_id)
            ->where('data_state', 0)
            ->get();

            $no = 1;

            foreach($salesdeliverynoteitem as $orderitem){
                $item = SalesDeliveryNoteitem::findOrFail($orderitem['sales_delivery_note_item_id']);		
                $item->data_state = 1;
                if($item->save()){	
                    $salesorderitem = SalesOrderItem::findOrFail($item['sales_order_item_id']);
                    $salesorderitem->quantity_resulted = $salesorderitem->quantity_resulted + $orderitem['quantity'];
                    $salesorderitem->quantity_delivered = $salesorderitem->quantity_delivered - $orderitem['quantity'];
                    $salesorderitem->save();
                }

                $no++;
            }
            
            $msg = 'Hapus Sales Delivery Note Berhasil';
            return redirect('/sales-delivery-note')->with('msg',$msg);
        }else{
            $msg = 'Hapus Sales Delivery Note Gagal';
            return redirect('/sales-delivery-note')->with('msg',$msg);
        }

    }

    public function getInvItemUnitName($item_unit_id){
        $unit = InvItemUnit::select('item_unit_name')
        ->where('item_unit_id', $item_unit_id)
        ->where('data_state', 0)
        ->first();

        return $unit['item_unit_name'];
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
        $orderitem = SalesOrderItem::select('sales_order_item.*','sales_order.customer_id','sales_order.sales_order_no','sales_order.sales_order_date', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name, " ", core_grade.grade_name) AS item_name'))
        ->where('sales_order_item_id', $sales_order_item_id)
        ->join('inv_item', 'inv_item.item_id', 'sales_order_item.item_id')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item.item_category_id')
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item.item_type_id')
        ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
        ->join('sales_order', 'sales_order.sales_order_id', 'sales_order_item.sales_order_id')
        ->first();

        return $orderitem;
    }

    public function getSalesDeliveryOrderDate($sales_delivery_order_id){
        $unit = SalesDeliveryOrder::select('sales_delivery_order_date')
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('data_state', 0)
        ->first();

        return $unit['sales_delivery_order_date'];
    }

    public function getCorePackageName($package_id){
        $unit = CorePackage::select('package_name')
        ->where('package_id', $package_id)
        ->where('data_state', 0)
        ->first();

        return $unit['package_name'];
    }

    public function getCoreExpeditionName($expedition_id){
        $unit = CoreExpedition::select('expedition_name')
        ->where('expedition_id', $expedition_id)
        ->where('data_state', 0)
        ->first();

        return $unit['expedition_name'];
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

        return $unit['customer_name'];
    }

    public function getCustomerAddressSalesOrderId($sales_order_id){
        $unit = SalesOrder::select('core_customer.customer_address')
        ->join('core_customer', 'core_customer.customer_id', 'sales_order.customer_id')
        ->where('sales_order_id', $sales_order_id)
        ->where('sales_order.data_state', 0)
        ->first();

        return $unit['customer_address'];
    }

    public function getInvWarehouseName($warehouse_id){
        $warehouse = InvWarehouse::select('warehouse_name')
        ->where('warehouse_id', $warehouse_id)
        ->where('data_state', 0)
        ->first();

        return $warehouse['warehouse_name'];
    }

    public function getSalesOrderNo($sales_order_id){
        $salesorder = SalesOrder::select('sales_order_no')
        ->where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->first();

        return $salesorder['sales_order_no'];
    }

    public function getSalesOrderDate($sales_order_id){
        $salesorder = SalesOrder::select('sales_order_date')
        ->where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->first();

        return $salesorder['sales_order_date'];
    }

    public function getInvItemCategoryName($item_category_id){
        $item = InvItemCategory::select('item_category_name')
        ->where('item_category_id', $item_category_id)
        ->where('data_state', 0)
        ->first();

        return $item['item_category_name'];
    }

    public function getInvItemTypeName($item_type_id){
        $item = InvItemType::select('item_type_name')
        ->where('item_type_id', $item_type_id)
        ->where('data_state', 0)
        ->first();

        return $item['item_type_name'];
    }

    public function getCoreGradeName($item_id){
        $item = InvItem::select('core_grade.grade_name')
        ->join('core_grade','core_grade.grade_id','inv_item.grade_id')
        ->where('inv_item.item_id', $item_id)
        ->where('inv_item.data_state', 0)
        ->first();

        return $item['grade_name'];
    }

    public function getItemName($item_id){
        $invitem = InvItem::select('inv_item.item_id', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name, " Grade ", core_grade.grade_name) AS item_name'))
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item.item_category_id')
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item.item_type_id')
        ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
        ->where('item_id', $item_id)
        ->where('inv_item.data_state','=',0)
        ->first();

        return $invitem['item_name'];
    }

    public function getItemUnitName($sales_order_item_id){
        $invitem = SalesOrderItem::select('inv_item_unit.item_unit_name')
        ->join('inv_item_unit', 'inv_item_unit.item_unit_id', 'sales_order_item.item_unit_id')
        ->where('sales_order_item.sales_order_item_id', $sales_order_item_id)
        ->where('sales_order_item.data_state','=',0)
        ->first();

        return $invitem['item_unit_name'];
    }

    public function addCoreExpedition(Request $request){
        $expedition_code             = $request->expedition_code;
        $expedition_name             = $request->expedition_name;
        $expedition_address          = $request->expedition_address;
        $expedition_route            = $request->expedition_route;
        $expedition_city             = $request->expedition_city;
        $expedition_home_phone       = $request->expedition_home_phone;
        $expedition_mobile_phone1    = $request->expedition_mobile_phone1;
        $expedition_mobile_phone2    = $request->expedition_mobile_phone2;
        $expedition_fax_number       = $request->expedition_fax_number;
        $expedition_email            = $request->expedition_email;
        $expedition_person_in_charge = $request->expedition_person_in_charge;
        $expedition_status           = $request->expedition_status;
        $expedition_remark           = $request->expedition_remark;
        $data='';
        
        $invitemunit = CoreExpedition::create([  
            'expedition_code'               => $expedition_code,
            'expedition_name'               => $expedition_name,
            'expedition_address'            => $expedition_address,
            'expedition_route'              => $expedition_route,
            'expedition_city'               => $expedition_city,
            'expedition_home_phone'         => $expedition_home_phone,
            'expedition_mobile_phone1'      => $expedition_mobile_phone1,
            'expedition_mobile_phone2'      => $expedition_mobile_phone2,
            'expedition_fax_number'         => $expedition_fax_number,
            'expedition_email'              => $expedition_email,
            'expedition_person_in_charge'   => $expedition_person_in_charge,
            'expedition_status'             => $expedition_status,
            'expedition_remark'             => $expedition_remark,
            'created_id'                    => Auth::id()
        ]);

        $coreexpedition = CoreExpedition::where('data_state', 0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($coreexpedition as $mp){
            $data .= "<option value='$mp[expedition_id]'>$mp[expedition_name]</option>\n";	
        }

        return $data;
    }
    
    public function processPrintingSalesDeliveryNote ($sales_delivery_note_id)
    {
        $salesdeliverynote      = SalesDeliveryNote::findOrFail($sales_delivery_note_id);
        $salesdeliverynoteitem  = SalesDeliveryNoteItem::select('sales_delivery_note_item.*')
        ->where('sales_delivery_note_id', $sales_delivery_note_id)
        ->where('data_state', 0)
        ->get();

        $pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(10, 10, 10, 10); // put space of 10 on top

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 8);$tbl = "";
        if(trim($salesdeliverynote['customer_tax_no']) != ''){
            $tbl = "
                <table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"0\">
                    <tr>
                        <td style=\"text-align:center;width:30%\">
                            <div style=\"font-size:26px\"><b>SURAT JALAN</b></div>
                        </td>
                        <td style=\"text-align:center;width:30%;\">
                            <table id=\"items\" width=\"100%\" cellpadding=\"1\">
                                <tr>
                                    <td style=\"text-align:left;width:25%\">
                                        <div style=\"font-size:15px\">
                                            NO. 
                                        </div>
                                    </td>
                                    <td style=\"text-align:left;width:70%\">
                                        <div style=\"font-size:15px\">
                                             : ".$salesdeliverynote['sales_delivery_note_no']."
                                         </div>
                                     </td>
                                 </tr>
                                 <tr>
                                    <td style=\"text-align:left;width:30%\">
                                        <div style=\"font-size:15px\">
                                            TGL
                                        </div>
                                    </td>
                                    <td style=\"text-align:left;width:70%\">
                                        <div style=\"font-size:15px\">
                                             : ".date('d M Y', strtotime($salesdeliverynote['sales_delivery_note_date']))."
                                         </div>
                                     </td>
                                 </tr>
                             </table>
                        </td>
                        <td style=\"text-align:left; height:20%;\">".$preference_company['company_name']."
                             <br>".$preference_company['company_address']."<br>Telp./Fax :
                             ".$preference_company['company_home_phone1']."<br>N.P.W.P :
                             ".$preference_company['company_tax_number']."
                         </td>
                     </tr>
                 </table>
            ";
        } else {
            $tbl = "
                <table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" >
                    <tr>
                        <td style=\"text-align:center;width:30%\">
                            <div style=\"font-size:26px\"><b>SURAT JALAN</b></div>
                        </td>
                        <td style=\"text-align:center;width:40%\">
                            <table id=\"items\" width=\"100%\" cellpadding=\"0\">
                                <tr>
                                    <td style=\"text-align:left;width:30%\">
                                        <div style=\"font-size:12px\">
                                            NO. 
                                        </div>
                                    </td>
                                    <td style=\"text-align:left;width:70%\">
                                        <div style=\"font-size:12px\">
                                             : ".$salesdeliverynote['sales_delivery_note_no']."
                                         </div>
                                     </td>
                                 </tr>
                                 <tr>
                                    <td style=\"text-align:left;width:30%\">
                                        <div style=\"font-size:12px\">
                                            TGL
                                        </div>
                                    </td>
                                    <td style=\"text-align:left;width:70%\">
                                        <div style=\"font-size:12px\">
                                             : ".date('d M Y', strtotime($salesdeliverynote['sales_delivery_note_date']))."
                                         </div>
                                     </td>
                                 </tr>
                             </table>
                        </td>
                        <td style=\"text-align:left;width:30%; height:20%;\">
                             
                         </td>
                     </tr>
                 </table>
            ";
        }

        $pdf::writeHTML($tbl, true, false, false, false, '');

        $tbl = "
        <table cellspacing=\"0\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td style=\"text-align:left;width:15%\">
                    Expedisi
                </td>
                <td style=\"text-align:left;width:50%\">
                    : ".$this->getCoreExpeditionName($salesdeliverynote['expedition_id'])."
                </td>
                <td style=\"text-align:left;width:15%\">
                    Driver
                </td>
                <td style=\"text-align:left;width:20%\">
                    : ".$salesdeliverynote['driver_name']."
                </td>

            </tr>
            <tr>
                
                <td style=\"text-align:left;width:15%\">
                    No. SO
                </td>
                <td style=\"text-align:left;width:50%\">
                    : ".$this->getSalesOrderNo($salesdeliverynote['sales_order_id'])."
                </td>
                <td style=\"text-align:left;width:15%\">
                    No Polisi
                </td>
                <td style=\"text-align:left;width:20%\">
                    : ".$salesdeliverynote['fleet_police_number']."
                </td>

            </tr>
            <br>
            <tr>
                <td style=\"text-align:left;width:65%\">
                    <b>Kepada Yth:</b>
                </td>
            </tr>
            <tr>
                <td style=\"text-align:left;width:100%\">
                    ".$this->getCustomerNameSalesOrderId($salesdeliverynote['sales_order_id'])."
                </td>

            </tr>
            <tr>
                <td style=\"text-align:left;width:100%\">
                    <div style=\"font-size:14px\">
                        ".$this->getCustomerAddressSalesOrderId($salesdeliverynote['sales_order_id'])."
                    </div>
                </td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl, true, false, false, false, '');

        $tbl1 = "
        Mohon diterima dengan baik barang - barang tersebut dibawah : <br>
        <table cellspacing=\"0\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\">			        
            <tr>
                <th style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;border-top: 1px solid black;\" width=\"5%\"><div style=\"font-size:14px\">No</div></th>
                <th style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;border-top: 1px solid black;\" width=\"35%\"><div style=\"font-size:14px\">Item Name</div></th> 
                <th style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;border-top: 1px solid black;\" width=\"20%\"><div style=\"font-size:14px\">Batch Number</div></th> 
                <th style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;border-top: 1px solid black;\" width=\"20%\"><div style=\"font-size:14px\">Satuan Unit</div></th> 
                <th style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;border-top: 1px solid black;border-right: 1px solid black;\" width=\"20%\"><div style=\"font-size:14px\">Qty</div></th>
            </tr>
            ";
        $no = 1;
        $totalqty = 0;
        $totalamount = 0;
        $tbl2 = "";
            foreach ($salesdeliverynoteitem as $key => $val) {
                $tbl2 .= "
                    <tr>
                        <td style=\"text-align:center;border-left: 1px solid black;\"><div style=\"font-size:14px\">$no</div></td>
                        <td style=\"text-align:left;border-left: 1px solid black;\"><div style=\"font-size:14px\">&nbsp;".$this->getItemName($val['item_id'])."</div></td>
                        <td style=\"text-align:right;border-left: 1px solid black;\"><div style=\"font-size:14px\">".$val['item_batch_number']." &nbsp;</div></td>
                        <td style=\"text-align:right;border-left: 1px solid black;\"><div style=\"font-size:14px\">".$this->getItemUnitName($val['sales_order_item_id'])." &nbsp;</div></td>
                        <td style=\"text-align:right;border-left: 1px solid black;border-right: 1px solid black;\"><div style=\"font-size:14px\">".$val['quantity']." &nbsp;</div></td>
                    </tr>						
                ";
                $totalqty += $val['quantity'];
                $no++;
            }
        
        $tbl3 = "
            <tr>
                <td style=\"text-align:center;border-left: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-right: 1px solid black;\"></td>

            </tr>
            <tr>
                <td style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;\"></td>
            </tr>";	

        $tbl4 = "
            <tr>
                <td style=\"text-align:left;\" colspan=\"3\">Ket : ".$salesdeliverynote['sales_delivery_note_remark']."</td>
                
                <td style=\"text-align:left;border-bottom: 1px solid black;border-left: 1px solid black;\" ><div style=\"font-size:14px\">&nbsp; Total </div></td>
                <td style=\"text-align:right;border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;\" ><div style=\"font-size:14px\">".$totalqty." &nbsp;</div></td>

            </tr>
                    
        </table>";

        $pdf::writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

        $tbl7 = "
        <br><br>
            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">

                <tr>
                    <th style=\"text-align:center;width:30%;\">Penerima</th>
                    <th style=\"text-align:center;width:30%;\">Gudang</th>
                    <th style=\"text-align:center;width:30%;\">Pengirim</th>
                </tr>
                <tr>
                    <td style=\"height: 40px !important;\"></td>
                    <td style=\"height: 40px !important;\"></td>
                    <td style=\"height: 40px !important;\"></td>
                </tr>
                <tr>
                    <td>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) </td>
                    <td>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
                    <td>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) </td>
                </tr>
            </table>
        ";

        $pdf::writeHTML($tbl7, true, false, false, false, '');

        // ob_clean();

        $filename = 'Surat Jalan'.$salesdeliverynote['sales_delivery_note_no'].'.pdf';
        $pdf::Output($filename, 'I');
    }
    
}
