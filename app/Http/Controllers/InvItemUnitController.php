<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\InvItem;
use App\Models\CoreGrade;
use App\Models\InvItemType;
use App\Models\InvItemUnit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InvItemUnitController extends Controller
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
        $invitemunit = InvItemUnit::where('data_state','=',0)->get();

        return view('content/InvItemUnit/ListInvItemUnit',compact('invitemunit'));
    }

    public function addInvItemUnit(Request $request)
    {
        return view('content/InvItemUnit/FormAddInvItemUnit');
    }

    public function processAddInvItemUnit(Request $request)
    {
        $fields = $request->validate([
            'item_unit_code'                => 'required',
            'item_unit_name'                => 'required',
            // 'item_unit_default_quantity'    => 'required',
        ]);
        

        $item = InvItemUnit::create([
            'item_unit_code'                => $fields['item_unit_code'], 
            'item_unit_name'                => $fields['item_unit_name'],   
            // 'item_unit_default_quantity'    => $fields['item_unit_default_quantity'],
            'item_unit_remark'              => $request->item_unit_remark,
            'created_id'                    => Auth::id(),
            'data_state'                    => 0
        ]);

        $msg = 'Tambah Satuan Barang Berhasil';
        return redirect('/inv-item-unit/add')->with('msg',$msg);
    }

    public function editInvItemUnit($item_unit_id)
    {
        $invitemunit = InvItemUnit::where('item_unit_id',$item_unit_id)->first();

        return view('content/InvItemUnit/FormEditInvItemUnit',compact('invitemunit'));
    }

    public function processEditInvItemUnit(Request $request)
    {
        $fields = $request->validate([
            'item_unit_id'                      => 'required',
            'item_unit_code'                    => 'required',
            'item_unit_name'                    => 'required',
            // 'item_unit_default_quantity'        => 'required'
        ]);

        $item = InvItemUnit::findOrFail($fields['item_unit_id']);
        $item->item_unit_code                   = $fields['item_unit_code'];
        $item->item_unit_name                   = $fields['item_unit_name'];
        // $item->item_unit_default_quantity       = $fields['item_unit_default_quantity'];
        $item->item_unit_remark                 = $request->item_unit_remark;

        if($item->save()){
            $msg = 'Edit Satuan Barang Berhasil';
            return redirect('/inv-item-unit')->with('msg',$msg);
        }else{
            $msg = 'Edit Satuan Barang Gagal';
            return redirect('/inv-item-unit')->with('msg',$msg);
        }
    }

    public function deleteInvItemUnit($item_unit_id)
    {
        $item = InvItemUnit::findOrFail($item_unit_id);
        $item->data_state = 1;
        if($item->save())
        {
            $msg = 'Hapus Satuan Barang Berhasil';
        }else{
            $msg = 'Hapus Satuan Barang Gagal';
        }

        return redirect('/inv-item-unit')->with('msg',$msg);
    }
}
