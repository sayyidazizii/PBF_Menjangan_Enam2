<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\PurchaseInvoice;
use App\Models\CoreSupplier;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctDebtCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if(!Session::get('start_date')){
            $start_date     = date('Y-m-d');
        }else{
            $start_date = Session::get('start_date');
        }

        if(!Session::get('end_date')){
            $end_date     = date('Y-m-d');
        }else{
            $end_date = Session::get('end_date');
        }

        $supplier = CoreSupplier::where('data_state','=',0)
        ->get();

        foreach($supplier as $key => $val){
            $total_amount = PurchaseInvoice::where('supplier_id', $val['supplier_id'])
            ->where('purchase_invoice_date', '>=', $start_date)
            ->where('purchase_invoice_date', '<=', $end_date)
            ->sum('total_amount');
            
            $paid_amount = PurchaseInvoice::where('supplier_id', $val['supplier_id'])
            ->where('purchase_invoice_date', '>=', $start_date)
            ->where('purchase_invoice_date', '<=', $end_date)
            ->sum('paid_amount');
            
            $owing_amount = PurchaseInvoice::where('supplier_id', $val['supplier_id'])
            ->where('purchase_invoice_date', '>=', $start_date)
            ->where('purchase_invoice_date', '<=', $end_date)
            ->sum('owing_amount');

            $val['total_amount']    = $total_amount;
            $val['paid_amount']     = $paid_amount;
            $val['owing_amount']    = $owing_amount;
        }

        return view('content/AcctDebtCard/ListDebtCard',compact('supplier', 'start_date', 'end_date'));
    }

    public function filter(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/debt-card');
    }

    public function processPrinting($supplier_id){

        if(!Session::get('start_date')){
            $start_date     = date('Y-m-d');
        }else{
            $start_date = Session::get('start_date');
        }

        if(!Session::get('end_date')){
            $end_date     = date('Y-m-d');
        }else{
            $end_date = Session::get('end_date');
        }

        $supplier = CoreSupplier::findOrFail($supplier_id);

        $purchase_invoice = PurchaseInvoice::where('supplier_id', $supplier_id)
        ->where('purchase_invoice_date', '>=', $start_date)
        ->where('purchase_invoice_date', '<=', $end_date)
        ->get();
        
        $pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(10, 10, 10, 10);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 8);

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td><div style=\"text-align: center; font-size:14px; font-weight: bold\">KARTU HUTANG PEMASOK</div></td>
            </tr>
        </table>
        <br>
        <br>
        <br>
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td width=\"10%\"><div style=\"text-align: lef=ft; font-size:12px;\">Pemasok</div></td>
                <td width=\"5%\"><div style=\"text-align: center; font-size:12px;\">:</div></td>
                <td width=\"75%\"><div style=\"text-align: left; font-size:12px;\">".$supplier['supplier_name']."</div></td>
            </tr>
            <tr>
                <td width=\"10%\"><div style=\"text-align: lef=ft; font-size:12px;\">Periode</div></td>
                <td width=\"5%\"><div style=\"text-align: center; font-size:12px;\">:</div></td>
                <td width=\"75%\"><div style=\"text-align: left; font-size:12px;\">".date('d-m-Y', strtotime($start_date)).' s/d '.date('d-m-Y', strtotime($end_date))."</div></td>
            </tr>
        </table>
        <br>
        <br>
        <br>
        <table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">
            <tr>
                <td width=\"5%\"><div style=\"text-align: center; font-size:12px; font-weight: bold;\">No</div></td>
                <td width=\"20%\"><div style=\"text-align: center; font-size:12px; font-weight: bold;\">No Purchase Invoice</div></td>
                <td width=\"12%\"><div style=\"text-align: center; font-size:12px; font-weight: bold;\">Tanggal</div></td>
                <td width=\"24%\"><div style=\"text-align: center; font-size:12px; font-weight: bold;\">Keterangan</div></td>
                <td width=\"13%\"><div style=\"text-align: center; font-size:12px; font-weight: bold;\">Pembelian</div></td>
                <td width=\"13%\"><div style=\"text-align: center; font-size:12px; font-weight: bold;\">Pembayaran</div></td>
                <td width=\"13%\"><div style=\"text-align: center; font-size:12px; font-weight: bold;\">Saldo Akhir</div></td>
            </tr>";

        $no             = 1;
        $total_amount   = 0;
        $paid_amount    = 0;
        $owing_amount   = 0;
        foreach($purchase_invoice as $key => $val){
            $tbl .= "
            <tr>
                <td width=\"5%\"><div style=\"text-align: center; font-size:12px;\">".$no.".</div></td>
                <td width=\"20%\"><div style=\"text-align: center; font-size:12px;\">".$val['purchase_invoice_no']."</div></td>
                <td width=\"12%\"><div style=\"text-align: center; font-size:12px;\">".date('d-m-Y', strtotime($val['purchase_invoice_date']))."</div></td>
                <td width=\"24%\"><div style=\"text-align: center; font-size:12px;\">".$val['purchase_invoice_remark']."</div></td>
                <td width=\"13%\"><div style=\"text-align: right; font-size:12px;\">".number_format($val['total_amount'], 2)."</div></td>
                <td width=\"13%\"><div style=\"text-align: right; font-size:12px;\">".number_format($val['paid_amount'], 2)."</div></td>
                <td width=\"13%\"><div style=\"text-align: right; font-size:12px;\">".number_format($val['owing_amount'], 2)."</div></td>
            </tr>";

            $total_amount   += $val['total_amount'];
            $paid_amount    += $val['paid_amount'];
            $owing_amount   += $val['owing_amount'];
            $no++;
        }
        $tbl .= "
            <tr>
                <td colspan=\"4\"><div style=\"text-align: center; font-size:12px; font-weight: bold;\">Total</div></td>
                <td><div style=\"text-align: right; font-size:12px; font-weight: bold;\">".number_format($total_amount, 2)."</div></td>
                <td><div style=\"text-align: right; font-size:12px; font-weight: bold;\">".number_format($paid_amount, 2)."</div></td>
                <td><div style=\"text-align: right; font-size:12px; font-weight: bold;\">".number_format($owing_amount, 2)."</div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl, true, false, false, false, '');
        
        // ob_clean();

        $filename = 'Kartu_Hutang_'.$supplier['supplier_name'].$start_date.' s/d '.$end_date.'.pdf';
        $pdf::Output($filename, 'I');

        return redirect('/ledger');
    }
}
