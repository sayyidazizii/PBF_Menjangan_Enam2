<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\InvWarehouse;
use App\Models\InvWarehouseLocation;
use App\Models\CoreProvince;
use App\Models\CoreCity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InvWarehouseController extends Controller
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
        $invwarehouse = InvWarehouse::where('data_state','=',0)->get();

        return view('content/InvWarehouse/ListInvWarehouse',compact('invwarehouse'));
    }

    public function addInvWarehouse(Request $request)
    {
        $location = InvWarehouseLocation::where('inv_warehouse_location.data_state',0)
        ->join('core_city','inv_warehouse_location.city_id','core_city.city_id')
        ->pluck('city_name', 'warehouse_location_id');

        $province = CoreProvince::where('data_state', 0)
        ->pluck('province_name', 'province_id');

        $city = CoreCity::where('data_state', 0)
        ->pluck('city_name', 'city_id');

        return view('content/InvWarehouse/FormAddInvWarehouse', compact('location', 'province', 'city'));
    }

    public function addInvWarehouseLocation(Request $request){
        $warehouse_location_code = $request->warehouse_location_code;
        $province_id             = $request->province_id;
        $city_id                 = $request->city_id;
        $data='';
        
        $warehouselocation = InvWarehouseLocation::create([  
            'warehouse_location_code'       => $warehouse_location_code,
            'province_id'                   => $province_id,
            'city_id'                       => $city_id,
            'created_id'                    => Auth::id()
        ]);

        $warehouselocation = InvWarehouseLocation::select('inv_warehouse_location.*', 'core_city.city_name')
        ->where('inv_warehouse_location.data_state', 0)
        ->join('core_city', 'core_city.city_id', 'inv_warehouse_location.city_id')
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($warehouselocation as $mp){
            $data .= "<option value='$mp[warehouse_location_id]'>$mp[city_name]</option>\n";	
        }

        return $data;
    }

    public function processAddInvWarehouse(Request $request)
    {
        $fields = $request->validate([
            'warehouse_code'            => 'required',
            'warehouse_type'            => 'required',
            'warehouse_name'            => 'required',
            'warehouse_address'         => 'required',
            'location_id'               => 'required',
        ]);
        

        $item = InvWarehouse::create([
            'warehouse_code'                => $fields['warehouse_code'], 
            'warehouse_type'                => $fields['warehouse_type'], 
            'warehouse_name'                => $fields['warehouse_name'],
            'warehouse_address'             => $fields['warehouse_address'], 
            'warehouse_location_id'         => $fields['location_id'],   
            'warehouse_phone'               => $request->warehouse_phone,
            'warehouse_remark'              => $request->warehouse_remark,
            'created_id'                    => Auth::id(),
            'data_state'                    => 0
        ]);

        $msg = 'Tambah Gudang Berhasil';
        return redirect('/warehouse/add')->with('msg',$msg);
    }

    public function editInvWarehouse($warehouse_id)
    {
        $location = InvWarehouseLocation::where('inv_warehouse_location.data_state',0)
        ->join('core_city','inv_warehouse_location.city_id','core_city.city_id')
        ->pluck('city_name', 'warehouse_location_id');

        $warehouse = InvWarehouse::where('warehouse_id',$warehouse_id)->first();

        $province = CoreProvince::where('data_state', 0)
        ->pluck('province_name', 'province_id');

        $city = CoreCity::where('data_state', 0)
        ->pluck('city_name', 'city_id');

        return view('content/InvWarehouse/FormEditInvWarehouse',compact('warehouse', 'location', 'province', 'city'));
    }

    public function processEditInvWarehouse(Request $request)
    {
        $fields = $request->validate([
            'warehouse_id'              => 'required',
            'warehouse_code'            => 'required',
            'warehouse_type'            => 'required',
            'warehouse_name'            => 'required',
            'warehouse_address'         => 'required',
            'location_id'               => 'required',
        ]);

        $item = InvWarehouse::findOrFail($fields['warehouse_id']);
        $item->warehouse_code          = $fields['warehouse_code'];
        $item->warehouse_type          = $fields['warehouse_type'];
        $item->warehouse_name          = $fields['warehouse_name'];
        $item->warehouse_address       = $fields['warehouse_address'];
        $item->warehouse_location_id   = $fields['location_id'];
        $item->warehouse_phone         = $request->warehouse_phone;
        $item->warehouse_remark        = $request->warehouse_remark;

        if($item->save()){
            $msg = 'Edit Gudang Berhasil';
            return redirect('/warehouse')->with('msg',$msg);
        }else{
            $msg = 'Edit Gudang Gagal';
            return redirect('/warehouse')->with('msg',$msg);
        }
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
}
