<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\InvItem;
use App\Models\CoreGrade;
use App\Models\InvItemType;
use App\Models\CoreSupplier;
use App\Models\CoreProvince;
use App\Models\CoreCity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CoreSupplierController extends Controller
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
        $coresupplier = CoreSupplier::where('data_state','=',0)->get();

        return view('content/CoreSupplier/ListCoreSupplier',compact('coresupplier'));
    }

    public function addCoreSupplier(Request $request)
    {
        $province = CoreProvince::where('data_state',0)
        ->pluck('province_name', 'province_id');

        return view('content/CoreSupplier/FormAddCoreSupplier', compact('province'));
    }

    public function processAddCoreSupplier(Request $request)
    {
        $fields = $request->validate([
            'supplier_name' => 'required',
            'province_id'   => 'required',
            'city_id'       => 'required',
        ]);
        

        $item = CoreSupplier::create([
            'supplier_name'                 => $fields['supplier_name'], 
            'province_id'                   => $fields['province_id'],   
            'city_id'                       => $fields['city_id'],
            'supplier_address'              => $request->supplier_address,
            'supplier_home_phone'           => $request->supplier_home_phone,
            'supplier_mobile_phone1'        => $request->supplier_mobile_phone1,
            'supplier_mobile_phone2'        => $request->supplier_mobile_phone2,
            'supplier_fax_number'           => $request->supplier_fax_number,
            'supplier_email'                => $request->supplier_email,
            'supplier_contact_person'       => $request->supplier_contact_person,
            'supplier_id_number'            => $request->supplier_id_number,
            'supplier_tax_no'               => $request->supplier_tax_no,
            'supplier_tax_no'               => $request->supplier_tax_no,
            'supplier_npwp_no'              => $request->supplier_npwp_no,
            'supplier_npwp_address'         => $request->supplier_npwp_address,
            'supplier_payment_terms'        => $request->supplier_payment_terms,
            'supplier_bank_acct_name'       => $request->supplier_bank_acct_name,
            'supplier_bank_acct_no'         => $request->supplier_bank_acct_no,
            'supplier_remark'               => $request->supplier_remark,
            'created_id'                    => Auth::id(),
            'data_state'                    => 0
        ]);

        $msg = 'Tambah Pemasok Berhasil';
        return redirect('/supplier')->with('msg',$msg);
    }

    public function editCoreSupplier($supplier_id)
    {
        $province = CoreProvince::where('data_state',0)
        ->pluck('province_name', 'province_id');

        $supplier = CoreSupplier::where('supplier_id',$supplier_id)->first();

        $city     = CoreCity::where('data_state',0)
        ->where('province_id', $supplier['province_id'])
        ->pluck('city_name', 'city_id');

        return view('content/CoreSupplier/FormEditCoreSupplier',compact('supplier', 'province', 'city'));
    }

    public function processEditCoreSupplier(Request $request)
    {
        $fields = $request->validate([
            'supplier_id'   => 'required',
            'supplier_name' => 'required',
            'province_id'   => 'required',
            'city_id'       => 'required',
        ]);

        $item = CoreSupplier::findOrFail($fields['supplier_id']);
        $item->supplier_name                    = $fields['supplier_name'];
        $item->province_id                      = $fields['province_id'];
        $item->city_id                          = $fields['city_id'];
        $item->supplier_address                 = $request->supplier_address;
        $item->supplier_home_phone              = $request->supplier_home_phone;
        $item->supplier_mobile_phone1           = $request->supplier_mobile_phone1;
        $item->supplier_mobile_phone2           = $request->supplier_mobile_phone2;
        $item->supplier_fax_number              = $request->supplier_fax_number;
        $item->supplier_email                   = $request->supplier_email;
        $item->supplier_contact_person          = $request->supplier_contact_person;
        $item->supplier_id_number               = $request->supplier_id_number;
        $item->supplier_tax_no                  = $request->supplier_tax_no;
        $item->supplier_npwp_no                 = $request->supplier_npwp_no;
        $item->supplier_npwp_address            = $request->supplier_npwp_address;
        $item->supplier_payment_terms           = $request->supplier_payment_terms;
        $item->supplier_bank_acct_name          = $request->supplier_bank_acct_name;
        $item->supplier_bank_acct_no            = $request->supplier_bank_acct_no;
        $item->supplier_remark                  = $request->supplier_remark;

        if($item->save()){
            $msg = 'Edit Pemasok Berhasil';
            return redirect('/supplier')->with('msg',$msg);
        }else{
            $msg = 'Edit Pemasok Gagal';
            return redirect('/supplier')->with('msg',$msg);
        }
    }

    public function deleteCoreSupplier($item_unit_id)
    {
        $item = CoreSupplier::findOrFail($item_unit_id);
        $item->data_state = 1;
        if($item->save())
        {
            $msg = 'Hapus Pemasok Berhasil';
        }else{
            $msg = 'Hapus Pemasok Gagal';
        }

        return redirect('/supplier')->with('msg',$msg);
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
}
