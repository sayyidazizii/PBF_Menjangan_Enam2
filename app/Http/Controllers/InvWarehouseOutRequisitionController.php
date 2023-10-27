<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\InvItemStock;
use App\Models\InvItemUnit;
use App\Models\InvWarehouse;
use App\Models\InvWarehouseOut;
use App\Models\InvWarehouseOutItem;
use App\Models\InvWarehouseOutType;
use App\Models\InvWarehouseLocation;
use App\Models\CoreProvince;
use App\Models\CoreCity;
use App\Models\CoreGrade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use stdClass;

class InvWarehouseOutRequisitionController extends Controller
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
        Session::forget("warehouseoutelements");
        Session::forget('datawarehouseoutrequisition');

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

        $invwarehouseout = InvWarehouseOut::where('data_state','=',0)
        ->where('warehouse_out_date', '>=', $start_date)
        ->where('warehouse_out_date', '<=', $end_date)
        ->get();

        return view('content/InvWarehouseOutRequisition/ListInvWarehouseOutRequisition',compact('invwarehouseout', 'start_date', 'end_date'));
    }

    public function filterInvWarehouseOutRequisition(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/warehouse-out-requisition');
    }

    public function resetFilterInvWarehouseOutRequisition(){
        Session::forget('start_date');
        Session::forget('end_date');

        return redirect('/warehouse-out-requisition');
    }

    public function addInvWarehouseOutRequisition()
    {
        $warehouseoutelements = Session::get("warehouseoutelements");
        $invwarehouseouttype = InvWarehouseOutType::where('data_state', 0)
        ->pluck('warehouse_out_type_name', 'warehouse_out_type_id');

        $invitemstock1 = InvItemStock::select(DB::raw("inv_item_stock.item_stock_id, CONCAT(inv_item_stock.item_batch_number, ' - ',inv_item_category.item_category_name, ' ', inv_item_type.item_type_name, ' - ', ' Kadaluarsa : ', inv_item_stock.item_stock_expired_date) AS item_batch_number"))
        ->join('inv_item_category', 'inv_item_stock.item_category_id', 'inv_item_category.item_category_id')
        ->join('inv_item_type', 'inv_item_stock.item_type_id', 'inv_item_type.item_type_id')
        ->where('inv_item_stock.data_state', 0)
        ->where('inv_item_stock.item_id', 0)
        ->get();

        $invitemstock2 = InvItemStock::select(DB::raw("inv_item_stock.item_stock_id, CONCAT(inv_item_stock.item_batch_number, ' - ',inv_item_category.item_category_name, ' ', inv_item_type.item_type_name, ' ', core_grade.grade_name, ' : ', inv_item_stock.item_stock_date) AS item_batch_number"))
        ->join('inv_item_category', 'inv_item_stock.item_category_id', 'inv_item_category.item_category_id')
        ->join('inv_item_type', 'inv_item_stock.item_type_id', 'inv_item_type.item_type_id')
        ->join('inv_item', 'inv_item.item_id', 'inv_item_stock.item_id')
        ->join('core_grade', 'inv_item.grade_id', 'core_grade.grade_id')
        ->where('inv_item_stock.data_state', 0)
        ->where('inv_item_stock.item_id', '!=', 0)
        ->get();

        $invitemstock = $invitemstock1->merge($invitemstock2);

        $invitemunit = InvItemUnit::where('data_state', 0)
        ->pluck('item_unit_name', 'item_unit_id');

        $invwarehouse = InvWarehouse::where('data_state', 0)
        ->where('warehouse_type', 1)
        ->pluck('warehouse_name', 'warehouse_id');

        $dataarray = Session::get('datawarehouseoutrequisition');

        return view('content/InvWarehouseOutRequisition/FormAddInvWarehouseOutRequisition', compact('invwarehouseouttype', 'invwarehouse', 'invitemunit', 'invitemstock', 'dataarray', 'warehouseoutelements'));
    }
    

    public function voidInvWarehouseOutRequisition($warehouse_out_id)
    {
        $warehouseout        = InvWarehouseOut::findOrFail($warehouse_out_id);
        
        $warehouseoutitem    = InvWarehouseOutItem::where('warehouse_out_id', $warehouse_out_id)
        ->get();

        return view('content/InvWarehouseOutRequisition/FormVoidInvWarehouseOutRequisition', compact('warehouseout', 'warehouseoutitem'));
    }
    

    public function detailInvWarehouseOutRequisition($warehouse_out_id)
    {
        $warehouseout        = InvWarehouseOut::findOrFail($warehouse_out_id);
        
        $warehouseoutitem    = InvWarehouseOutItem::where('warehouse_out_id', $warehouse_out_id)
        ->get();

        return view('content/InvWarehouseOutRequisition/FormDetailInvWarehouseOutRequisition', compact('warehouseout', 'warehouseoutitem'));
    }

    public function processAddInvWarehouseOutRequisition(Request $request)
    {
        $fields = $request->validate([
            'warehouse_id'                     => 'required',
            'warehouse_out_type_id'            => 'required',
            'warehouse_out_requisition_date'   => 'required',
        ]);
        

        $warehouseout = array(
            'warehouse_id'                      => $fields['warehouse_id'], 
            'warehouse_out_type_id'             => $fields['warehouse_out_type_id'],
            'warehouse_out_date'                => $fields['warehouse_out_requisition_date'], 
            'warehouse_out_remark'              => $request->warehouse_out_remark,
            'created_id'                        => Auth::id(),
            'data_state'                        => 0
        );

        if(InvWarehouseOut::create($warehouseout)){
            $warehouseoutitem = Session::get('datawarehouseoutrequisition');
            $lastwarehouseout = InvWarehouseOut::orderBy('created_at', 'DESC')
            ->first();
            foreach($warehouseoutitem as $val){
                $warehouseoutitem = array(
                    'warehouse_out_id'  => $lastwarehouseout['warehouse_out_id'],
                    'item_stock_id'     => $val['item_stock_id'], 
                    'item_unit_id'      => $val['item_unit_id'],
                    'quantity'          => $val['quantity'], 
                    'created_id'        => Auth::id(),
                    'data_state'        => 0
                );
                InvWarehouseOutItem::create($warehouseoutitem);
            }

            $msg = 'Tambah Pengeluaran Gudang Berhasil';
            return redirect('/warehouse-out-requisition')->with('msg',$msg);
        }else{
            $msg = 'Tambah Pengeluaran Gudang Gagal';
            return redirect('/warehouse-out-requisition/add')->with('msg',$msg);
        }
    }
    
    public function processAddArrayWarehouseOutRequisitionItem(Request $request)
    {
        $quantity = $request->quantity;
        $unit = InvItemUnit::where('item_unit_id', $request->item_unit_id)->first();

        $warehouseoutrequisition = array(
            'item_stock_id'    => $request->item_stock_id,
            'quantity'         => $request->quantity,
            'item_unit_id'     => $request->item_unit_id,
        );

        $lastwarehouseoutrequisition = Session::get('datawarehouseoutrequisition');
        if($lastwarehouseoutrequisition !== null){
            array_push($lastwarehouseoutrequisition, $warehouseoutrequisition);
            Session::put('datawarehouseoutrequisition', $lastwarehouseoutrequisition);
        }else{
            $lastwarehouseoutrequisition = [];
            array_push($lastwarehouseoutrequisition, $warehouseoutrequisition);
            Session::push('datawarehouseoutrequisition', $warehouseoutrequisition);
        }
    }

    public function processVoidInvWarehouseOutRequisition($warehouse_out_id){
        $warehouseout = InvWarehouseOut::findOrFail($warehouse_out_id);
        $warehouseout->data_state = 1;
        if($warehouseout->save()){
            $msg = 'Hapus Pengeluaran Gudang Berhasil';
            return redirect('/warehouse-out-requisition')->with('msg',$msg);
        }else{
            $msg = 'Hapus Pengeluaran Gudang Gagal';
            return redirect('/warehouse-out-requisition')->with('msg',$msg);
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

    public function deleteInvWarehouse($warehouse_id)
    {
        $item = InvWarehouse::findOrFail($warehouse_id);
        $item->data_state = 1;
        if($item->save())
        {
            $msg = 'Hapus Gudang Berhasil';
        }else{
            $msg = 'Hapus Gudang Gagal';
        }

        return redirect('/warehouse')->with('msg',$msg);
    }

    public function getCoreCity(Request $request){
        $province_id = $request->province_id;
        $data='';

        $city = CoreCity::where('province_id', $province_id)
        ->where('data_state','=',0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($city as $mp){
            $data .= "<option value='$mp[city_id]'>$mp[city_name]</option>\n";	
        }

        return $data;
    }

    public function getCityName($warehouse_location_id){
        $warehouselocation = InvWarehouseLocation::where('warehouse_location_id', $warehouse_location_id)
        ->where('data_state','=',0)
        ->first();

        $city = CoreCity::where('city_id', $warehouselocation['city_id'])
        ->where('data_state','=',0)
        ->first();

        return $city['city_name'];
    }

    public function getProvinceName($province_id){
        $province = CoreProvince::where('province_id', $province_id)
        ->where('data_state','=',0)
        ->first();

        return $province['province_name'];
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

    public function getItemStockDetail(Request $request){
        $itemstock = InvItemStock::select('inv_item_stock.item_total', 'inv_item_stock.item_id', 'inv_item_stock.item_category_id', 'inv_item_unit.item_unit_name', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name) AS item_name'))
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_stock.item_category_id')
        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
        ->join('inv_item_unit', 'inv_item_unit.item_unit_id', 'inv_item_stock.item_unit_id')
        ->where('inv_item_stock.data_state', 0)
        ->where('inv_item_stock.item_stock_id', $request->item_stock_id)
        ->first();

        if($itemstock['item_id'] != 0){
            $grade = InvItemStock::select('core_grade.grade_name')
            ->join('inv_item', 'inv_item.item_id', 'inv_item_stock.item_id')
            ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
            ->where('inv_item_stock.data_state', 0)
            ->where('inv_item_stock.item_stock_id', $request->item_stock_id)
            ->first();

            $item_name = $itemstock['item_name'].' '.$grade['grade_name'];
        }else{
            if($itemstock['item_category_id'] == 3){
                $item_name = $itemstock['item_name'];
            }else{
                $item_name = $itemstock['item_name']." No Grade";
            }
        }

        $datas = new stdClass;
        
        $datas->item_total      = $itemstock['item_total'];
        $datas->item_name       = $item_name;
        $datas->item_unit_name  = $itemstock['item_unit_name'];

        return response()->json(json_encode($datas));
    }

    public function resetArrayInvWarehouseOutRequisition (){
        Session::forget("warehouseoutelements");
        Session::forget('datawarehouseoutrequisition');

        $msg = 'Reset Data Form Tambah Pengeluaran Gudang Berhasil';
        return redirect('/warehouse-out-requisition/add')->with('msg',$msg);
    }
    
    public function deleteArrayInvWarehouseOutRequisitionItem ($record_id)
    {
        $arrayBaru			= array();
        $dataArrayHeader	= Session::get('datawarehouseoutrequisition');
        
        foreach($dataArrayHeader as $key=>$val){
            if($key != $record_id){
                $arrayBaru[$key] = $val;
            }
        }
        Session::forget('datawarehouseoutrequisition');
        Session::put('datawarehouseoutrequisition', $arrayBaru);

        return redirect('/warehouse-out-requisition/add');
    }
}
