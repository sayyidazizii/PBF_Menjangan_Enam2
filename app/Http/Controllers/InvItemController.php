<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\InvItem;
use App\Models\CoreGrade;
use App\Models\InvItemType;
use App\Models\InvItemCategory;
use App\Models\InvItemUnit;
use App\Models\AcctAccount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class InvItemController extends Controller
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
        if(!Session::get('filteritemcategoryid')){
            $item_category_id = Session::get('filteritemcategoryid');
            $invitem = InvItem::where('data_state','=',0)->get();
        }else{
            $item_category_id = Session::get('filteritemcategoryid');
            $invitem = InvItem::where('data_state','=',0)->where('item_category_id', $item_category_id)->get();
        }

        $invitemcategory = InvItemCategory::where('data_state','=',0)->get()->pluck('item_category_name', 'item_category_id');
        return view('content/InvItem/ListInvItem',compact('invitem','invitemcategory', 'item_category_id'));
    }

    public function addInvItem(Request $request)
    {
        $acctaccountcode    = AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))
        ->where('acct_account.data_state','=','0')
        ->where('parent_account_status', 0)
        ->get()
        ->pluck('full_name','account_id');

        $invitemtype        = InvItemType::where('data_state', 0)->get();
        $invgrade           = CoreGrade::where('data_state', 0)->get()->pluck('grade_name', 'grade_id');
        $invitemunit        = InvItemUnit::where('data_state', 0)->get()->pluck('item_unit_name','item_unit_id');
        $invitemcategory    = InvItemCategory::where('data_state', 0)->get()->pluck('item_category_name','item_category_id');

        return view('content/InvItem/FormAddInvItem', compact('invgrade','invitemtype','invitemcategory', 'acctaccountcode', 'invitemunit'));
    }

    public function processAddInvItem(Request $request)
    {
        $fields = $request->validate([
            'item_type_id'                    => 'required',
            'item_category_id'                => 'required',
            // 'grade_id'                        => 'required',
            'item_unit_id'                    => 'required',
            // 'purchase_account_id'             => 'required',
            // 'purchase_return_account_id'      => 'required',
            // 'purchase_discount_account_id'    => 'required',
            // 'sales_account_id'                => 'required',
            // 'sales_return_account_id'         => 'required',
            // 'sales_discount_account_id'       => 'required',
        ]);

        $item = InvItem::create([
            'item_type_id'                  => $fields['item_type_id'], 
            'item_category_id'              => $fields['item_category_id'],   
            // 'grade_id'                      => $fields['grade_id'],
            'item_unit_id'                  => $fields['item_unit_id'],
            'item_remark'                   => $request->item_remark,
            'item_barcode'                  => $request->item_barcode,
            'purchase_account_id'           => $request->purchase_account_id,
            'purchase_return_account_id'    => $request->purchase_return_account_id,
            'purchase_discount_account_id'  => $request->purchase_discount_account_id,
            'sales_account_id'              => $request->sales_account_id,
            'sales_return_account_id'       => $request->sales_return_account_id,
            'sales_discount_account_id'     => $request->sales_discount_account_id,
            'inv_account_id'                => $request->inv_account_id,
            'inv_return_account_id'         => $request->inv_return_account_id,
            'inv_discount_account_id'       => $request->inv_discount_account_id,
            'hpp_account_id'                => $request->hpp_account_id,
            'created_id'                    => Auth::id()
        ]);

        $msg = 'Tambah Barang Berhasil';
        return redirect('/inv-item/add')->with('msg',$msg);
    }

    public function editInvItem($item_id)
    {
        $acctaccountcode            = AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))
        ->where('acct_account.data_state','=','0')
        ->where('parent_account_status', 0)
        ->get()
        ->pluck('full_name','account_id');

        $invitem = InvItem::where('item_id',$item_id)
        ->first();

        $invitemunit        = InvItemUnit::where('data_state', 0)
        ->pluck('item_unit_name','item_unit_id');

        $invgrade = CoreGrade::where('data_state','=',0)
        ->pluck('grade_name','grade_id');

        $invitemtype = InvItemType::where('data_state','=',0)
        ->where('item_category_id', $invitem['item_category_id'])
        ->pluck('item_type_name', 'item_type_id');

        $invitemcategory = InvItemCategory::where('data_state','=',0)->get()->pluck('item_category_name', 'item_category_id');

        return view('content/InvItem/FormEditInvItem',compact('invitem', 'item_id', 'invgrade', 'invitemtype', 'invitemcategory', 'acctaccountcode', 'invitemunit'));
    }

    public function processEditInvItem(Request $request)
    {
        $fields = $request->validate([
            'item_id'            => 'required',
            // 'grade_id'           => 'required',
            'item_type_id'       => 'required',
            'item_category_id'   => 'required',
        ]);

        $item = InvItem::findOrFail($fields['item_id']);
        // $item->grade_id                         = $fields['grade_id'];
        $item->item_type_id                     = $fields['item_type_id'];
        $item->item_category_id                 = $fields['item_category_id'];
        $item->item_unit_id                     = $request->item_unit_id;
        $item->item_remark                      = $request->item_remark;
        $item->item_barcode                     = $request->item_barcode;
        $item->purchase_account_id              = $request->purchase_account_id;
        $item->purchase_return_account_id       = $request->purchase_return_account_id;
        $item->purchase_discount_account_id     = $request->purchase_discount_account_id;
        $item->sales_account_id                 = $request->sales_account_id;
        $item->sales_return_account_id          = $request->sales_return_account_id;
        $item->sales_discount_account_id        = $request->sales_discount_account_id;
        $item->inv_account_id                   = $request->inv_account_id;
        $item->inv_return_account_id            = $request->inv_return_account_id;
        $item->inv_discount_account_id          = $request->inv_discount_account_id;
        $item->hpp_account_id                   = $request->hpp_account_id;

        if($item->save()){
            $msg = 'Edit Barang Berhasil';
            return redirect('/inv-item')->with('msg',$msg);
        }else{
            $msg = 'Edit Barang Gagal';
            return redirect('/inv-item')->with('msg',$msg);
        }
    }

    public function filterInvItem(Request $request){
        $item_category_id  = $request->item_category_id;

        Session::put('filteritemcategoryid', $item_category_id);

        return redirect('/inv-item');
    }

    public function deleteInvItem($item_id)
    {
        $item = InvItem::findOrFail($item_id);
        $item->data_state = 1;
        if($item->save())
        {
            $msg = 'Hapus Barang Berhasil';
        }else{
            $msg = 'Hapus Barang Gagal';
        }

        return redirect('/inv-item')->with('msg',$msg);
    }

    public function getProductCategoryName($item_category_id){
        $item = InvItemCategory::where('item_category_id',$item_category_id)->first();
        
        if($item == null){
            return "-";
        }
        return $item['item_category_name'];
    }

    public function getProductTypeName($item_type_id){
        $item = InvItemType::where('item_type_id',$item_type_id)->first();
        if($item == null){
            return "-";
        }
        return $item['item_type_name'];
    }

    public function getGradeName($grade_id){
        $item = CoreGrade::where('grade_id',$grade_id)->first();
        if($item == null){
            return "-";
        }
        return $item['grade_name'];
    }

    public function getItemUnitName($item_unit_id){
        $item = InvItemUnit::where('item_unit_id',$item_unit_id)->first();
        if($item == null){
            return "-";
        }
        return $item['item_unit_name'];
    }

    public function getCoreType(Request $request){
        $item_category_id = $request->item_category_id;
        $data='';

        $type = InvItemType::where('item_category_id', $item_category_id)
        ->where('data_state','=',0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($type as $mp){
            $data .= "<option value='$mp[item_type_id]'>$mp[item_type_name]</option>\n";	
        }

        return $data;
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

    public function addInvItemType(Request $request){
        $item_type_name         = $request->item_type_name;
        $item_category_id       = $request->item_category_id;
        $item_type_expired_time = $request->item_type_expired_time;
        $data='';
        
        $itemtype = InvItemType::create([  
            'item_type_name'            => $item_type_name,
            'item_category_id'          => $item_category_id,
            'item_type_expired_time'    => $item_type_expired_time,
            'created_id'                => Auth::id()
        ]);

        $invitemtype = InvItemType::where('data_state', 0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($invitemtype as $mp){
            $data .= "<option value='$mp[item_type_id]'>$mp[item_type_name]</option>\n";	
        }

        return $data;
    }

    public function addCoreGrade(Request $request){
        $grade_name = $request->grade_name;
        $data='';
        
        $coregrade = CoreGrade::create([  
            'grade_name'  => $grade_name,
            'created_id'  => Auth::id()
        ]);

        $coregrade = CoreGrade::where('data_state', 0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($coregrade as $mp){
            $data .= "<option value='$mp[grade_id]'>$mp[grade_name]</option>\n";	
        }

        return $data;
    }

    public function addInvItemUnit(Request $request){
        $item_unit_code             = $request->item_unit_code;
        $item_unit_name             = $request->item_unit_name;
        $item_unit_default_quantity = $request->item_unit_default_quantity;
        $item_unit_remark           = $request->item_unit_remark;
        $data='';
        
        $invitemunit = InvItemUnit::create([  
            'item_unit_code'              => $item_unit_code,
            'item_unit_name'              => $item_unit_name,
            'item_unit_default_quantity'  => $item_unit_default_quantity,
            'item_unit_remark'            => $item_unit_remark,
            'created_id'                  => Auth::id()
        ]);

        $invitemunit = InvItemUnit::where('data_state', 0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($invitemunit as $mp){
            $data .= "<option value='$mp[item_unit_id]'>$mp[item_unit_name]</option>\n";	
        }

        return $data;
    }
}
