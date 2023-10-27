<?php

namespace App\Http\Controllers;

use App\Models\AcctAccount;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\InvWarehouse;
use App\Models\CoreSupplier;
use App\Models\InvItemType;
use App\Models\InvItemCategory;
use App\Models\InvItemUnit;
use App\Models\PurchaseOrderReturn;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrder;
use App\Models\InvItemStock;
use App\Models\PurchaseOrderReturnItem;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class PurchaseOrderReturnReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        
    }

    public function index()
    {
        Session::forget('acctjournalvoucherelements');
        Session::forget('dataarrayjournalvoucher');
        Session::forget('supplier_id');
        $supplier         = CoreSupplier::where('data_state','=',0)->get();

        if(!Session::get('start_date')){
            $start_date     = date('Y-m-d');
        }else{
            $start_date     = Session::get('start_date');
        }

        if(!Session::get('end_date')){
            $end_date       = date('Y-m-d');
        }else{
            $end_date       = Session::get('end_date');
        }

        if(!Session::get('supplier_id')){
            $supplier_id      = $supplier[0]['supplier_id'];
        }else{
            $supplier_id      = Session::get('supplier_id');
        }
        
        $coreSupplier         = CoreSupplier::where('core_supplier.data_state','=','0')->get()->pluck('supplier_name','supplier_id');
        // dd($supplier_id);

        $purchaseorderreturn = PurchaseOrderReturn::select('*')
        ->leftjoin('core_supplier', 'core_supplier.supplier_id', 'purchase_order_return.supplier_id')
        ->join('purchase_order_return_item', 'purchase_order_return_item.purchase_order_return_id', 'purchase_order_return.purchase_order_return_id')
        ->where('purchase_order_return.data_state', 0)
        ->where('purchase_order_return.purchase_order_return_date','>=',$start_date)
        ->where('purchase_order_return.purchase_order_return_date','<=',$end_date)
        ->where('purchase_order_return.supplier_id','=',$supplier_id)
        ->get();

      // dd($purchaseorderreturn);
       

        $company = PreferenceCompany::select('*')
            ->first();

        
        return view('content/PurchaseOrderReturnReport/ListPurchaseOrderReturn', compact('coreSupplier','start_date','end_date','supplier_id','purchaseorderreturn'));
    }


    public function filterPurchaseOrderReturnReport(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $supplier_id    = $request->supplier_id;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('supplier_id', $supplier_id);

        return redirect('/purchase-order-return-report');
    }
    

    public function getDiscount($purchase_order_item_id){
        $purchaseorder = PurchaseOrderItem::select('*')
        ->where('purchase_order_item_id',$purchase_order_item_id)
        ->first();

        return $purchaseorder['discount_percentage'];
    }

    public function getDiscountAmount($purchase_order_item_id){
        $purchaseorder = PurchaseOrderItem::select('*')
        ->where('purchase_order_item_id',$purchase_order_item_id)
        ->first();

        return $purchaseorder['discount_amount'];
    }
    public function getCost($purchase_order_item_id){
        $purchaseorder = PurchaseOrderItem::select('*')
        ->where('purchase_order_item_id',$purchase_order_item_id)
        ->first();

        return $purchaseorder['item_unit_cost'];
    }

    public function PrintPurchaseOrderReturnReport(){
        Session::forget('acctjournalvoucherelements');
        Session::forget('dataarrayjournalvoucher');
        Session::forget('dataprocessjournalvoucher');
        $supplier         = CoreSupplier::where('data_state','=',0)->get();

        if(!Session::get('start_date')){
            $start_date     = date('Y-m-d');
        }else{
            $start_date     = Session::get('start_date');
        }

        if(!Session::get('end_date')){
            $end_date       = date('Y-m-d');
        }else{
            $end_date       = Session::get('end_date');
        }

        if(!Session::get('supplier_id')){
            $supplier_id      = $supplier[0]['supplier_id'];
        }else{
            $supplier_id      = Session::get('supplier_id');
        }
        
        $coreSupplier         = CoreSupplier::where('core_supplier.data_state','=','0')->get()->pluck('supplier_name','supplier_id');
        // dd($supplier_id);

        $purchaseorderreturn = PurchaseOrderReturn::select('*')
        ->leftjoin('core_supplier', 'core_supplier.supplier_id', 'purchase_order_return.supplier_id')
        ->join('purchase_order_return_item', 'purchase_order_return_item.purchase_order_return_id', 'purchase_order_return.purchase_order_return_id')
        ->where('purchase_order_return.data_state', 0)
        ->where('purchase_order_return.purchase_order_return_date','>=',$start_date)
        ->where('purchase_order_return.purchase_order_return_date','<=',$end_date)
        ->where('purchase_order_return.supplier_id','=',$supplier_id)
        ->get();

      // dd($purchaseorderreturn);
       

        $company = PreferenceCompany::select('*')
            ->first();


         // Buat objek TCPDF baru
         $pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

         $pdf::SetPrintHeader(false);
         $pdf::SetPrintFooter(false);
 
         $pdf::SetMargins(10, 10, 10, 10); // put space of 10 on top
 
         $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);
 
         if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
             require_once(dirname(__FILE__).'/lang/eng.php');
             $pdf::setLanguageArray($l);
         }
 
         $pdf::SetFont('helvetica', 'B', 20);
 
         // $pdf::AddPage();
 
         $pdf::SetFont('helvetica', '', 8);

         //Render view ke string HTML
         $view = view('content/PurchaseOrderReturnReport/PrintPurchaseOrderReturnReport', compact('coreSupplier','start_date','end_date','supplier_id','purchaseorderreturn'));
         $html = $view->render();
 
         // Tambahkan konten HTML ke PDF
         $pdf::AddPage();
         $pdf::writeHTML($html, true, false, true, false, '');
 
         // Cetak PDF ke browser
         $pdf::Output('Laporan_Retur_Pembelian.pdf', 'I');
    }




}
