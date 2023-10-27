<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\CoreGrade;
use Illuminate\Foundation\Auth\RegistersCoreGrades;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CoreGradeController extends Controller
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
        $coregrade = CoreGrade::where('data_state','=',0)->get();
        return view('content/CoreGrade/ListCoreGrade',compact('coregrade'));
    }

    public function addCoreGrade(Request $request)
    {
        return view('content/CoreGrade/FormAddCoreGrade');
    }

    public function processAddCoreGrade(Request $request)
    {
        $fields = $request->validate([
            'grade_name'  => 'required',
        ]);

        $user = CoreGrade::create([
            'grade_name'  => $fields['grade_name'],
            'created_id'  => Auth::id(),
        ]);

        $msg = 'Tambah Grade Berhasil';
        return redirect('/core-grade/add')->with('msg',$msg);
    }

    public function editCoreGrade($grade_id)
    {
        $coregrade = CoreGrade::where('grade_id',$grade_id)->first();
        return view('content/CoreGrade/FormEditCoreGrade',compact('coregrade', 'grade_id'));
    }

    public function processEditCoreGrade(Request $request)
    {
        $fields = $request->validate([
            'grade_id'                  => 'required',
            'grade_name'                => 'required',
        ]);

        $grade = CoreGrade::findOrFail($fields['grade_id']);
        $grade->grade_name = $fields['grade_name'];

        if($grade->save()){
            $msg = 'Edit Grade Berhasil';
            return redirect('/core-grade')->with('msg',$msg);
        }else{
            $msg = 'Edit Grade Gagal';
            return redirect('/core-grade')->with('msg',$msg);
        }
    }

    public function deleteCoreGrade($grade_id)
    {
        $grade = CoreGrade::findOrFail($grade_id);
        $grade->data_state = 1;
        if($grade->save())
        {
            $msg = 'Hapus Grade Berhasil';
        }else{
            $msg = 'Hapus Grade Gagal';
        }

        return redirect('/core-grade')->with('msg',$msg);
    }
}
