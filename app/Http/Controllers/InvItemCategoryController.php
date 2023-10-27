<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\InvItemCategory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class InvItemCategoryController extends Controller
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
        $invitemcategory = InvItemCategory::where('data_state','=',0)->get();
        return view('content/InvItemCategory/ListInvItemCategory',compact('invitemcategory'));
    }

    public function addInvItemCategory(Request $request)
    {
        return view('content/InvItemCategory/FormAddInvItemCategory');
    }

    public function processAddInvItemCategory(Request $request)
    {
        $fields = $request->validate([
            'item_category_name'  => 'required',
        ]);

        $user = InvItemCategory::create([
            'item_category_name'  => $fields['item_category_name'],
            'created_id'             => Auth::id(),
        ]);

        $msg = 'Tambah Kategori Barang Berhasil';
        return redirect('/inv-item-category/add')->with('msg',$msg);
    }

    public function editInvItemCategory($item_category_id)
    {
        $invitemcategory = InvItemCategory::where('item_category_id',$item_category_id)->first();
        return view('content/InvItemCategory/FormEditInvItemCategory',compact('invitemcategory', 'item_category_id'));
    }

    public function processEditInvItemCategory(Request $request)
    {
        $fields = $request->validate([
            'item_category_id'                  => 'required',
            'item_category_name'                => 'required',
        ]);

        $itemcategory = InvItemCategory::findOrFail($fields['item_category_id']);
        $itemcategory->item_category_name = $fields['item_category_name'];

        if($itemcategory->save()){
            $msg = 'Edit Kategori Barang Berhasil';
            return redirect('/inv-item-category')->with('msg',$msg);
        }else{
            $msg = 'Edit Kategori Barang Gagal';
            return redirect('/inv-item-category')->with('msg',$msg);
        }
    }

    public function deleteInvItemCategory($item_category_id)
    {
        $itemcategory = InvItemCategory::findOrFail($item_category_id);
        $itemcategory->data_state = 1;
        if($itemcategory->save())
        {
            $msg = 'Hapus Kategori Barang Berhasil';
        }else{
            $msg = 'Hapus Kategori Barang Gagal';
        }

        return redirect('/inv-item-category')->with('msg',$msg);
    }
}
