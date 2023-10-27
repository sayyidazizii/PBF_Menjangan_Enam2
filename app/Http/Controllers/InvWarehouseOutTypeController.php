<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\InvWarehouse;
use App\Models\InvWarehouseOut;
use App\Models\InvWarehouseOutType;
use App\Models\InvWarehouseLocation;
use App\Models\CoreProvince;
use App\Models\CoreCity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InvWarehouseOutTypeController extends Controller
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
        $invwarehouseouttype = InvWarehouseOutType::where('data_state','=',0)->get();

        return view('content/InvWarehouseOutType/ListInvWarehouseOutType',compact('invwarehouseouttype'));
    }

    public function addInvWarehouseOutType(Request $request)
    {

        return view('content/InvWarehouseOutType/FormAddInvWarehouseOutType');
    }

    public function processAddInvWarehouseOutType(Request $request)
    {
        $fields = $request->validate([
            'warehouse_out_type_name'       => 'required',
        ]);
        

        $item = InvWarehouseOutType::create([
            'warehouse_out_type_name'       => $fields['warehouse_out_type_name'], 
            'warehouse_out_type_remark'     => $request->warehouse_out_type_remark,
            'created_id'                    => Auth::id(),
            'data_state'                    => 0
        ]);

        $msg = 'Tambah Tipe Pengeluaran Gudang Berhasil';
        return redirect('/warehouse-out-type/add')->with('msg',$msg);
    }

    public function editInvWarehouseOutType($warehouse_out_type_id)
    {
        $warehouseouttype = InvWarehouseOutType::findOrFail($warehouse_out_type_id);

        return view('content/InvWarehouseOutType/FormEditInvWarehouseOutType',compact('warehouseouttype'));
    }

    public function processEditInvWarehouseOutType(Request $request)
    {
        $fields = $request->validate([
            'warehouse_out_type_id'              => 'required',
            'warehouse_out_type_name'            => 'required',
        ]);

        $item = InvWarehouseOutType::findOrFail($fields['warehouse_out_type_id']);
        $item->warehouse_out_type_name          = $fields['warehouse_out_type_name'];
        $item->warehouse_out_type_remark        = $request->warehouse_out_type_remark;

        if($item->save()){
            $msg = 'Edit Tipe Pengeluaran Gudang Berhasil';
            return redirect('/warehouse-out-type')->with('msg',$msg);
        }else{
            $msg = 'Edit Tipe Pengeluaran Gudang Gagal';
            return redirect('/warehouse-out-type')->with('msg',$msg);
        }
    }

    public function deleteInvWarehouseOutType($warehouse_out_type_id)
    {
        $item = InvWarehouseOutType::findOrFail($warehouse_out_type_id);
        $item->data_state        = 1;

        if($item->save()){
            $msg = 'Hapus Tipe Pengeluaran Gudang Berhasil';
            return redirect('/warehouse-out-type')->with('msg',$msg);
        }else{
            $msg = 'Hapus Tipe Pengeluaran Gudang Gagal';
            return redirect('/warehouse-out-type')->with('msg',$msg);
        }
    }
}
