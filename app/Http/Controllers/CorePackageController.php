<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\CorePackage;
use Illuminate\Foundation\Auth\RegistersCorePackages;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CorePackageController extends Controller
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
        $corepackage = CorePackage::where('data_state','=',0)->get();
        return view('content/CorePackage/ListCorePackage',compact('corepackage'));
    }

    public function addCorePackage(Request $request)
    {
        return view('content/CorePackage/FormAddCorePackage');
    }

    public function processAddCorePackage(Request $request)
    {
        $fields = $request->validate([
            'package_name'  => 'required',
        ]);

        $user = CorePackage::create([
            'package_name'  => $fields['package_name'],
            'created_id'  => Auth::id(),
        ]);

        $msg = 'Tambah Package Berhasil';
        return redirect('/core-package/add')->with('msg',$msg);
    }

    public function editCorePackage($package_id)
    {
        $corepackage = CorePackage::where('package_id',$package_id)->first();
        return view('content/CorePackage/FormEditCorePackage',compact('corepackage', 'package_id'));
    }

    public function processEditCorePackage(Request $request)
    {
        $fields = $request->validate([
            'package_id'                  => 'required',
            'package_name'                => 'required',
        ]);

        $package = CorePackage::findOrFail($fields['package_id']);
        $package->package_name = $fields['package_name'];

        if($package->save()){
            $msg = 'Edit Package Berhasil';
            return redirect('/core-package')->with('msg',$msg);
        }else{
            $msg = 'Edit Package Gagal';
            return redirect('/core-package')->with('msg',$msg);
        }
    }

    public function deleteCorePackage($package_id)
    {
        $package = CorePackage::findOrFail($package_id);
        $package->data_state = 1;
        if($package->save())
        {
            $msg = 'Hapus Package Berhasil';
        }else{
            $msg = 'Hapus Package Gagal';
        }

        return redirect('/core-package')->with('msg',$msg);
    }
}
