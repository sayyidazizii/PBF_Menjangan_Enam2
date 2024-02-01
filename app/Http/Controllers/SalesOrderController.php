<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\CoreProvince;
use App\Models\CoreCity;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesOrderType;
use App\Models\PreferenceCompany;
use App\Models\SalesOrderItemStock;
use App\Models\SalesOrderItemStockTemporary;
use App\Models\InvWarehouse;
use App\Models\CoreCustomer;
use App\Models\InvItemCategory;
use Illuminate\Validation\Rule;
use App\Models\InvItemUnit;
use App\Models\InvItemType;
use App\Models\InvItem;
use App\Models\InvItemStock;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SalesOrderController extends Controller
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

        Session::put('editarraystate', 0);
        Session::forget('salesorderitem');
        Session::forget('salesorderelements');

        $salesorder = SalesOrder::where('data_state','=',0)
        ->where('sales_order_date', '>=', $start_date)
        ->where('sales_order_date', '<=', $end_date)
        ->get();

        return view('content/SalesOrder/ListSalesOrder',compact('salesorder', 'start_date', 'end_date'));
    }

    public function addSalesOrder()
    {
        $salesorderelements  = Session::get('salesorderelements');
        $salesorderitem      = Session::get('salesorderitem');

        
        // $invitem = InvItem::select('inv_item.item_id', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name) AS item_name'))
        // ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item.item_category_id')
        // ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item.item_type_id')
        // ->join('inv_item_stock', 'inv_item_stock.item_id', 'inv_item.item_id')
        // ->where('inv_item.data_state','=',0)
        // ->where('inv_item_stock.item_total', '>', 0)
        // ->pluck('item_name','inv_item.item_id');
        // $p = 9;
        // $invitemstock = InvItemStock::where('inv_item_stock.data_state','=',0)
        // ->where('item_type_id', $p)
        // ->sum('item_total');

        // dd($invitemstock);

        $invitemtype = InvItemType::where('inv_item_type.data_state','=',0)
        ->select('*')
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->join('inv_item_stock', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
       // ->where('inv_item_stock.item_total', '>', 0)
        ->pluck('inv_item_type.item_type_name','inv_item_type.item_type_id');

        $warehouse          = InvWarehouse::where('data_state','=',0)->pluck('warehouse_name', 'warehouse_id');
        $customer           = CoreCustomer::where('data_state','=',0)->pluck('customer_name', 'customer_id');
        $itemcategory       = InvItemCategory::where('data_state','=',0)->pluck('item_category_name', 'item_category_id');
        $itemunit           = InvItemUnit::where('data_state','=',0)->pluck('item_unit_name', 'item_unit_id');
        $itemtype           = [];
        $coreprovince       = CoreProvince::where('data_state', 0)->pluck('province_name', 'province_id');
        $corecity           = CoreCity::where('data_state', 0)->pluck('city_name', 'city_id');
        $salesordertype     = SalesOrderType::where('data_state', 0)->pluck('sales_order_type_name', 'sales_order_type_id');

        $null_item_type_id = Session::get('item_type_id');
        $ppnOut            = PreferenceCompany::select('ppn_amount_out')->first();
        //dd($salesorderelements);
        //Session::forget('salesorderitem');

        return view('content/SalesOrder/FormAddSalesOrder',compact('ppnOut','null_item_type_id', 'warehouse', 'customer', 'itemcategory', 'itemtype', 'salesorderitem', 'itemunit', 'salesorderelements', 'invitemtype', 'coreprovince', 'corecity', 'salesordertype'));
    }

    public function editSalesOrder($sales_order_id)
    {
        $editarraystate = Session::get('editarraystate');

        $salesorder= SalesOrder::where('data_state',0)
        ->where('sales_order_id', $sales_order_id)
        ->first();
        
        $invitem = InvItem::select('inv_item.item_id', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name, " ", core_grade.grade_name) AS item_name'))
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item.item_category_id')
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item.item_type_id')
        ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
        ->where('inv_item.data_state','=',0)
        ->pluck('item_name','inv_item.item_id');

        if($editarraystate == 0){
            Session::forget('salesorderitem');
            $salesorderitem = SalesOrderItem::where('data_state',0)
            ->where('sales_order_id', $sales_order_id)
            ->get();

            foreach($salesorderitem as $key => $val){

                $salesorderitem = array(
                    'sales_order_item_id'	=> $val['sales_order_item_id'],
                    'item_category_id'	    => $val['item_category_id'],
                    'item_type_id'	        => $val['item_type_id'],
                    'item_unit_id'	        => $val['item_unit_id'],
                    'quantity'	            => $val['quantity'],
                    'price'	                => $val['item_unit_cost'],
                    'total_price'	        => $val['subtotal_amount'],
                );
    
                $lastsalesorderitem= Session::get('salesorderitem');
                if($lastsalesorderitem!== null){
                    array_push($lastsalesorderitem, $salesorderitem);
                    Session::put('salesorderitem', $lastsalesorderitem);
                }else{
                    $lastsalesorderitem= [];
                    array_push($lastsalesorderitem, $salesorderitem);
                    Session::push('salesorderitem', $salesorderitem);
                }

            }
        }
        
        $salesorderitem = Session::get('salesorderitem');

        $warehouse = InvWarehouse::where('data_state','=',0)->pluck('warehouse_name', 'warehouse_id');
        $customer = CoreCustomer::where('data_state','=',0)->pluck('customer_name', 'customer_id');
        $itemcategory = InvItemCategory::where('data_state','=',0)->pluck('item_category_name', 'item_category_id');
        $itemunit = InvItemUnit::where('data_state','=',0)->pluck('item_unit_name', 'item_unit_id');
        $itemtype = InvItemType::where('data_state', 0)->where('item_category_id', $salesorder['item_category_id'])->pluck('item_type_name', 'item_type_id');

        return view('content/SalesOrder/FormEditSalesOrder',compact('warehouse', 'customer', 'itemcategory', 'itemtype', 'salesorderitem', 'itemunit', 'salesorder', 'editarraystate', 'invitem'));
    }

    public function detailSalesOrder($sales_order_id)
    {
        $salesorder= SalesOrder::where('data_state',0)
        ->where('sales_order_id', $sales_order_id)
        ->first();

        $salesorderitem= SalesOrderItem::select('sales_order_item.*','inv_item_type.*')
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'sales_order_item.item_type_id')
        ->where('sales_order_item.data_state',0)
        ->where('sales_order_item.sales_order_id', $sales_order_id)
        ->get();
        //dd($salesorder,$salesorderitem);
        return view('content/SalesOrder/FormDetailSalesOrder',compact('salesorderitem', 'salesorder'));
    }

    public function elements_add(Request $request){
        $salesorderelements= Session::get('salesorderelements');
        if(!$salesorderelements || $salesorderelements == ''){
            $salesorderelements['sales_order_date'] = '';
            $salesorderelements['sales_order_delivery_date'] = '';
            $salesorderelements['warehouse_id'] = '';
            $salesorderelements['customer_id'] = '';
            $salesorderelements['sales_order_type_id'] = '';
            $salesorderelements['sales_order_remark'] = '';
            $salesorderelements['purchase_order_no'] = '';
            $salesorderelements['purchase_order_due_date'] = '';
        }
        $salesorderelements[$request->name] = $request->value;
        Session::put('salesorderelements', $salesorderelements);
    }
    
    public function processAddArraySalesOrderItem(Request $request)
    {
        $fields = $request->validate([
            'item_category_id'              => 'required',
            'item_type_id'                  => 'required',
            'item_unit_id'                  => 'required',
            'quantity'                      => 'required',
            'price'                         => 'required',
            'total_price'                   => 'required',
            'discount_percentage_item'      => 'required',
            'discount_amount_item'          => 'required',
            'subtotal_after_discount_item_a'=> 'required',
            'discount_percentage_item_b'    => 'required',
            'discount_amount_item_b'        => 'required',
            'subtotal_after_discount_item_b'=> 'required',
        ]);

        $salesorderitem = array(
            'item_category_id'	            => $request->item_category_id,
            'item_type_id'	                => $request->item_type_id,
            'item_unit_id'	                => $request->item_unit_id,
            'quantity'	                    => $request->quantity,
            'price'	                        => $request->price,
            'total_price'	                => $request->total_price,
            'discount_percentage_item'	    => $request->discount_percentage_item,
            'discount_amount_item'	        => $request->discount_amount_item,
            'subtotal_after_discount_item_a'=> $request->subtotal_after_discount_item_a,
            'discount_percentage_item_b'	=> $request->discount_percentage_item_b,
            'discount_amount_item_b'	    => $request->discount_amount_item_b,
            'subtotal_after_discount_item_b'=> $request->subtotal_after_discount_item_b,
            'ppn_amount_item'               => $request->ppn_amount_item,
            'total_price_after_ppn_amount'  => $request->total_price_after_ppn_amount,
        );

        // dd($salesorderitem);

        $lastsalesorderitem= Session::get('salesorderitem');
        if($lastsalesorderitem!== null){
            array_push($lastsalesorderitem, $salesorderitem);
            Session::put('salesorderitem', $lastsalesorderitem);
        }else{
            $lastsalesorderitem= [];
            array_push($lastsalesorderitem, $salesorderitem);
            Session::push('salesorderitem', $salesorderitem);
        }
        
        Session::put('editarraystate', 1);
        
        return redirect('/sales-order/add');
    }

    public function deleteArraySalesOrderItem ($record_id)
    {
        $arrayBaru			= array();
        $dataArrayHeader	= Session::get('salesorderitem');
        
        foreach($dataArrayHeader as $key=>$val){
            if($key != $record_id){
                $arrayBaru[$key] = $val;
            }
        }
        Session::forget('salesorderitem');
        Session::put('salesorderitem', $arrayBaru);

        return redirect('/sales-order/add');
    }

    public function deleteSalesOrder ($sales_order_id)
    {
        $salesorder = SalesOrder::findOrFail($sales_order_id);
        $salesorder->data_state = 1;
        
        if($salesorder->save()){
            $msg = 'Hapus Sales Order Berhasil';
            return redirect('/sales-order')->with('msg',$msg);
        }else{
            $msg = 'Hapus Sales Order Gagal';
            return redirect('/sales-order')->with('msg',$msg);
        }
    }

    public function getItemCategoryName($item_category_id){
        $itemcategory = InvItemCategory::where('data_state', 0)
        ->where('item_category_id', $item_category_id)
        ->first();

        return $itemcategory['item_category_name'];
    }

    public function getItemTypeName($item_type_id){
        $itemtype = InvItemType::where('inv_item_type.data_state','=',0)
        ->select('inv_item_type.item_type_id', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name) AS item_name'))
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->where('inv_item_type.item_type_id', $item_type_id)
        ->first();

        if($itemtype == null){
            return '-';
        }
        // dd($itemtype[9]);
        return $itemtype['item_name'];
    }

    public function getItemUnitName($item_unit_id){
        $itemunit = InvItemUnit::where('data_state', 0)
        ->where('item_unit_id', $item_unit_id)
        ->first();

        if($itemunit == null){
            return '-';
        }

        return $itemunit['item_unit_name'];
    }

    public function getItemName($item_id){
        $item = InvItem::select('inv_item.item_id', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name, " ", core_grade.grade_name) AS item_name'))
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item.item_category_id')
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item.item_type_id')
        ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
        ->where('inv_item.data_state', 0)
        ->where('inv_item.item_id', $item_id)
        ->first();

        if($item == null){
            return '-';
        }else{
            return $item['item_name'];
        }

    }

    public function getCoreCustomerName($customer_id){
        $customer = CoreCustomer::where('data_state', 0)
        ->where('customer_id', $customer_id)
        ->first();

        return $customer['customer_name'] ?? '';
    }

    public function getInvWarehouseName($warehouse_id){
        $warehouse = InvWarehouse::where('data_state', 0)
        ->where('warehouse_id', $warehouse_id)
        ->first();

        return $warehouse['warehouse_name'];
    }

    public function getDiscountNota($sales_order_id){
        $salesorderitem = SalesOrderItem::join('sales_order', 'sales_order.sales_order_id', '=', 'sales_order_item.sales_order_id')
        ->where('sales_order_item.data_state', 0)
        ->where('sales_order_item.sales_order_id', $sales_order_id)
        ->first();

        return $salesorderitem['discount_amount'];
    }

    public function getPPNOut($sales_order_id){
        $salesorderitem = SalesOrderItem::join('sales_order', 'sales_order.sales_order_id', '=', 'sales_order_item.sales_order_id')
        ->where('sales_order_item.data_state', 0)
        ->where('sales_order_item.sales_order_id', $sales_order_id)
        ->first();

        return $salesorderitem['ppn_out_amount'];
    }

    public function getAmountAfterPPN_Out($sales_order_id){
        $salesorderitem = SalesOrderItem::join('sales_order', 'sales_order.sales_order_id', '=', 'sales_order_item.sales_order_id')
        ->where('sales_order_item.data_state', 0)
        ->where('sales_order_item.sales_order_id', $sales_order_id)
        ->first();

        return $salesorderitem['subtotal_after_ppn_out'];
    }

    public function getDiscountNotaNominal($sales_order_id){
        $salesorderitem = SalesOrderItem::join('sales_order', 'sales_order.sales_order_id', '=', 'sales_order_item.sales_order_id')
        ->where('sales_order_item.data_state', 0)
        ->where('sales_order_item.sales_order_id', $sales_order_id)
        ->first();

        return $salesorderitem['subtotal_after_discount'];
    }


    public function filterSalesOrder(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/sales-order');
    }

    public function resetFilterSalesOrder(){
        Session::forget('start_date');
        Session::forget('end_date');

        return redirect('/sales-order');
    }

    public function processAddSalesOrder(Request $request){
        $validationRules = [
            'sales_order_date'               => 'required',
            'sales_order_delivery_date'      => 'required',
            'customer_id'                    => 'required',
            'purchase_order_no'              => [
                'required',
                Rule::unique('sales_order', 'purchase_order_no'),
            ],
            'sales_order_type_id'            => 'required',
            'total_item_all'                 => 'required',
            'total_price_all'                => 'required',
        ];

        
        $validatedData = $request->validate($validationRules);
        $fileNameToStore = '';

        if($request->hasFile('receipt_image')){

            //Storage::delete('/public/receipt_images/'.$user->receipt_image);

            // Get filename with the extension
            $filenameWithExt = $request->file('receipt_image')->getClientOriginalName();
            //Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('receipt_image')->getClientOriginalExtension();
            // Filename to store
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $path = $request->file('receipt_image')->storeAs('public/receipt',$fileNameToStore);

        }

        // print_r($request->file('receipt_image'));

        $salesorder = array (
            'sales_order_date'           => $validatedData['sales_order_date'],
            'sales_order_delivery_date'  => $validatedData['sales_order_delivery_date'],
            'customer_id'                => $validatedData['customer_id'],
            'sales_order_type_id'        => $validatedData['sales_order_type_id'],
            'total_item'                 => $validatedData['total_item_all'],
            'total_amount'               => $validatedData['total_price_all'],
            'sales_order_remark'         => $request->sales_order_remark,
            'discount_percentage'        => $request->discount_percentage,
            'discount_amount'            => $request->discount_amount,
            'subtotal_after_discount'    => $request->subtotal_after_discount,
            'ppn_out_percentage'	     => $request['ppn_out_percentage'],
            'ppn_out_amount'	         => $request['ppn_out_amount'],
            'subtotal_after_ppn_out'	 => $request['subtotal_after_ppn_out'],
            'receipt_image'              => $fileNameToStore,
            'branch_id'                  => Auth::user()->branch_id,
            'purchase_order_no'          => $validatedData['purchase_order_no'],
            'purchase_order_due_date'    => $request['purchase_order_due_date'],

        );
        if (SalesOrder::where('purchase_order_no', $validatedData['purchase_order_no'])->exists()) {
            $msg = 'Nomor PO sudah ada.';
            return redirect('/sales-order/add')->with('msg', $msg);
        }
       // dd($salesorder);

        if(SalesOrder::create($salesorder)){
            $sales_order_id = SalesOrder::orderBy('created_at','DESC')->first();
            $salesorderitem = Session::get('salesorderitem');
            //dd($salesorderitem);
            foreach ($salesorderitem AS $key => $val){
                $datasalesorderitem = array (
                    'sales_order_id'                => $sales_order_id['sales_order_id'],
                    'item_category_id'              => $val['item_category_id'],
                    'item_type_id'                  => $val['item_type_id'],
                    'item_unit_id'                  => $val['item_unit_id'],
                    // 'item_stock_id'                 => $val['item_stock_id'],
                    'quantity'                      => $val['quantity'],
                    'quantity_resulted'             => $val['quantity'],
                    'item_unit_price'               => $val['price'],
                    'subtotal_amount'               => $val['total_price'],
                    'discount_percentage_item'      => $val['discount_percentage_item'],
                    'discount_amount_item'          => $val['discount_amount_item'],
                    'subtotal_after_discount_item_a'=> $val['subtotal_after_discount_item_a'],
                    'discount_percentage_item_b'    => $val['discount_percentage_item_b'],
                    'discount_amount_item_b'        => $val['discount_amount_item_b'],
                    'subtotal_after_discount_item_b'=> $val['subtotal_after_discount_item_b'],
                    'ppn_amount_item'               => $val['ppn_amount_item'],
                    'total_price_after_ppn_amount'  => $val['total_price_after_ppn_amount'],

                );
                //dd($datasalesorderitem);
                SalesOrderItem::create($datasalesorderitem);
                //     // $sales_order_item_id = SalesOrderItem::where('sales_order_id',$datasalesorderitem['sales_order_id'])
                //     // // ->orderBy('created_at','DESC')
                //     // ->get();
                //     // $salesorderitem = Session::get('salesorderitem');
                //     // dd($sales_order_item_id);
                //     foreach($datasalesorderitem AS $key =>  $itemstock){

                //         $data = array(
                //             'sales_order_id' 			    => $sales_order_id['sales_order_id'],
                //             // 'sales_order_item_id' 		    => $sales_order_item_id['sales_order_item_id'],
                //             'item_unit_id' 		            => $itemstock['item_unit_id'],
                //             'item_stock_id' 		        => $itemstock['item_stock_id'],
                //             'item_total_stock' 		        => $itemstock['quantity'],
                //             'created_id'                    => Auth::id(),
                //         );
                //         dd($data);
                //         SalesOrderItemStock::create($datasalesorderitem);
                //}
                    
                //}    

            }
            $msg = 'Tambah Sales Order Berhasil';
            return redirect('/sales-order')->with('msg',$msg);
        }else{
            $msg = 'Tambah Sales Order Gagal';
            return redirect('/sales-order/add')->with('msg',$msg);
        }

    }

    public function addCoreCustomer(Request $request){
        $customer_name              = $request->customer_name;
        $province_id                = $request->province_id;
        $city_id                    = $request->city_id;
        $customer_address           = $request->customer_address;
        $customer_home_phone        = $request->customer_home_phone;
        $customer_mobile_phone1     = $request->customer_mobile_phone1;
        $customer_mobile_phone2     = $request->customer_mobile_phone2;
        $customer_fax_number        = $request->customer_fax_number;
        $customer_email             = $request->customer_email;
        $customer_contact_person    = $request->customer_contact_person;
        $customer_id_number         = $request->customer_id_number;
        $customer_tax_no            = $request->customer_tax_no;
        $customer_payment_terms     = $request->customer_payment_terms;
        $customer_remark            = $request->customer_remark;
        $data='';

        $corecustomer = CoreCustomer::create([  
            'customer_name'             => $customer_name,
            'province_id'               => $province_id,
            'city_id'                   => $city_id,
            'customer_address'          => $customer_address,
            'customer_home_phone'       => $customer_home_phone,
            'customer_mobile_phone1'    => $customer_mobile_phone1,
            'customer_mobile_phone2'    => $customer_mobile_phone2,
            'customer_fax_number'       => $customer_fax_number,
            'customer_email'            => $customer_email,
            'customer_contact_person'   => $customer_contact_person,
            'customer_id_number'        => $customer_id_number,
            'customer_tax_no'           => $customer_tax_no,
            'customer_payment_terms'    => $customer_payment_terms,
            'customer_remark'           => $customer_remark,
            'created_id'                => Auth::id()
        ]);

        $customer = CoreCustomer::where('data_state','=',0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($customer as $mp){
            $data .= "<option value='$mp[customer_id]'>$mp[customer_name]</option>\n";	
        }

        return $data;
    }

    public function kwitansiSalesOrder($sales_order_id){
        $salesorder = SalesOrder::findOrFail($sales_order_id);
        
        return response()->download(
            storage_path('app/public/receipt/'.$salesorder['receipt_image']),
            'kwitansi_'.$salesorder['sales_order_id'].'.png',
        );
    }

        public function getAvailableStock(Request $request){
            $item_stock_id    = $request->item_stock_id;
            $available_stock = 0;

            $itemstock  = InvItemStock::where('inv_item_stock.data_state', 0)
            ->where('inv_item_stock.item_stock_id', $item_stock_id)
            ->where('inv_item_stock.warehouse_id',6)
            ->sum('quantity_unit');

            $itemunitsecond = InvItemStock::join('inv_item_unit', 'inv_item_stock.item_unit_id', '=', 'inv_item_unit.item_unit_id')
            ->where('inv_item_stock.item_stock_id', $item_stock_id)
            ->first();

            if($itemstock == null){
                $return_data =  'kosong';
                return $return_data;
            }else{
                $return_data =  $itemstock . ' ' .  $itemunitsecond['item_unit_name'];
                return $return_data;
            }
        }

        public function getItemUnitPrice(Request $request){
            $item_stock_id    = $request->item_stock_id;

            $itemstock  = InvItemStock::select('item_unit_price')
            ->where('inv_item_stock.data_state', 0)
            ->where('inv_item_stock.item_stock_id', $item_stock_id)
            ->where('inv_item_stock.warehouse_id',6)
            ->first();

            if($itemstock == null){
                $return_data =  'kosong';
                return $return_data;
            }else{
                $return_data =  $itemstock;
                return $return_data['item_unit_price'];
            }
        }


        public function getInvItemType(Request $request)
        {
            $item_category_id = $request->item_category_id;
            $data = '';
            
            $type = InvItemType::select('*')
            ->where('inv_item_type.data_state','=',0)
            ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
            ->join('inv_item_stock', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
            ->where('inv_item_stock.item_category_id', $item_category_id)
            ->where('inv_item_stock.warehouse_id', 6)
            ->get();

            $data .= "<option value=''>--Choose One--</option>";
            foreach ($type as $mp) {
                $data .= "<option value='$mp[item_stock_id]'>$mp[item_type_name]".'-'."$mp[item_batch_number]</option>\n";
            }

            return $data;
        }

        public function getInvItemTypeId(Request $request)
        {
            $item_stock_id = $request->item_stock_id;
            // $data = '';
            
            $type = InvItemStock::select('*')
            ->where('inv_item_stock.data_state','=',0)
            ->where('inv_item_stock.item_stock_id', $item_stock_id)
            ->where('inv_item_stock.warehouse_id', 6)
            ->first();

            return $type['item_type_id'];
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


        $data .= "<option value=''>--Choose One--</option>";
        foreach ($stock as $val){
            $data .= "<option value='$val[item_stock_id]'>$val[item_name]</option>\n";	
        }
        return $data;
    }



    public function getSelectDataUnit(Request $request){

        $item_stock_id  = $request->item_stock_id;
        $item_type_id   = InvItemStock::select('*')
        ->where('inv_item_stock.data_state','=',0)
        ->where('inv_item_stock.item_stock_id', $item_stock_id)
        ->where('inv_item_stock.warehouse_id', 6)
        ->first();

        $inv_item_type= InvItemType::where('item_type_id', $item_type_id['item_type_id'])
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
