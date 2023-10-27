<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\InvWarehouse;
use App\Models\InvWarehouseIn;
use App\Models\InvWarehouseInType;
use App\Models\InvWarehouseLocation;
use App\Models\CoreProvince;
use App\Models\CoreCity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InvWarehouseInTypeController extends Controller
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
        $invwarehouseintype = InvWarehouseInType::where('data_state','=',0)->get();

        return view('content/InvWarehouseInType/ListInvWarehouseInType',compact('invwarehouseintype'));
    }

    public function addInvWarehouseInType(Request $request)
    {

        return view('content/InvWarehouseInType/FormAddInvWarehouseInType');
    }

    public function processAddInvWarehouseInType(Request $request)
    {
        $fields = $request->validate([
            'warehouse_in_type_name'       => 'required',
        ]);
        

        $item = InvWarehouseInType::create([
            'warehouse_in_type_name'       => $fields['warehouse_in_type_name'], 
            'warehouse_in_type_remark'     => $request->warehouse_in_type_remark,
            'created_id'                    => Auth::id(),
            'data_state'                    => 0
        ]);

        // dd($item);

        $msg = 'Tambah Tipe Pemasukan Gudang Berhasil';
        return redirect('/warehouse-in-type/add')->with('msg',$msg);
    }

    public function editInvWarehouseInType($warehouse_in_type_id)
    {
        $warehouseintype = InvWarehouseInType::findOrFail($warehouse_in_type_id);

        return view('content/InvWarehouseInType/FormEditInvWarehouseInType',compact('warehouseintype'));
    }

    public function processEditInvWarehouseInType(Request $request)
    {
        $fields = $request->validate([
            'warehouse_in_type_id'              => 'required',
            'warehouse_in_type_name'            => 'required',
        ]);

        $item = InvWarehouseInType::findOrFail($fields['warehouse_in_type_id']);
        $item->warehouse_in_type_name          = $fields['warehouse_in_type_name'];
        $item->warehouse_in_type_remark        = $request->warehouse_in_type_remark;

        if($item->save()){
            $msg = 'Edit Tipe Penambahan Gudang Berhasil';
            return redirect('/warehouse-in-type')->with('msg',$msg);
        }else{
            $msg = 'Edit Tipe Penambahan Gudang Gagal';
            return redirect('/warehouse-in-type')->with('msg',$msg);
        }
    }

    public function deleteInvWarehouseInType($warehouse_in_type_id)
    {
        $item = InvWarehouseInType::findOrFail($warehouse_in_type_id);
        $item->data_state        = 1;

        if($item->save()){
            $msg = 'Hapus Tipe Penambahan Gudang Berhasil';
            return redirect('/warehouse-in-type')->with('msg',$msg);
        }else{
            $msg = 'Hapus Tipe Penambahan Gudang Gagal';
            return redirect('/warehouse-in-type')->with('msg',$msg);
        }
    }
}
