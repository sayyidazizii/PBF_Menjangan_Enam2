<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\AcctAccount;
use App\Models\CoreGrade;
use App\Models\PreferenceCompany;
use Illuminate\Foundation\Auth\RegistersCoreGrades;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PreferenceCompanyController extends Controller
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
        $preferencecompany = PreferenceCompany::get();
        return view('content/PreferenceCompany/ListPreferenceCompany',compact('preferencecompany'));
    }

    public function editPreferenceCompany($company_id)
    {
        $preferencecompany = PreferenceCompany::findOrFail($company_id);

        $acctaccount    = AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))
        ->where('acct_account.data_state', 0)
        ->where('parent_account_status', 0)
        ->pluck('full_name','account_id');

        return view('content/PreferenceCompany/FormEditPreferenceCompany',compact('preferencecompany', 'company_id', 'acctaccount'));
    }

    public function processEditPreferenceCompany(Request $request)
    {
        $fields = $request->validate([
            'company_id'            => 'required',
            'company_name'          => 'required',
            'company_address'       => 'required',
            'company_phone_number'  => 'required',
            'company_mobile_number' => 'required',
            'company_email'         => 'required',
            'company_website'       => 'required',
        ]);

        $preferencecompany = PreferenceCompany::findOrFail($fields['company_id']);
        $preferencecompany->company_name            = $fields['company_name'];
        $preferencecompany->company_address         = $fields['company_address'];
        $preferencecompany->company_phone_number    = $fields['company_phone_number'];
        $preferencecompany->company_mobile_number   = $fields['company_mobile_number'];
        $preferencecompany->company_email           = $fields['company_email'];
        $preferencecompany->company_website         = $fields['company_website'];
        $preferencecompany->account_payable_id      = $request->account_payable_id;
        $preferencecompany->account_receivable_id   = $request->account_receivable_id;
        $preferencecompany->account_shortover_id    = $request->account_shortover_id;
        $preferencecompany->account_delivery_id     = $request->account_delivery_id;
        $preferencecompany->account_cash_id         = $request->account_cash_id;
        $preferencecompany->account_cash_on_way_id  = $request->account_cash_on_way_id;
        $preferencecompany->pharmacist_license_no   = $request->pharmacist_license_no;

        if($preferencecompany->save()){
            $msg = 'Edit Preferensi Perusahaan Berhasil';
            return redirect('/preference-company')->with('msg',$msg);
        }else{
            $msg = 'Edit Preferensi Perusahaan Gagal';
            return redirect('/preference-company')->with('msg',$msg);
        }
    }




    //ppn
    public function index_ppn()
    {
        $preferencecompany = PreferenceCompany::get();
        return view('content/PreferenceCompany/ListPpn',compact('preferencecompany'));
    }



    public function editppn($company_id)
    {
        $preferencecompany = PreferenceCompany::findOrFail($company_id);

        $acctaccount    = AcctAccount::select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))
        ->where('acct_account.data_state', 0)
        ->where('parent_account_status', 0)
        ->pluck('full_name','account_id');

        return view('content/PreferenceCompany/FormEditPpn',compact('preferencecompany', 'company_id', 'acctaccount'));
    }



    public function processEditPpnPreferenceCompany(Request $request)
    {
        $fields = $request->validate([
            'company_id'            => 'required',
            'company_name'          => 'required',
            'company_address'       => 'required',
            'company_phone_number'  => 'required',
            'company_mobile_number' => 'required',
            'company_email'         => 'required',
            'company_website'       => 'required',
        ]);

        $preferencecompany = PreferenceCompany::findOrFail($fields['company_id']);
        $preferencecompany->company_name            = $fields['company_name'];
        $preferencecompany->company_address         = $fields['company_address'];
        $preferencecompany->company_phone_number    = $fields['company_phone_number'];
        $preferencecompany->company_mobile_number   = $fields['company_mobile_number'];
        $preferencecompany->company_email           = $fields['company_email'];
        $preferencecompany->company_website         = $fields['company_website'];
        $preferencecompany->account_payable_id      = $request->account_payable_id;
        $preferencecompany->account_receivable_id   = $request->account_receivable_id;
        $preferencecompany->account_shortover_id    = $request->account_shortover_id;
        $preferencecompany->account_delivery_id     = $request->account_delivery_id;
        $preferencecompany->account_cash_id         = $request->account_cash_id;
        $preferencecompany->account_cash_on_way_id  = $request->account_cash_on_way_id;
        $preferencecompany->pharmacist_license_no   = $request->pharmacist_license_no;
        $preferencecompany->ppn_amount_in           = $request->ppn_amount_in;
        $preferencecompany->ppn_amount_out          = $request->ppn_amount_out;

        if($preferencecompany->save()){
            $msg = 'Edit Preferensi ppn Berhasil';
            return redirect('/ppn')->with('msg',$msg);
        }else{
            $msg = 'Edit Preferensi ppn Gagal';
            return redirect('/ppn')->with('msg',$msg);
        }
    }


}
