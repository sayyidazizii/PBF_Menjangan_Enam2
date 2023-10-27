<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\CoreExpedition;
use App\Models\CoreCity;
use Illuminate\Foundation\Auth\RegistersCoreExpeditions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CoreExpeditionController extends Controller
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
        $coreexpedition = CoreExpedition::where('data_state','=',0)->get();
        return view('content/CoreExpedition/ListCoreExpedition',compact('coreexpedition'));
    }

    public function addCoreExpedition(Request $request)
    {
        $city = CoreCity::where('data_state', 0)
        ->pluck('city_name', 'city_id');

        $status = array(
            1 => 'Active',
            2 => 'Non Active',
            3 => 'All',
        );

        return view('content/CoreExpedition/FormAddCoreExpedition', compact('city', 'status'));
    }

    public function processAddCoreExpedition(Request $request)
    {
        $fields = $request->validate([
            'expedition_name'  => 'required',
            'expedition_route' => 'required',
        ]);

        $expedition = CoreExpedition::create([
            'expedition_name'               => $fields['expedition_name'],
            'expedition_route'              => $fields['expedition_route'],
            'expedition_code'               => $request->expedition_code,
            'expedition_address'            => $request->expedition_address,
            'expedition_city'               => $request->city_id,
            'expedition_home_phone'         => $request->expedition_home_phone,
            'expedition_mobile_phone1'      => $request->expedition_mobile_phone1,
            'expedition_mobile_phone2'      => $request->expedition_mobile_phone2,
            'expedition_fax_number'         => $request->expedition_fax_number,
            'expedition_email'              => $request->expedition_email,
            'expedition_person_in_charge'   => $request->expedition_person_in_charge,
            'expedition_status'             => $request->status_id,
            'expedition_remark'             => $request->expedition_remark,
            'created_id'                    => Auth::id(),
        ]);

        $msg = 'Tambah Expedition Berhasil';
        return redirect('/expedition/add')->with('msg',$msg);
    }

    public function editCoreExpedition($expedition_id)
    {
        $expedition = CoreExpedition::where('expedition_id',$expedition_id)->first();

        $city = CoreCity::where('data_state', 0)
        ->pluck('city_name', 'city_id');

        $status = array(
            1 => 'Active',
            2 => 'Non Active',
            3 => 'All',
        );

        return view('content/CoreExpedition/FormEditCoreExpedition',compact('expedition', 'expedition_id', 'city', 'status'));
    }

    public function processEditCoreExpedition(Request $request)
    {
        $fields = $request->validate([
            'expedition_id'                  => 'required',
            'expedition_name'                => 'required',
            'expedition_route'               => 'required',
        ]);

        $expedition = CoreExpedition::findOrFail($fields['expedition_id']);
        $expedition->expedition_name               = $fields['expedition_name'];
        $expedition->expedition_route              = $fields['expedition_route'];
        $expedition->expedition_code               = $request->expedition_code;
        $expedition->expedition_address            = $request->expedition_address;
        $expedition->expedition_city               = $request->city_id;
        $expedition->expedition_home_phone         = $request->expedition_home_phone;
        $expedition->expedition_mobile_phone1      = $request->expedition_mobile_phone1;
        $expedition->expedition_mobile_phone2      = $request->expedition_mobile_phone2;
        $expedition->expedition_fax_number         = $request->expedition_fax_number;
        $expedition->expedition_email              = $request->expedition_email;
        $expedition->expedition_person_in_charge   = $request->expedition_person_in_charge;
        $expedition->expedition_status             = $request->status_id;
        $expedition->expedition_remark             = $request->expedition_remark;

        if($expedition->save()){
            $msg = 'Edit Expedition Berhasil';
            return redirect('/expedition')->with('msg',$msg);
        }else{
            $msg = 'Edit Expedition Gagal';
            return redirect('/expedition')->with('msg',$msg);
        }
    }

    public function deleteCoreExpedition($expedition_id)
    {
        $expedition = CoreExpedition::findOrFail($expedition_id);
        $expedition->data_state = 1;
        if($expedition->save())
        {
            $msg = 'Hapus Expedition Berhasil';
        }else{
            $msg = 'Hapus Expedition Gagal';
        }

        return redirect('/expedition')->with('msg',$msg);
    }
}
