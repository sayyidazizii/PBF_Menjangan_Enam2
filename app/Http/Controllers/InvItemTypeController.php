<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\AcctAccount;
use App\Models\InvItemType;
use App\Models\InvItemCategory;
use App\Models\InvItemUnit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class InvItemTypeController extends Controller
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
        $invitemtype = InvItemType::where('data_state','=',0)->get();

        // dd($invitemtype);
        return view('content/InvItemType/ListInvItemType',compact('invitemtype'));
    }

    public function addInvItemType(Request $request)
    {
        $acctaccountcode    = AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))
        ->where('acct_account.data_state','=','0')
        ->where('parent_account_status', 0)
        ->get()
        ->pluck('full_name','account_id');

        $inv_unit = InvItemUnit::where('data_state','=','0')
        ->get()
        ->pluck('item_unit_name','item_unit_id');

        $invitemcategory = InvItemCategory::where('data_state','=',0)->get();

        $null_item_unit_1 = Session::get('item_unit_1');
        $null_item_unit_2 = Session::get('item_unit_2');
        $null_item_unit_3 = Session::get('item_unit_3');
        return view('content/InvItemType/FormAddInvItemType', compact('null_item_unit_1','null_item_unit_2','null_item_unit_3', 'inv_unit', 'invitemcategory', 'acctaccountcode'));
    }

    public function processAddInvItemType(Request $request)
    {
        $fields = $request->validate([
            'item_type_name'            => 'required',
            'item_category_id'          => 'required',
            // 'item_type_expired_time'    => 'required',
        ]);

        $user = InvItemType::create([
            'item_type_name'                => $fields['item_type_name'],   
            'item_category_id'              => $fields['item_category_id'],
            // 'item_type_expired_time'        => $fields['item_type_expired_time'],
            'item_unit_1'                   => $request->item_unit_1,
            'item_quantity_default_1'       => $request->item_quantity_default_1,
            'item_weight_1'                 => $request->item_weight_1,
            'item_unit_2'                   => $request->item_unit_2,
            'item_quantity_default_2'       => $request->item_quantity_default_2,
            'item_weight_2'                 => $request->item_weight_2,
            'item_unit_3'                   => $request->item_unit_3,
            'item_quantity_default_3'       => $request->item_quantity_default_3,
            'item_weight_3'                 => $request->item_weight_3,
            'hpp_account_id'                => $request->hpp_account_id,
            'purchase_account_id'           => $request->purchase_account_id,
            'purchase_return_account_id'    => $request->purchase_return_account_id,
            'purchase_discount_account_id'  => $request->purchase_discount_account_id,
            'sales_account_id'              => $request->sales_account_id,
            'sales_return_account_id'       => $request->sales_return_account_id,
            'sales_discount_account_id'     => $request->sales_discount_account_id,
            'inv_account_id'                => $request->inv_account_id,
            'inv_return_account_id'         => $request->inv_return_account_id,
            'inv_discount_account_id'       => $request->inv_discount_account_id,
            'created_id'                    => Auth::id()
        ]);

        $msg = 'Tambah Tipe Barang Berhasil';
        return redirect('/inv-item-type/add')->with('msg',$msg);
    }

    public function editInvItemType($item_type_id)
    {
        $acctaccountcode    = AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))
        ->where('acct_account.data_state','=','0')
        ->where('parent_account_status', 0)
        ->get()
        ->pluck('full_name','account_id');
        $invitemtype = InvItemType::where('item_type_id',$item_type_id)->first();
        $invitemcategory = InvItemCategory::where('data_state','=',0)->get()->pluck('item_category_name','item_category_id');

        $inv_unit = InvItemUnit::where('data_state','=','0')
        ->get()
        ->pluck('item_unit_name','item_unit_id');

        
        $null_item_unit_1 = Session::get('item_unit_1');
        $null_item_unit_2 = Session::get('item_unit_2');
        $null_item_unit_3 = Session::get('item_unit_3');

        return view('content/InvItemType/FormEditInvItemType',compact('null_item_unit_1', 'null_item_unit_2', 'null_item_unit_3', 'inv_unit','invitemtype', 'item_type_id', 'invitemcategory', 'acctaccountcode'));
    }

    public function processEditInvItemType(Request $request)
    {
        $fields = $request->validate([
            'item_type_id'                  => 'required',
            'item_type_name'                => 'required',
            'item_category_id'              => 'required',
            // 'item_type_expired_time'        => 'required',
        ]);

        $itemtype = InvItemType::findOrFail($fields['item_type_id']);
        $itemtype->item_type_name                   = $fields['item_type_name'];
        $itemtype->item_category_id                 = $fields['item_category_id'];
        // $itemtype->item_type_expired_time           = $fields['item_type_expired_time'];
        $itemtype->item_unit_1                      = $request->item_unit_1;
        $itemtype->item_quantity_default_1          = $request->item_quantity_default_1;
        $itemtype->item_weight_1                    = $request->item_weight_1;
        $itemtype->item_unit_2                      = $request->item_unit_2;
        $itemtype->item_quantity_default_2          = $request->item_quantity_default_2;
        $itemtype->item_weight_2                    = $request->item_weight_2;
        $itemtype->item_unit_3                      = $request->item_unit_3;
        $itemtype->item_quantity_default_3          = $request->item_quantity_default_3;
        $itemtype->item_weight_3                    = $request->item_weight_3;
        $itemtype->hpp_account_id                   = $request->hpp_account_id;
        $itemtype->purchase_account_id              = $request->purchase_account_id;
        $itemtype->purchase_return_account_id       = $request->purchase_return_account_id;
        $itemtype->purchase_discount_account_id     = $request->purchase_discount_account_id;
        $itemtype->sales_account_id                 = $request->sales_account_id;
        $itemtype->sales_return_account_id          = $request->sales_return_account_id;
        $itemtype->sales_discount_account_id        = $request->sales_discount_account_id;
        $itemtype->inv_account_id                   = $request->inv_account_id;
        $itemtype->inv_return_account_id            = $request->inv_return_account_id;
        $itemtype->inv_discount_account_id          = $request->inv_discount_account_id;

        if($itemtype->save()){
            $msg = 'Edit Tipe Barang Berhasil';
            return redirect('/inv-item-type')->with('msg',$msg);
        }else{
            $msg = 'Edit Tipe Barang Gagal';
            return redirect('/inv-item-type')->with('msg',$msg);
        }
    }

    public function deleteInvItemType($item_type_id)
    {
        $itemtype = InvItemType::findOrFail($item_type_id);
        $itemtype->data_state = 1;
        if($itemtype->save())
        {
            $msg = 'Hapus Tipe Barang Berhasil';
        }else{
            $msg = 'Hapus Tipe Barang Gagal';
        }

        return redirect('/inv-item-type')->with('msg',$msg);
    }

    public function addInvItemCategory(Request $request){
        $item_category_name = $request->item_category_name;
        $data='';
        
        $itemcategory = InvItemCategory::create([  
            'item_category_name'  => $item_category_name,
            'created_id'          => Auth::id()
        ]);

        $invitemcategory = InvItemCategory::where('data_state', 0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($invitemcategory as $mp){
            $data .= "<option value='$mp[item_category_id]'>$mp[item_category_name]</option>\n";	
        }

        return $data;
    }

    public function getCategoryName($item_category_id){
        $category = InvItemCategory::findOrFail($item_category_id);

        return $category['item_category_name'];
    }

    public function getUnitName($id){
        $unit = InvItemUnit::where('item_unit_id', $id)
        ->first();

        if($unit == null){
            return '';
        }
        return $unit['item_unit_name'];
    }
}
