<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\InvWarehouseLocation;
use App\Models\CoreProvince;
use App\Models\CoreCity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InvWarehouseLocationController extends Controller
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
        $invwarehouselocation = InvWarehouseLocation::where('data_state','=',0)->get();

        return view('content/InvWarehouseLocation/ListInvWarehouseLocation',compact('invwarehouselocation'));
    }

    public function addInvWarehouseLocation(Request $request)
    {
        $province = CoreProvince::where('data_state',0)
        ->pluck('province_name', 'province_id');

        return view('content/InvWarehouseLocation/FormAddInvWarehouseLocation', compact('province'));
    }

    public function processAddInvWarehouseLocation(Request $request)
    {
        $fields = $request->validate([
            'warehouse_location_code'   => 'required',
            'province_id'               => 'required',
            'city_id'                   => 'required',
        ]);
        

        $item = InvWarehouseLocation::create([
            'warehouse_location_code'       => $fields['warehouse_location_code'], 
            'province_id'                   => $fields['province_id'],   
            'city_id'                       => $fields['city_id'],
            'created_id'                    => Auth::id(),
            'data_state'                    => 0
        ]);

        $msg = 'Tambah Lokasi Gudang Berhasil';
        return redirect('/warehouse-location/add')->with('msg',$msg);
    }

    public function editInvWarehouseLocation($warehouse_location_id)
    {
        $province = CoreProvince::where('data_state',0)
        ->pluck('province_name', 'province_id');

        $warehouselocation = InvWarehouseLocation::where('warehouse_location_id',$warehouse_location_id)->first();

        $city     = CoreCity::where('data_state',0)
        ->where('province_id', $warehouselocation['province_id'])
        ->pluck('city_name', 'city_id');

        return view('content/InvWarehouseLocation/FormEditInvWarehouseLocation',compact('warehouselocation', 'province', 'city'));
    }

    public function processEditInvWarehouseLocation(Request $request)
    {
        $fields = $request->validate([
            'warehouse_location_id'     => 'required',
            'warehouse_location_code'   => 'required',
            'province_id'               => 'required',
            'city_id'                   => 'required',
        ]);

        $item = InvWarehouseLocation::findOrFail($fields['warehouse_location_id']);
        $item->warehouse_location_code          = $fields['warehouse_location_code'];
        $item->province_id                      = $fields['province_id'];
        $item->city_id                          = $fields['city_id'];

        if($item->save()){
            $msg = 'Edit Lokasi Gudang Berhasil';
            return redirect('/warehouse-location')->with('msg',$msg);
        }else{
            $msg = 'Edit Lokasi Gudang Gagal';
            return redirect('/warehouse-location')->with('msg',$msg);
        }
    }

    public function deleteInvWarehouseLocation($item_unit_id)
    {
        $item = InvWarehouseLocation::findOrFail($item_unit_id);
        $item->data_state = 1;
        if($item->save())
        {
            $msg = 'Hapus Lokasi Gudang Berhasil';
        }else{
            $msg = 'Hapus Lokasi Gudang Gagal';
        }

        return redirect('/warehouse-location')->with('msg',$msg);
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

    public function getCityName($city_id){
        $city = CoreCity::where('city_id', $city_id)
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
