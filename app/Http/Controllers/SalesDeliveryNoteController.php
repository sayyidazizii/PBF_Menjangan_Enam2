<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicController;
use App\Providers\RouteServiceProvider;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\CoreSupplier;
use App\Models\CoreGrade;
use App\Models\CorePackage;
use App\Models\CoreCity;
use App\Models\CoreCustomer;
use App\Models\CoreExpedition;
use App\Models\InvWarehouse;
use App\Models\InvGoodsReceivedNote;
use App\Models\InvGoodsReceivedNoteItem;
use App\Models\InvItem;
use App\Models\InvItemUnit;
use App\Models\InvItemCategory;
use App\Models\InvItemType;
use App\Models\InvItemStock;
use App\Models\InvItemStockCard;
use App\Models\PurchaseOrder;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\PurchaseOrderItem;
use App\Models\SalesDeliveryOrder;
use App\Models\SalesDeliveryOrderItem;
use App\Models\SalesDeliveryNote;
use App\Models\SalesDeliveryNoteItem;
use App\Models\SalesDeliveryNoteItemStock;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesDeliveryOrderItemStock;
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
        Session::forget('salesdeliveryordernoteelements');

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

        Session::forget('salesdeliveryordernoteelements');

        return view('content/SalesDeliveryNote/SearchSalesDeliveryOrder',compact('salesdeliveryorder'));
    }


    public function getPpnOut($sales_delivery_order_id){

        $sales_delivery_order = SalesDeliveryOrder::select('ppn_out_amount')
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('data_state', 0)
        ->first();

        return $sales_delivery_order['ppn_out_amount'];
    }

    public function getPOnum($sales_order_id){

        $poNum = SalesOrder::select('purchase_order_no')
        ->where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->first();

        return $poNum['purchase_order_no'];
    }

    public function addSalesDeliveryNote($sales_delivery_order_id)
    {

        $salesdeliveryordernoteelements  = Session::get('salesdeliveryordernoteelements');

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
        ->where('data_state', 0)
        ->pluck('warehouse_name', 'warehouse_id');

        $salesdeliveryorder     = SalesDeliveryOrder::findOrFail($sales_delivery_order_id);
        
        $salesdeliveryorderitem = SalesDeliveryOrderItem::select('sales_delivery_order_item.*','sales_delivery_order_item_stock.*')
        ->rightJoin('sales_delivery_order_item_stock', 'sales_delivery_order_item_stock.sales_delivery_order_id', '=', 'sales_delivery_order_item_stock.sales_delivery_order_id')
        ->where('sales_delivery_order_item.sales_delivery_order_id', $sales_delivery_order_id)
        ->where('sales_delivery_order_item_stock.sales_delivery_order_id', $sales_delivery_order_id)
        ->where('sales_delivery_order_item.data_state', 0)
        ->groupby('sales_delivery_order_item.sales_delivery_order_id',
                  'sales_delivery_order_item_stock.sales_delivery_order_item_id',
                  'sales_delivery_order_item_stock.item_stock_id',
                  'sales_delivery_order_item_stock.item_total_stock')
                    ->get();
                 

        $salesdeliverynoteitem_view  = SalesDeliveryOrderItem::select('sales_delivery_order_item.*')
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('data_state', 0)
        ->get();
    
                    // dd($salesdeliveryorderitem);
        //  return json_encode($salesdeliveryorderitem);
        //  exit;
        $expedition = CoreExpedition::select('expedition_name', 'expedition_id')
        ->where('data_state', 0)
        ->pluck('expedition_name', 'expedition_id');

        $city = CoreCity::where('data_state', 0)
        ->pluck('city_name', 'city_id');

        $null_warehouse_id = Session::get('warehouse_id');

        $status = array(
            1 => 'Active',
            2 => 'Non Active',
            3 => 'All',
        );

        return view('content/SalesDeliveryNote/FormAddSalesDeliveryNote',compact('warehouse','null_warehouse_id','salesdeliveryordernoteelements', 'salesdeliveryorder', 'salesdeliveryorderitem','salesdeliverynoteitem_view', 'sales_delivery_order_id', 'expedition', 'city', 'status'));
    }


    public function getdataItemStok($sales_delivery_order_item_id){
        $salesdeliveryorderitemstok = SalesDeliveryOrderItemStock::select('item_total_stock')
        ->where('sales_delivery_order_item_id', $sales_delivery_order_item_id)
        ->where('data_state', 0)
        ->get();   

        return $salesdeliveryorderitemstok[0]['item_total_stock'];
    }


    public function getdataItemStokNote($sales_delivery_order_item_id){
        $salesdeliveryorderitemstok = SalesDeliveryOrderItemStock::select('*')
        ->where('sales_delivery_order_item_id', $sales_delivery_order_item_id)
        ->where('data_state', 0)
        ->get();   

        return $salesdeliveryorderitemstok;
    }


    public function getItemUnitprice($sales_delivery_order_item_id){
        $item = SalesDeliveryOrderItem::select('item_unit_price')
        ->where('sales_delivery_order_item_id', $sales_delivery_order_item_id)
        ->where('data_state', 0)
        ->first();

        return $item['item_unit_price'];
    }

    public function getItemBatchNumber($item_stock_id){
        $item = InvItemStock::select('item_batch_number')
        ->where('item_stock_id', $item_stock_id)
        ->where('data_state', 0)
        ->first();

        return $item['item_batch_number']?? '';
    }

    public function getItemIdNotenow($sales_order_item_id){
        $item = SalesDeliveryNoteItem::select('sales_delivery_note_item_id')
        ->where('sales_order_item_id',$sales_order_item_id)
        // ->where('created_at',  \Carbon\Carbon::now())
        ->where('data_state', 0)
        ->get();

        return $item;
    }


    public function elements_add(Request $request){
        $salesdeliveryordernoteelements= Session::get('salesdeliveryordernoteelements');
        if(!$salesdeliveryordernoteelements || $salesdeliveryordernoteelements == ''){
            $salesdeliveryordernoteelements['warehouse_id'] = '';
            // $salesdeliveryordernoteelements['sales_delivery_order_date'] = '';
            // $salesdeliveryordernoteelements['sales_delivery_order_remark'] = '';
        }
        $salesdeliveryordernoteelements[$request->name] = $request->value;
        Session::put('salesdeliveryordernoteelements', $salesdeliveryordernoteelements);
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
            'sales_order_id'                => $request->sales_order_id__1,
            'customer_id' 				    => $request->customer_id,
            'warehouse_id'                  => 8,
            'expedition_id'                 => $request->expedition_id,
            'driver_name'                   => $request->driver_name,
            'fleet_police_number'           => $request->fleet_police_number,
            'sales_delivery_note_remark'    => $request->sales_delivery_note_remark,
            'sales_delivery_note_date'      => $request->sales_delivery_note_date,
            'expedition_receipt_no'         => $request->expedition_receipt_no,
            'ppn_out_amount'                => $request->ppn_out_amount,
            'branch_id'                     => Auth::user()->branch_id,
            'created_id'                    => Auth::id(),
        );
    //    dd([$request->all(),$salesdeliverynote]);
        if(SalesDeliveryNote::create($salesdeliverynote)){
            $salesdeliverynoteid = SalesDeliveryNote::select('sales_delivery_note_id', 'sales_order_id', 'sales_delivery_note_no')
            ->orderBy('created_at', 'DESC')
            ->first();

            $salesdeliveryorderitem = SalesDeliveryOrderItem::select('sales_delivery_order_item.*', 'inv_item_type.*')
            ->join('inv_item_type', 'inv_item_type.item_type_id', '=', 'sales_delivery_order_item.item_type_id')
            ->where('sales_delivery_order_item.sales_delivery_order_id', $request->sales_delivery_order_id)
            ->where('sales_delivery_order_item.data_state', 0)
            ->orderBy('sales_delivery_order_item.sales_delivery_order_item_id', 'DESC')
            ->get();

            $no =1;
            
            // dd($salesdeliveryorderitem);
            $dataitem = $request->all();
            //dd($dataitem);
            foreach($salesdeliveryorderitem as $item){  

                // dd($item);
                $temp_quantity = $dataitem['quantity_delivered_'.$no];
                $item_stocks = '';

                $item_unit_id_unit = $item['item_unit_1'];
                $quantity_unit = $dataitem['quantity_'.$no] * $item['item_quantity_default_1'];
                $default_quantity = $item['item_quantity_default_1'];
                $item_weight = $dataitem['quantity_'.$no] * $item['item_weight_1'];
                if($dataitem['item_unit_id_'.$no] == $item['item_unit_1']){
                    $quantity_unit = $dataitem['quantity_'.$no] * $item['item_quantity_default_1'];
                    $default_quantity = $item['item_quantity_default_1'];
                    $item_weight = $dataitem['quantity_'.$no] * $item['item_weight_1'];
                }
                if($dataitem['item_unit_id_'.$no] == $item['item_unit_2']){
                    $quantity_unit = $dataitem['quantity_'.$no] * $item['item_quantity_default_2'];
                    $default_quantity = $item['item_quantity_default_2'];
                    $item_weight = $dataitem['quantity_'.$no] * $item['item_weight_2'];
                }
                if($dataitem['item_unit_id_'.$no] == $item['item_unit_3']){
                    $quantity_unit = $dataitem['quantity_'.$no] * $item['item_quantity_default_3'];
                    $default_quantity = $item['item_quantity_default_3'];
                    $item_weight = $dataitem['quantity_'.$no] * $item['item_weight_3'];
                }

                $data = SalesDeliveryNoteItem::create([
                    'sales_delivery_note_id'	    => $salesdeliverynoteid['sales_delivery_note_id'],
                    'warehouse_id'                  => $request->warehouse_id,
                    'sales_order_id' 			    => $dataitem['sales_order_id__'.$no],
                    'sales_order_item_id' 		    => $dataitem['sales_order_item_id__'.$no],
                    'sales_delivery_order_id' 	    => $dataitem['sales_delivery_order_id__'.$no],
                    'sales_delivery_order_item_id'  => $dataitem['sales_delivery_order_item_id__'.$no],
                    'customer_id' 				    => $dataitem['customer_id'],
                    'item_id' 		                => $dataitem['item_id_'.$no],
                    'item_type_id' 		            => $dataitem['item_type_id_'.$no],
                    'item_unit_id' 		            => $dataitem['item_unit_id_'.$no],
                    'item_unit_id_unit' 		    => $item_unit_id_unit,
                    'quantity_unit' 		        => $dataitem['quantity_delivered_'.$no],
                    'item_default_quantity_unit'    => $default_quantity,
                    'item_weight_unit' 		        => $item_weight,
                    'item_unit_price' 		        => $dataitem['item_unit_price_'.$no],
                    'subtotal_price' 		        => $dataitem['subtotal_price_'.$no],
                    'item_stock_id' 		        => '',
                    'quantity'					    => $dataitem['quantity_delivered_'.$no],		
                    'quantity_ordered'		        => $dataitem['quantity_'.$no],	
                    'created_id'                    => Auth::id(),
                ]);


                $salesdeliverynoteitem = SalesDeliveryNoteItem::select('sales_delivery_note_item.*')
                // ->where('sales_delivery_note_item.sales_order_id', $data->sales_order_id)
                // ->where('sales_delivery_note_item.sales_order_item_id', $data->sales_order_item_id)
                ->orderBy('created_at', 'DESC')
                ->first();

                
                $sdo_item_stock = SalesDeliveryOrderItemStock::select('sales_delivery_order_item_stock.*')
                ->join('inv_item_stock', 'inv_item_stock.item_stock_id', '=', 'sales_delivery_order_item_stock.item_stock_id')
                // ->join('inv_item_type', 'inv_item_stock.item_type_id', '=', 'inv_item_type.item_type_id')
                ->where('sales_delivery_order_item_stock.sales_order_id', $salesdeliverynoteitem['sales_order_id'])
                ->where('sales_delivery_order_item_stock.data_state', 0)
                ->get();


                $no++;
                }
               
              


                $itemNoteNew = SalesDeliveryNoteItem::select('*')
                ->where('created_at', \Carbon\Carbon::now())
                ->where('sales_delivery_note_id', $salesdeliverynoteid['sales_delivery_note_id'])
                ->orderBy('created_at', 'DESC')
                ->get();

                $dataItemNote = array("sales_delivery_note_item_id");
                $rows = 1;
                foreach ($itemNoteNew as $data_item) {
                    $dataItemNote[] = array(
                      'sales_delivery_note_item_id' => $data_item->sales_delivery_note_item_id,
                    );
                   
                  
                }


                //pengurangan Stock
                $total_no = $request->total_no;
                for ($i = 1; $i <= $total_no; $i++) {

                    $itemstock     = InvItemStock::findOrFail($dataitem['item_stock_id_'.$i]);
                    $itemstock->quantity_unit = (int)$itemstock['quantity_unit'] - (int)$dataitem['quantity_'.$i];
                    $itemstock->save();

                   
    
    
                        $inv_stock_note = array(
                            'goods_received_note_id'            =>   $itemstock['goods_received_note_id'],
                            'goods_received_note_item_id'       =>   $itemstock['goods_received_note_item_id'],
                            'item_stock_date'                   =>  \Carbon\Carbon::now(), # new \Datetime()
                            'item_stock_expired_date'           =>   $itemstock['item_stock_expired_date'],
                            'item_batch_number'                 =>   $itemstock['item_batch_number'],
                            'purchase_order_item_id'            =>   $itemstock['purchase_order_item_id'],
                            'warehouse_id'                      =>   8,
                            'item_category_id'                  =>   $itemstock['item_category_id'],
                            'item_type_id'                      =>   $dataitem['item_type_id_'.$i],
                            'item_unit_id'                      =>   $dataitem['item_unit_id_'.$i],
                            'item_unit_cost'                    =>   $itemstock['item_unit_cost'],
                            'item_unit_price'                   =>   $dataitem['item_unit_price_'.$i],
                            'item_total'                        =>   $itemstock['item_total'],
                            'item_unit_id_default'              =>   $itemstock['item_unit_id_default'],
                            'item_default_quantity_unit'        =>   $itemstock['item_default_quantity_unit'],
                            'quantity_unit'                     =>   (int)$dataitem['quantity_'.$i],
                            'item_weight_default'               =>   $itemstock['item_weight_default'],
                            'item_weight_unit'                  =>   $itemstock['item_weight_unit'],
                            'package_id'                        =>   $itemstock['package_id'],
                            'package_total'                     =>   $itemstock['package_total'],
                            'package_unit_id'                   =>   $itemstock['package_unit_id'],
                            'package_price'                     =>   $itemstock['package_price'],
                            'data_state'                        =>   $itemstock['data_state'],
                            'created_id'                        =>   $itemstock['created_id'],
                            'created_at'                        =>  \Carbon\Carbon::now(), # new \Datetime()
                            'updated_at'                        =>   $itemstock['updated_at'],
                            'purchase_order_no'                 =>   $dataitem['purchase_order_no'],
                            
                        );    
                        InvItemStock::create($inv_stock_note);
                    // }
                    
    
                    $itemstockNew = InvItemStock::select('*')
                        ->where('created_at', \Carbon\Carbon::now())
                        ->where('item_type_id', $dataitem['item_type_id_'.$i])
                        ->where('item_category_id',$inv_stock_note['item_category_id'])
                        ->where('item_unit_id',$dataitem['item_unit_id_'.$i])
                        ->where('quantity_unit', (int)$dataitem['quantity_'.$i])
                        ->where('item_batch_number', $dataitem['item_batch_number_'.$i])
                        ->where('warehouse_id',8)
                        ->orderBy('created_at', 'DESC')
                        ->get();
    
                    $dataStock = array("item_stock_id");
                    $row = 1;
                    foreach ($itemstockNew as $data_item) {
                        $dataStock[] = array(
                          'item_stock_id' => $data_item->item_stock_id,
                        );
                   
                            $nos = 1;
                            foreach($sdo_item_stock as $val){
                
                                $item_unit_id_unit = $val['item_unit_1'];
                
                                if($val['item_unit_id'] == $val['item_unit_1']){
                                    $quantity_unit = $val['item_total_stock'] * $val['item_quantity_default_1'];
                                    $default_quantity = $val['item_quantity_default_1'];
                                    $item_weight = $val['item_total_stock'] * $val['item_weight_1'];
                                    $item_weight_default = $val['item_weight_1'];
                                    // dd($item_unit_id_unit);
                                }
                                if($val['item_unit_id'] == $val['item_unit_2']){
                                    $quantity_unit = $val['item_total_stock'] * $val['item_quantity_default_2'];
                                    $default_quantity = $val['item_quantity_default_2'];
                                    $item_weight = $val['item_total_stock'] * $val['item_weight_2'];
                                    $item_weight_default = $val['item_weight_2'];
                                }
                                if($val['item_unit_id'] == $val['item_unit_3']){
                                    $quantity_unit = $val['item_total_stock'] * $val['item_quantity_default_3'];
                                    $default_quantity = $val['item_quantity_default_3'];
                                    $item_weight = $val['item_total_stock'] * $val['item_weight_3'];
                                    $item_weight_default = $val['item_weight_2'];
                                }

                                
                                    $dataitemnotestock =array(
                                        'sales_delivery_note_id'	                => $salesdeliverynoteid['sales_delivery_note_id'],
                                        'sales_delivery_note_item_id'	            => $data['sales_delivery_note_item_id'],
                                        'sales_delivery_order_id'	                => $dataitem['sales_delivery_order_id_'.$i],
                                        'sales_delivery_order_item_id'	            => $dataitem['sales_delivery_order_item_id_'.$i],
                                        'sales_delivery_order_item_stock_id'	    => $dataitem['sales_delivery_order_item_stock_id_'.$i],
                                        'sales_order_id' 			                => $dataitem['sales_order_id_'.$i],
                                        'sales_order_item_id' 		                => $dataitem['sales_order_item_id_'.$i],
                                        // 'item_category_id' 		                => $val['item_category_id'.$i],
                                        'item_type_id' 		                        => $dataitem['item_type_id_'.$i],
                                        'item_unit_id' 		                        => $dataitem['item_unit_id_'.$i],
                                        'item_unit_id_unit' 		                => $item_unit_id_unit,
                                        'quantity_unit' 		                    => $dataitem['quantity_'.$i],
                                        'item_default_quantity_unit'                => $default_quantity,
                                        'item_weight_unit' 		                    => $item_weight,
                                        // 'item_weight_default' 		            => $item_weight_default,
                                        'item_stock_id' 		                    => $dataStock[$row]['item_stock_id'],
                                        'quantity'					                => $quantity_unit,
                                        'warehouse_id'                              => $request->warehouse_id,
                                        'created_id'                                => Auth::id(),
                                    );  

                                }
                                        
                            }
    
                            SalesDeliveryNoteItemStock::create($dataitemnotestock);
                            $row++; 
                            
                }

                

                // UPDATE NOTE ITEM ID 
                DB::table('sales_delivery_note_item_stock as a')
                ->join('sales_delivery_note_item as c', 'a.sales_delivery_order_item_id', '=', 'c.sales_delivery_order_item_id')
                ->update([ 'a.sales_delivery_note_item_id' => DB::raw("`c`.`sales_delivery_note_item_id`") ]);


                  // UPDATE kartu stock
                  DB::table('inv_item_stock_card as a')
                  ->join('sales_delivery_order_item_stock as b', 'a.item_stock_id', '=', 'b.item_stock_id')
                  ->where('b.sales_delivery_order_id',$request->sales_delivery_order_id)
                  ->update([
                     'a.item_stock_card_in' => DB::raw("`a`.`item_stock_card_in` - `b`.`item_total_stock`") ,
                     'a.item_stock_card_out' => DB::raw("`a`.`item_stock_card_out` + `b`.`item_total_stock`") 
                    ]);



                // $total_no2 = $request->total_no;
                // for ($i = 1; $i <= $total_no2; $i++) {
                //     $itemstockcard     = InvItemStockCard::findOrFail($dataitem['item_stock_id_'.$i]);
                //     // $itemstockcard->item_stock_card_in = (int)$itemstockcard['item_stock_card_in'] - (int)$dataitem['quantity_'.$i];
                //     // $itemstockcard->item_stock_card_out = (int)$itemstockcard['item_stock_card_out'] + (int)$dataitem['quantity_'.$i];
                //     // $itemstockcard->save();
                // }
                    
            //dd($itemstockcard);
            //  exit;
//dd($dataitemnotestock);
            
           

            $salesorder = SalesOrder::where('sales_order_id', $salesdeliverynote['sales_order_id'])
            ->first();

            if($sales_order_status_cek == 0){
                $salesorder->sales_order_status = 2;
            }else if($sales_order_status_cek == 1){
                $salesorder->sales_order_status = 1;
            }
            $salesorder->save();
            
//----------------------------------------------------------Journal Voucher-------------------------------------------------------------------//

       // if($purchaseorder['purchase_order_type_id'] == 2){
            
            // $preferencecompany 			= PreferenceCompany::first();
        
            // $transaction_module_code 	= "SDN";
    
            // $transactionmodule 		    = PreferenceTransactionModule::where('transaction_module_code', $transaction_module_code)
            // ->first();
    
            // $transaction_module_id 		= $transactionmodule['transaction_module_id'];

            // $journal_voucher_period 	= date("Ym", strtotime($salesdeliverynote['sales_delivery_note_date']));

            // $data_journal = array(
            //     'branch_id'						=> 1,
            //     'journal_voucher_period' 		=> $journal_voucher_period,
            //     'journal_voucher_date'			=> $salesdeliverynote['sales_delivery_note_date'],
            //     'journal_voucher_title'			=> 'Surat Jalan '.$salesdeliverynoteid['sales_delivery_note_no'],
            //     'journal_voucher_no'			=> $salesdeliverynoteid['sales_delivery_note_no'],
            //     'journal_voucher_description'	=> $salesdeliverynote['sales_delivery_note_remark'],
            //     'transaction_module_id'			=> $transaction_module_id,
            //     'transaction_module_code'		=> $transaction_module_code,
            //     'transaction_journal_id' 		=> $salesdeliverynoteid['sales_delivery_note_id'],
            //     'transaction_journal_no' 		=> $salesdeliverynoteid['sales_delivery_note_no'],
            //     'created_id' 					=> Auth::id(),
            // );
            
            // AcctJournalVoucher::create($data_journal);

            // $journalvoucher = AcctJournalVoucher::where('created_id', Auth::id())
            // ->orderBy('journal_voucher_id', 'DESC')
            // ->first();

            // $journal_voucher_id 	= $journalvoucher['journal_voucher_id'];

            // if($request->sales_delivery_note_cost != '' || $request->sales_delivery_note_cost != 0) {

            //     $account 		= AcctAccount::where('account_id', $preferencecompany['account_delivery_id'])
            //     ->where('data_state', 0)
            //     ->first();

            //     $account_id_default_status 		= $account['account_default_status'];

            //     $data_debit = array (
            //         'journal_voucher_id'			=> $journal_voucher_id,
            //         'account_id'					=> $preferencecompany['account_delivery_id'],
            //         'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
            //         'journal_voucher_amount'		=> ABS($salesdeliverynote['sales_delivery_note_cost']),
            //         'journal_voucher_debit_amount'	=> ABS($salesdeliverynote['sales_delivery_note_cost']),
            //         'account_id_default_status'		=> $account_id_default_status,
            //         'account_id_status'				=> 1,
            //     );

            //     AcctJournalVoucherItem::create($data_debit);

            //     $account 		= AcctAccount::where('account_id', $preferencecompany['account_cash_id'])
            //     ->where('data_state', 0)
            //     ->first();

            //     $account_id_default_status 		= $account['account_default_status'];

            //     $data_credit = array (
            //         'journal_voucher_id'			=> $journal_voucher_id,
            //         'account_id'					=> $preferencecompany['account_cash_id'],
            //         'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
            //         'journal_voucher_amount'		=> ABS($salesdeliverynote['sales_delivery_note_cost']),
            //         'journal_voucher_credit_amount'	=> ABS($salesdeliverynote['sales_delivery_note_cost']),
            //         'account_id_default_status'		=> $account_id_default_status,
            //         'account_id_status'				=> 0,
            //     );

            //     AcctJournalVoucherItem::create($data_credit);
            // }
            


            // $salesdeliverynoteitem = SalesDeliveryNoteItem::where('sales_delivery_note_id', $salesdeliverynoteid['sales_delivery_note_id'])
            // ->get();

            
            // foreach($salesdeliverynoteitem as $item){

            //     $preference_company = PreferenceCompany::first();

            //     // $hpp_amount = $invitem['hpp_amount'] * $item['quantity'];


            //     //------account_id Persediaan Barang Dalam Perjalanan------//
            //     $account = AcctAccount::where('account_id', $preference_company['account_pdp_id'])
            //     ->where('data_state', 0)
            //     ->first();
                
            //     $account_id_default_status 		= $account['account_default_status'];

            //     $item_type_id = SalesOrderItem::select('item_type_id')
            //         ->where('sales_order_item_id', $data['sales_order_item_id'])
            //         ->first(); 
            //         //dd($item_type_id);

            //     $harga_beli = PurchaseOrderItem::select('purchase_order.total_amount')
            //     ->join('purchase_order', 'purchase_order.purchase_order_id', '=', 'purchase_order_item.purchase_order_id')
            //     ->where('purchase_order_item.item_type_id', $item_type_id['item_type_id'])
            //     ->first();
            //    // dd($harga_beli);
            //     $data_debit = array (
            //         'journal_voucher_id'			=> $journal_voucher_id,
            //         'account_id'					=> $account['account_id'],
            //         'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
            //         'journal_voucher_amount'		=> ABS($harga_beli['total_amount']),
            //         'journal_voucher_debit_amount'	=> ABS($harga_beli['total_amount']),
            //         'account_id_default_status'		=> $account_id_default_status,
            //         'account_id_status'				=> 1,
            //     );
                
                
            //     AcctJournalVoucherItem::create($data_debit);
                
                
            //     //------account_id Persediaan Barang Dagang------//
            //     $account = AcctAccount::where('account_id', $preference_company['account_inventory_trade_id'])
            //     ->where('data_state', 0)
            //     ->first();
                
            //     $account_id_default_status 		= $account['account_default_status'];
                
            //     $data_credit = array (
            //         'journal_voucher_id'			=> $journal_voucher_id,
            //         'account_id'					=> $account['account_id'],
            //         'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
            //         'journal_voucher_amount'		=> ABS($harga_beli['total_amount']),
            //         'journal_voucher_credit_amount'	=> ABS($harga_beli['total_amount']),
            //         'account_id_default_status'		=> $account_id_default_status,
            //         'account_id_status'				=> 0,
            //     );
            //     // dd($data_credit);

            //     AcctJournalVoucherItem::create($data_credit);

            // }
        //}
//--------------------------------------------------------End Journal Voucher-----------------------------------------------------------------//

            
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
        $salesdeliverynote->customer_id                 = $request->customer_id;
        $salesdeliverynote->driver_name                 = $request->driver_name;
        $salesdeliverynote->fleet_police_number         = $request->fleet_police_number;
        $salesdeliverynote->sales_delivery_note_remark  = $request->sales_delivery_note_remark;
        $salesdeliverynote->sales_delivery_note_date    = $request->sales_delivery_note_date;
        $salesdeliverynote->expedition_receipt_no       = $request->expedition_receipt_no;
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

                    $salesdeliverynoteitemstock = SalesDeliveryNoteItemStock::where('sales_delivery_note_item_id',$item['sales_delivery_note_item_id'])
                    ->get();

                    foreach($salesdeliverynoteitemstock as $val){
                        $itemstock = InvItemStock::where('item_stock_id', $val['item_stock_id'])
                        ->first();

                        $itemunitfirst  = InvItemUnit::where('item_unit_id', $val['item_unit_id'])->first();
                        $itemunitsecond = InvItemUnit::where('item_unit_id', $itemstock['item_unit_id'])->first();
                        
                        $item_total = $itemstock['item_total'] + ($val['quantity']* $itemunitsecond['item_unit_default_quantity'] / $itemunitfirst['item_unit_default_quantity']);

                        $itemstock->item_total = $itemstock['item_total'] + $item_total;
                        $itemstock->data_state = 0;
                        $itemstock->save();
                    }
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
        $orderitem = SalesOrderItem::select('sales_order_item.*','sales_order.customer_id','sales_order.sales_order_no','sales_order.sales_order_date', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name) AS item_name'))
        ->where('sales_order_item.sales_order_item_id', $sales_order_item_id)
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'sales_order_item.item_type_id')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->join('sales_order', 'sales_order.sales_order_id', 'sales_order_item.sales_order_id')
        ->first();

        return $orderitem;
    }

    public function getSalesOrderItemStock2($sales_order_item_id){
        $orderitem = SalesOrderItem::select('sales_order_item.*','sales_order.customer_id','sales_order.sales_order_no','sales_order.sales_order_date', DB::raw('CONCAT(inv_item_category.item_category_name) AS item_name'))
        ->where('sales_order_item.sales_order_item_id', $sales_order_item_id)
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'sales_order_item.item_type_id')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->join('sales_order', 'sales_order.sales_order_id', 'sales_order_item.sales_order_id')
        ->first();

        return $orderitem['item_name'];
    }

    public function getSalesOrderItem($sales_order_item_id){
        $orderitem = SalesOrderItem::select('sales_order_item.*','sales_order.customer_id','sales_order.sales_order_no','sales_order.sales_order_date', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name) AS item_name'))
        ->where('sales_order_item_id', $sales_order_item_id)
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'sales_order_item.item_type_id')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->join('sales_order', 'sales_order.sales_order_id', 'sales_order_item.sales_order_id')
        ->first();

        if($orderitem == null){
            return '-';
        }

        // dd($orderitem);

        return $orderitem;
    }

    public function getSalesDeliveryOrderDate($sales_delivery_order_id){
        $unit = SalesDeliveryOrder::select('sales_delivery_order_date')
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('data_state', 0)
        ->first();

        return $unit['sales_delivery_order_date'];
    }


    public function getSalesDeliveryOrderItemStock($sales_delivery_order_id){
        $unit = SalesDeliveryOrderItemStock::select('item_stock_id')
        ->where('sales_delivery_order_id', $sales_delivery_order_id)
        ->where('data_state', 0)
        ->get();

        return $unit;
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
    public function getCustomerId($sales_order_id){
        $unit = SalesOrder::select('core_customer.customer_id')
        ->join('core_customer', 'core_customer.customer_id', 'sales_order.customer_id')
        ->where('sales_order_id', $sales_order_id)
        ->where('sales_order.data_state', 0)
        ->first();

        return $unit['customer_id'];
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

        return $salesorder['sales_order_no'];
    }

    public function getPurchaseOrderNo($sales_order_id){
        $salesorder = SalesOrder::select('purchase_order_no')
        ->where('sales_order_id', $sales_order_id)
        ->where('data_state', 0)
        ->first();

        return $salesorder['purchase_order_no'];
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

        return $item['item_category_name']?? '';
    }
    public function getInvItemCategoryNameStock($item_stock_id){
        $item = SalesDeliveryNoteItemStock::select('sales_delivery_note_item_stock.*','inv_item_category.*')
        ->join('inv_item_category', 'sales_delivery_note_item_stock.item_category_id', 'inv_item_category.item_category_id')
        ->where('sales_delivery_note_item_stock.item_stock_id', $item_stock_id)
        ->where('sales_delivery_note_item_stock.data_state', 0)
        ->first();

        return $item['item_category_name'];
    }

    public function getInvItemTypeName($item_type_id){
        $item = InvItemType::select('item_type_name')
        ->where('item_type_id', $item_type_id)
        ->where('data_state', 0)
        ->first();

        return $item['item_type_name'] ?? '';
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

        if($invitem == null){
            return "-";
        }

        return $invitem['item_name'];
    }

    public function getItemUnitName($item_unit_id){
        $invitem = InvItemUnit::where('inv_item_unit.data_state','=',0)
        // ->join('inv_item_unit', 'inv_item_unit.item_unit_id', 'sales_delivery_order_item.item_unit_id')
        ->where('inv_item_unit.item_unit_id', $item_unit_id)
        ->first();

        if($invitem == null){
            return '-';
        }

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

    public function detailStockSalesDeliveryOrderToSDN($sales_delivery_order_id, $sales_delivery_order_item_id){
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

        return view('content/SalesDeliveryNote/FormDetailStockSalesDeliveryOrderToSDN',compact('type', 'detail_stock_sdo', 'sales_delivery_order_id', 'sales_delivery_order_item_id', 'sales_order_item_id'));
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

   public function getWeight($sales_delivery_note_item_id){
        $weight = SalesDeliveryNoteItem::select('item_weight_unit')
        ->where('sales_delivery_note_item.data_state', 0)
        ->where('sales_delivery_note_item.sales_delivery_note_item_id', $sales_delivery_note_item_id)
        ->first();

        return $weight['item_weight_unit'];
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

    public function getName($customer_id){
        $addres = CoreCustomer::select('customer_name')
        ->where('core_customer.data_state', 0)
        ->where('core_customer.customer_id', $customer_id)
        ->first();

        return $addres['customer_name'];
    }
    
    public function processPrintingSalesDeliveryNote ($sales_delivery_note_id)
    {
        $salesdeliverynote      = SalesDeliveryNote::findOrFail($sales_delivery_note_id);
        $salesdeliverynoteitem  = SalesDeliveryNoteItem::where('sales_delivery_note_item.sales_delivery_note_id', $sales_delivery_note_id)
        ->first();

        $sdn_item_stock  = SalesDeliveryNoteItemStock::select('sales_delivery_note_item_stock.*', 'inv_item_stock.item_batch_number', 'inv_item_stock.item_stock_expired_date')
        ->where('sales_delivery_note_item_stock.data_state', 0)
        ->join('inv_item_stock', 'sales_delivery_note_item_stock.item_stock_id', 'inv_item_stock.item_stock_id')
        ->where('sales_delivery_note_item_stock.sales_delivery_note_id', $salesdeliverynoteitem['sales_delivery_note_id'])
        ->orderby('sales_delivery_note_item_stock.sales_delivery_order_item_id', 'ASC')
        ->get();
        // dd($sdn_item_stock);
        $company = PreferenceCompany::select('*')
            ->first();
        
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

            $tbl = "
                <table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" >
                <tr>
                <td><div style=\"text-align: left; font-size:12px; font-weight: bold\">PBF MENJANGAN ENAM</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: left; font-size:10px\">Jl.Puspowarno Raya No 55D RT 06 RW 09</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: left; font-size:10px\">APJ : " . Auth::user()->name . "</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: left; font-size:10px\">" . $company['CDBO_no'] . "</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: left; font-size:10px\">" . $company['distribution_no'] . "</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: left; font-size:10px\">SIPA: 449.2/16/DPM-PTSP/SIKA.16/11/2019</div></td>
            </tr>
                    <tr>
                        <td style=\"text-align:center;width:100%\">
                            <div style=\"font-size:25px\"><b>SURAT JALAN</b></div>
                            <b style=\"font-size:14px\">".$salesdeliverynote['sales_delivery_note_no']."</b>
                        </td>
                    </tr>
                </table>
            ";

        $pdf::writeHTML($tbl, true, false, false, false, '');

        $tbl = "
        <table cellspacing=\"0\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\">
           
            <br>
            <tr>
                <td style=\"text-align:left;width:65%\">
                    <b>Kepada : </b>
                </td>
            </tr>
            <tr>
                <td style=\"text-align:left;width:50%\">
                    <b style=\"font-size:14px\">".$this->getCustomerNameSalesOrderId($salesdeliverynote['sales_order_id'])."</b> 
                    <br>
                    ".$this->getCustomerAddressSalesOrderId($salesdeliverynote['sales_order_id'])."
                </td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl, true, false, false, false, '');

        $tbl1 = "
        <b><div style=\"font-size:13px\">  Remarks : Pengiriman atas pesanan nomor ".$this->getPurchaseOrderNo($salesdeliverynote['sales_order_id'])." tanggal ".date('d/m/Y', strtotime($this->getSalesOrderDate($salesdeliverynote['sales_order_id'])))."</div></b><br>
        <table cellspacing=\"1\" cellpadding=\"0\" border=\"1\">			        
            <tr>
                <th style=\"text-align:center;\" width=\"5%\"><div style=\"font-size:13px\">No.</div></th>
                <th style=\"text-align:center;\" width=\"15%\"><div style=\"font-size:13px\">Batch Number</div></th> 
                <th style=\"text-align:center;\" width=\"15%\"><div style=\"font-size:13px\">Expired Date</div></th> 
                <th style=\"text-align:center;\" width=\"10%\"><div style=\"font-size:13px\">Kategori</div></th> 
                <th style=\"text-align:center;\" width=\"25%\"><div style=\"font-size:13px\">Nama Barang</div></th> 
                <th style=\"text-align:center;\" width=\"10%\"><div style=\"font-size:13px\">Unit</div></th> 
                <th style=\"text-align:center;\" width=\"10%\"><div style=\"font-size:13px\">Berat (Kg)</div></th> 
                <th style=\"text-align:center;\" width=\"10%\"><div style=\"font-size:13px\">Qty</div></th>
            </tr>   
            ";
        $no = 1;
        $totalqty = 0;
        $totalweight = 0;
        $totalamount = 0;
        $tbl2 = "";
            foreach ($sdn_item_stock as $key => $val) {
                $tbl2 .= "
                    <tr>
                        <td style=\"text-align:center;\"><div style=\"font-size:12px\">$no</div></td>
                        <td style=\"text-align:left;\"><div style=\"font-size:12px\"> ".$val['item_batch_number']."</div></td>
                        <td style=\"text-align:center;\"><div style=\"font-size:12px\">".$val['item_stock_expired_date']."</div></td>
                        <td style=\"text-align:left;\"><div style=\"font-size:12px\"> ".$this->getSalesOrderItemStock2($val['sales_order_item_id'])."</div></td>
                        <td style=\"text-align:left;\"><div style=\"font-size:12px\"> ".$this->getInvItemTypeName($val['item_type_id'])."</div></td>
                        <td style=\"text-align:left;\"><div style=\"font-size:12px\"> ".$this->getItemUnitName($val['item_unit_id'])." &nbsp;</div></td>
                        <td style=\"text-align:right;\"><div style=\"font-size:12px\"> ".$this->getWeight($val['sales_delivery_note_item_id'])." &nbsp;</div></td>
                        <td style=\"text-align:right;\"><div style=\"font-size:12px\">".$val['quantity_unit']." &nbsp;</div></td>
                    </tr>						
                ";
               
                $totalweight += $this->getWeight($val['sales_delivery_note_item_id']);
                $totalqty += $val['quantity_unit'];
                $no++;
            }

        $tbl4 = "
            <tr>
                <td colspan=\"6\" style=\"text-align:right;\" > Total : &nbsp;</td>
                <td style=\"text-align:right;font-size:10px\" >".$totalweight." &nbsp; </td>
                <td style=\"text-align:right;font-size:10px\" >".$totalqty." &nbsp; </td>

            </tr>
                    
        </table>";

        $pdf::writeHTML($tbl1.$tbl2.$tbl4, true, false, false, '');

        $tbl7 = "
        <br>
            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">

                <tr>
                    <th style=\"text-align:center;\">< style=\"font-size:12px\">Penerima/Cap</div></th>
                    <th style=\"text-align:center;\"><div style=\"font-size:12px\">Penerima Barang </div></th>
                    <th style=\"text-align:center;\"><div style=\"font-size:12px\">Dikeluarkan Oleh </div></th>
                    <th style=\"text-align:center;\"><div style=\"font-size:12px\">Semarang , ".date('d M Y')." &nbsp;&nbsp; </div></th>
                 </tr>
<tr>
	            <td></td>
                    <td></td>
                    <td></td>
                    <td></td>

</tr>
<tr>
	            <td></td>
                    <td></td>
                    <td></td>
                    <td></td>

</tr>
<tr>
	            <td></td>
                    <td></td>
                    <td></td>
                    <td></td>

</tr>


                <tr>
                    <td style=\"text-align:center;\"><div style=\"font-size:12px\">Angkutan </div></td>
                    <td style=\"text-align:center;\"><div style=\"font-size:12px\"> (Tanda Tangan, Cap, Nama Jelas) </div></td>
                    <td style=\"text-align:center;\"><div style=\"font-size:12px\">Pelaksana </div></td>
                    <td style=\"text-align:center;\"><div style=\"font-size:12px\">Hormat Kami, </div></td>
                </tr>
                <tr>
                `   <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        ";

        $pdf::writeHTML($tbl7, true, false, false, false, '');

        // ob_clean();

        $filename = 'Surat Jalan'.$salesdeliverynote['sales_delivery_note_no'].'.pdf';
        $pdf::Output($filename, 'I');
    }
    public function export(){
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
        $preference_company         = PreferenceCompany::first();
        $salesdeliverynote = SalesDeliveryNote::select('sales_delivery_note.*','sales_delivery_note_item.*','sales_delivery_note_item_stock.*','sales_order.*')
        ->rightJoin('sales_delivery_note_item','sales_delivery_note_item.sales_delivery_note_id' ,'sales_delivery_note.sales_delivery_note_id')
        ->leftjoin('sales_delivery_note_item_stock','sales_delivery_note_item_stock.sales_delivery_note_item_id' ,'sales_delivery_note_item.sales_delivery_note_item_id')
        ->leftJoin('sales_order','sales_order.sales_order_id' ,'sales_delivery_note.sales_order_id')
        ->where('sales_delivery_note.data_state','=',0)
        ->where('sales_delivery_note.sales_delivery_note_date', '>=', $start_date)
        ->where('sales_delivery_note.sales_delivery_note_date', '<=', $end_date)
        
        ->get();
        Session::forget('salesdeliveryordernoteelements');
   // dd($salesdeliverynote);


       $spreadsheet = new Spreadsheet();

       if(count($salesdeliverynote)>=0){
           $spreadsheet->getProperties()->setCreator("TRADING SYSTEM")
               ->setLastModifiedBy("TRADING SYSTEM")
               ->setTitle("Sales Delivery Note")
               ->setSubject("")
               ->setDescription("Sales Delivery Note")
               ->setKeywords("Sales Delivery Note")
               ->setCategory("Sales Delivery Note");

           $sheet = $spreadsheet->getActiveSheet(0);
           $spreadsheet->getActiveSheet()->setTitle("Sales Delivery Note");
           $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
           $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
           $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
           $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
           $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);
           $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(30);
           $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(25);
           $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);


   
           $spreadsheet->getActiveSheet()->mergeCells("B5:M5");
           $spreadsheet->getActiveSheet()->mergeCells("B6:M6");
           $spreadsheet->getActiveSheet()->mergeCells("B7:M7");
           $spreadsheet->getActiveSheet()->mergeCells("B8:M8");
           $spreadsheet->getActiveSheet()->mergeCells("B9:M9");
           $spreadsheet->getActiveSheet()->mergeCells("B10:M10");
           $spreadsheet->getActiveSheet()->mergeCells("B11:M11");
           $spreadsheet->getActiveSheet()->getStyle('B5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B11')->getFont()->setBold(true)->setSize(16);

           $spreadsheet->getActiveSheet()->getStyle('B12:M12')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
           $spreadsheet->getActiveSheet()->getStyle('B12:M12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);




           $sheet->setCellValue('B5', "PBF MENJANGAN ENAM ");	
           $sheet->setCellValue('B6', "Jl.Puspowarno Raya No 55D RT 06 RW 09");
           $sheet->setCellValue('B7', "APA : ".Auth::user()->name."");
           $sheet->setCellValue('B8', " SIKA: 449.2/16/DPM-PTSP/SIKA.16/III/2019 ");
           $sheet->setCellValue('B9', "");
           $sheet->setCellValue('B10', "");
           $sheet->setCellValue('B11', "SURAT JALAN Periode ".$start_date." - ".$end_date);	
           $sheet->setCellValue('B12', "No");
           $sheet->setCellValue('C12', "Nomor PO");
           $sheet->setCellValue('D12', "TGL PO");
           $sheet->setCellValue('E12', "No.SHIPPER");
           $sheet->setCellValue('F12', "TGL SHIPPER");
           $sheet->setCellValue('G12', "CABANG");
           $sheet->setCellValue('H12', "NAMA OBAT");
           $sheet->setCellValue('I12', "SATUAN");
           $sheet->setCellValue('J12', "Batch Number");
           $sheet->setCellValue('K12', "QTY");
           $sheet->setCellValue('L12', "HARGA");
           $sheet->setCellValue('M12', "JUMLAH");
           
           $j  = 13;
           $no = 1;

           if(count($salesdeliverynote)==0){
            $lastno = 2;
            $lastj = 13;
           }else{

            foreach($salesdeliverynote as $key => $val){
                $sheet = $spreadsheet->getActiveSheet(0);
                $spreadsheet->getActiveSheet()->setTitle("SURAT JALAN");
                $spreadsheet->getActiveSheet()->getStyle('B'.$j.':M'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->setCellValue('B'.$j, $no);
                $sheet->setCellValue('C'.$j, $val['purchase_order_no']);
                $sheet->setCellValue('D'.$j, $val['sales_order_date']);
                $sheet->setCellValue('E'.$j, $val['sales_delivery_note_no']);
                $sheet->setCellValue('F'.$j, $val['sales_delivery_note_date']);
                $sheet->setCellValue('G'.$j, $this->getName($val['customer_id']));
                $sheet->setCellValue('H'.$j, $this->getInvItemTypeName($val['item_type_id']));
                $sheet->setCellValue('I'.$j, $this->getItemUnitName($val['item_unit_id']));
                $sheet->setCellValue('J'.$j, $this->getItemBatchNumber($val['item_stock_id']));
                $sheet->setCellValue('K'.$j, $val['quantity_unit']);
                $sheet->setCellValue('L'.$j, $val['item_unit_price']);
                $sheet->setCellValue('M'.$j, $val['subtotal_price']);
          
                $no++;
                $j++;
                $lastno = $no;
                $lastj = $j;
             
            }
 
      
 
             $sheet = $spreadsheet->getActiveSheet(0);
             $spreadsheet->getActiveSheet()->getStyle('B'.$lastj.':M'.$lastj)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
             $sheet->setCellValue('H' . $lastj , 'Jumlah Total:');
  
             $sumrangedpp = 'K'. $lastno - 1 .':K'.$j;
             $sheet->setCellValue('K' . $lastj , '=SUM('.$sumrangedpp.')');
  
             $sumrangeppn = 'L'. $lastno - 1 .':L'.$j;
             $sheet->setCellValue('L' . $lastj , '=SUM('.$sumrangeppn.')');
  
             $sumrangetotal = 'M'. $lastno - 1 .':M'.$j;
             $sheet->setCellValue('M' . $lastj , '=SUM('.$sumrangetotal.')');


             $sheet->setCellValue('F' . $lastj + 1, 'Mengetahui');
             $sheet->setCellValue('K' . $lastj + 1, 'Dibuat Oleh');
 
 
             $spreadsheet->getActiveSheet()->getStyle('E'.$lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
             $spreadsheet->getActiveSheet()->getStyle('G'.$lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
             $spreadsheet->getActiveSheet()->getStyle('K'.$lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
            
             $sheet->setCellValue('E' . $lastj + 5, 'Apoteker');
             $sheet->setCellValue('G' . $lastj + 5, 'Administrasi Pajak');
             $sheet->setCellValue('K' . $lastj + 5, 'Dibuat Oleh');
 


           }
          
          
          
           
           ob_clean();
           $filename='Sales Delivery Note '.date('d M Y').'.xls';
           header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
           header('Content-Disposition: attachment;filename="'.$filename.'"');
           header('Cache-Control: max-age=0');

           $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
           $writer->save('php://output');
       }else{
           echo "Maaf data yang di eksport tidak ada !";
       }


    }
}
