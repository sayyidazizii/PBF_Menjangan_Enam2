<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\CoreCustomer;
use App\Models\CoreProvince;
use App\Models\CoreCity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CoreCustomerController extends Controller
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
        $corecustomer = CoreCustomer::where('data_state','=',0)->get();

        return view('content/CoreCustomer/ListCoreCustomer',compact('corecustomer'));
    }

    public function addCoreCustomer(Request $request)
    {
        $province = CoreProvince::where('data_state',0)
        ->pluck('province_name', 'province_id');

        return view('content/CoreCustomer/FormAddCoreCustomer', compact('province'));
    }

    public function processAddCoreCustomer(Request $request)
    {
        $fields = $request->validate([
            'customer_name' => 'required',
            'province_id'   => 'required',
            'city_id'       => 'required',
        ]);
        

        $item = CoreCustomer::create([
            'customer_name'                 => $fields['customer_name'], 
            'province_id'                   => $fields['province_id'],   
            'city_id'                       => $fields['city_id'],
            'customer_address'              => $request->customer_address,
            'customer_home_phone'           => $request->customer_home_phone,
            'customer_mobile_phone1'        => $request->customer_mobile_phone1,
            'customer_mobile_phone2'        => $request->customer_mobile_phone2,
            'customer_fax_number'           => $request->customer_fax_number,
            'customer_email'                => $request->customer_email,
            'customer_contact_person'       => $request->customer_contact_person,
            'customer_tax_no'               => $request->customer_tax_no,
            'customer_payment_terms'        => $request->customer_payment_terms,
            'customer_remark'               => $request->customer_remark,
            'created_id'                    => Auth::id(),
            'data_state'                    => 0
        ]);

        $msg = 'Tambah customer Berhasil';
        return redirect('/customer/add')->with('msg',$msg);
    }

    public function editCoreCustomer($customer_id)
    {
        $province = CoreProvince::where('data_state',0)
        ->pluck('province_name', 'province_id');

        $customer = CoreCustomer::where('customer_id',$customer_id)->first();

        $city     = CoreCity::where('data_state',0)
        ->where('province_id', $customer['province_id'])
        ->pluck('city_name', 'city_id');

        return view('content/CoreCustomer/FormEditCoreCustomer',compact('customer', 'province', 'city'));
    }

    public function processEditCoreCustomer(Request $request)
    {
        $fields = $request->validate([
            'customer_id'   => 'required',
            'customer_name' => 'required',
            'province_id'   => 'required',
            'city_id'       => 'required',
        ]);

        $item = CoreCustomer::findOrFail($fields['customer_id']);
        $item->customer_name                    = $fields['customer_name'];
        $item->province_id                      = $fields['province_id'];
        $item->city_id                          = $fields['city_id'];
        $item->customer_address                 = $request->customer_address;
        $item->customer_home_phone              = $request->customer_home_phone;
        $item->customer_mobile_phone1           = $request->customer_mobile_phone1;
        $item->customer_mobile_phone2           = $request->customer_mobile_phone2;
        $item->customer_fax_number              = $request->customer_fax_number;
        $item->customer_email                   = $request->customer_email;
        $item->customer_contact_person          = $request->customer_contact_person;
        $item->customer_tax_no                  = $request->customer_tax_no;
        $item->customer_payment_terms           = $request->customer_payment_terms;
        $item->customer_remark                  = $request->customer_remark;

        if($item->save()){
            $msg = 'Edit customer Berhasil';
            return redirect('/customer')->with('msg',$msg);
        }else{
            $msg = 'Edit customer Gagal';
            return redirect('/customer')->with('msg',$msg);
        }
    }

    public function deleteCoreCustomer($item_unit_id)
    {
        $item = CoreCustomer::findOrFail($item_unit_id);
        $item->data_state = 1;
        if($item->save())
        {
            $msg = 'Hapus customer Berhasil';
        }else{
            $msg = 'Hapus customer Gagal';
        }

        return redirect('/customer')->with('msg',$msg);
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
