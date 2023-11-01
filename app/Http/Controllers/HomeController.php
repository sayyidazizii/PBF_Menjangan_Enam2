<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use App\Models\SystemMenuMapping;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
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
        $menus =  User::select('system_menu_mapping.*','system_menu.*')
        ->join('system_user_group','system_user_group.user_group_id','=','system_user.user_group_id')
        ->join('system_menu_mapping','system_menu_mapping.user_group_level','=','system_user_group.user_group_level')
        ->join('system_menu','system_menu.id_menu','=','system_menu_mapping.id_menu')
        ->where('system_user.user_id','=',Auth::id())
        ->orderBy('system_menu_mapping.id_menu','ASC')
        ->get();

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7);

        $purchaseinvoice = PurchaseInvoice::select('*')
        ->whereBetween('purchase_invoice_due_date' ,[$startDate, $endDate])
        ->simplePaginate(3);

        $salesinvoice = SalesInvoice::select('*')
        ->whereBetween('sales_invoice_due_date' ,[$startDate, $endDate])
        ->simplePaginate(3);
       // dd($salesinvoice);

       $purchaseinvoiceCount = count($purchaseinvoice);
       $salesinvoiceCount = count($salesinvoice);

       if($purchaseinvoiceCount == 0)
       {
        $purchaseinvoice = PurchaseInvoice::select('*')
        ->where('purchase_invoice_due_date' ,'<',$startDate)
        ->simplePaginate(3);
       }
       if($salesinvoiceCount == 0)
       {
        $salesinvoice = SalesInvoice::select('*')
        ->where('sales_invoice_due_date' ,'<',$startDate)
        ->simplePaginate(3);
       }
    


        return view('home',compact('menus','purchaseinvoice','salesinvoice'));
    }
}
