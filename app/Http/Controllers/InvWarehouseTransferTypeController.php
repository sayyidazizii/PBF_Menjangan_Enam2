<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\InvWarehouse;
use App\Models\InvWarehouseTransferType;
use App\Models\InvWarehouseLocation;
use App\Models\CoreProvince;
use App\Models\CoreCity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InvWarehouseTransferTypeController extends Controller
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
        $invwarehousetransfertype = InvWarehouseTransferType::where('data_state','=',0)->get();

        return view('content/InvWarehouseTransferType/ListInvWarehouseTransferType',compact('invwarehousetransfertype'));
    }

    public function addInvWarehouseTransferType(Request $request)
    {

        return view('content/InvWarehouseTransferType/FormAddInvWarehouseTransferType');
    }

    public function editInvWarehouseTransferType($warehouse_transfer_type_id)
    {
        $warehousetransfertype = InvWarehouseTransferType::findOrFail($warehouse_transfer_type_id);
        return view('content/InvWarehouseTransferType/FormEditInvWarehouseTransferType', compact('warehousetransfertype'));
    }

    public function processAddInvWarehouseTransferType(Request $request)
    {
        $fields = $request->validate([
            'warehouse_transfer_type_name'           => 'required',
        ]);

        $item = array(
            'warehouse_transfer_type_name'      => $fields['warehouse_transfer_type_name'],
            'warehouse_transfer_type_remark'    => $request->warehouse_transfer_type_remark,
            'created_id'                        => Auth::id(),
            'data_state'                        => 0
        );

        if(InvWarehouseTransferType::create($item)){
            $msg = 'Tambah Tipe Transfer Gudang Berhasil';
            return redirect('/warehouse-transfer-type/add')->with('msg',$msg);
        }else{
            $msg = 'Tambah Tipe Transfer Gudang Gagal';
            return redirect('/warehouse-transfer-type/add')->with('msg',$msg);
        }
    }

    public function processEditInvWarehouseTransferType(Request $request)
    {
        $fields = $request->validate([
            'warehouse_transfer_type_id'    => 'required',
            'warehouse_transfer_type_name'  => 'required',
        ]);

        $item = InvWarehouseTransferType::findOrFail($fields['warehouse_transfer_type_id']);
        $item->warehouse_transfer_type_name          = $fields['warehouse_transfer_type_name'];
        $item->warehouse_transfer_type_remark        = $request->warehouse_transfer_type_remark;

        if($item->save()){
            $msg = 'Edit Tipe Transfer Gudang Berhasil';
            return redirect('/warehouse-transfer-type')->with('msg',$msg);
        }else{
            $msg = 'Edit Tipe Transfer Gudang Gagal';
            return redirect('/warehouse-transfer-type/edit/'.$fields['warehouse_transfer_type_id'])->with('msg',$msg);
        }
    }

    public function deleteInvWarehouseTransferType($warehouse_transfer_type_id)
    {
        $item = InvWarehouseTransferType::findOrFail($warehouse_transfer_type_id);
        $item->data_state = 1;
        if($item->save())
        {
            $msg = 'Hapus Tipe Transfer Gudang Berhasil';
        }else{
            $msg = 'Hapus Tipe Transfer Gudang Gagal';
        }

        return redirect('/warehouse-transfer-type')->with('msg',$msg);
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
