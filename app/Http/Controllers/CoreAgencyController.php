<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\CoreAgency;
use App\Models\CoreProvince;
use App\Models\CoreCity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CoreAgencyController extends Controller
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
        $coreagency = CoreAgency::where('data_state','=',0)->get();

        return view('content/CoreAgency/ListCoreAgency',compact('coreagency'));
    }

    public function addCoreAgency(Request $request)
    {
        return view('content/CoreAgency/FormAddCoreAgency');
    }

    public function processAddCoreAgency(Request $request)
    {
        $fields = $request->validate([
            'agency_name' => 'required',
        ]);
        

        $item = CoreAgency::create([
            'agency_name'                           => $fields['agency_name'], 
            'agency_address'                        => $request->agency_address,
            'agency_phone_number'                   => $request->agency_phone_number,
            'agency_code'                           => $request->agency_code,
            'agency_email'                          => $request->agency_email,
            'agency_contact_person'                 => $request->agency_contact_person,
            'agency_profit_sharing_percentage'      => $request->agency_profit_sharing_percentage,
            'agency_remark'                         => $request->agency_remark,
            'created_id'                            => Auth::id(),
            'data_state'                            => 0
        ]);

        $msg = 'Tambah Agensi Berhasil';
        return redirect('/agency/add')->with('msg',$msg);
    }

    public function editCoreAgency($agency_id)
    {
        $agency = CoreAgency::where('agency_id',$agency_id)->first();

        return view('content/CoreAgency/FormEditCoreAgency',compact('agency'));
    }

    public function processEditCoreAgency(Request $request)
    {
        $fields = $request->validate([
            'agency_id'   => 'required',
            'agency_name' => 'required',
        ]);

        $item = CoreAgency::findOrFail($fields['agency_id']);
        $item->agency_name                           = $fields['agency_name'];
        $item->agency_address                        = $request->agency_address;
        $item->agency_phone_number                   = $request->agency_phone_number;
        $item->agency_code                           = $request->agency_code;
        $item->agency_email                          = $request->agency_email;
        $item->agency_contact_person                 = $request->agency_contact_person;
        $item->agency_profit_sharing_percentage      = $request->agency_profit_sharing_percentage;
        $item->agency_remark                         = $request->agency_remark;

        if($item->save()){
            $msg = 'Edit Agensi Berhasil';
            return redirect('/agency')->with('msg',$msg);
        }else{
            $msg = 'Edit Agensi Gagal';
            return redirect('/agency')->with('msg',$msg);
        }
    }

    public function deleteCoreAgency($item_unit_id)
    {
        $item = CoreAgency::findOrFail($item_unit_id);
        $item->data_state = 1;
        if($item->save())
        {
            $msg = 'Hapus Agensi Berhasil';
        }else{
            $msg = 'Hapus Agensi Gagal';
        }

        return redirect('/agency')->with('msg',$msg);
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
