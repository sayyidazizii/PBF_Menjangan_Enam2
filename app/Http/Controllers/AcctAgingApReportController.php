<?php

namespace App\Http\Controllers;

use App\Models\CoreSupplier;
use App\Models\PurchaseInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AcctAgingApReportController extends Controller
{
    //
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

        $supplier_id = Session::get('supplier_id');

        Session::forget('purchaseinvoiceitem');
        Session::forget('purchaseinvoiceelements');

        $purchaseinvoice = PurchaseInvoice::join('purchase_order', 'purchase_order.purchase_order_id', 'purchase_invoice.purchase_order_id')
        ->where('purchase_invoice.data_state','=',0)
        ->where('purchase_invoice.purchase_invoice_date', '>=', $start_date)
        ->where('purchase_invoice.purchase_invoice_date', '<=', $end_date);
        if($supplier_id||$supplier_id!=null||$supplier_id!=''){
            $purchaseinvoice   = $purchaseinvoice->where('purchase_invoice.supplier_id', $supplier_id);
        }
        $purchaseinvoice       = $purchaseinvoice->get();

        $supplier = CoreSupplier::select('supplier_id', 'supplier_name')
        ->where('data_state', 0)
        ->pluck('supplier_name', 'supplier_id');

        return view('content.AcctAccountPayable.ListAgingAccountPayable',compact('purchaseinvoice', 'start_date', 'end_date', 'supplier_id', 'supplier'));
    }

    public function filterAcctAgingAp(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $supplier_id       = $request->supplier_id;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('supplier_id', $supplier_id);

        return redirect('/aging-account-payable');
    }

    public function resetFilterAcctAgingAp(){
        Session::forget('start_date');
        Session::forget('end_date');
        Session::forget('supplier_id');

        return redirect('/aging-account-payable');

    }


    
    // public function printKwitansi($sales_kwitansi_id){
    //     $saleskwitansi = SalesKwitansi::select('*')
    //     ->where('data_state', '=', 0)
    //     ->where('sales_kwitansi_id', '=', $sales_kwitansi_id)
    //     ->first();

    //     $saleskwitansiItem = SalesKwitansiItem::select('*')
    //     ->join('sales_invoice_item','sales_invoice_item.sales_invoice_id','sales_kwitansi_item.sales_invoice_id')
    //     ->where('sales_kwitansi_item.sales_kwitansi_id', '=', $sales_kwitansi_id)
    //     ->where('checked', '=', 1)
    //     ->groupBy('sales_invoice_item.sales_invoice_item_id')
    //     ->get();


    //     $company = PreferenceCompany::select('*')
    //         ->first();


    //     //pdf

    //     $pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);
    //     //$path = public_path('resources/assets/img/TTD.png');

    //     $pdf::SetPrintHeader(false);
    //     $pdf::SetPrintFooter(false);

    //     $pdf::SetMargins(10, 10, 10, 10); // put space of 10 on top

    //     $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

    //     if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    //         require_once(dirname(__FILE__) . '/lang/eng.php');
    //         $pdf::setLanguageArray($l);
    //     }

    //     $pdf::SetFont('helvetica', 'B', 20);

    //     $pdf::AddPage();

    //     $pdf::SetFont('helvetica', '', 8);

    //     $tbl = "
    //     <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
    //         <tr>

    //         <td>
    //             <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
    //                 <tr>
    //                     <td><div style=\"text-align: left; font-size:12px; font-weight: bold\">PBF MENJANGAN ENAM</div></td>
    //                 </tr>
    //                 <tr>
    //                     <td><div style=\"text-align: left; font-size:10px\">Jl.Puspowarno Raya No 55D Bojong Salaman, Semarang Barat</div></td>
    //                 </tr>
    //                 <tr>
    //                     <td><div style=\"text-align: left; font-size:10px\">APA : ISTI RAHMADANI,S.Farm, Apt</div></td>
    //                 </tr>
    //                 <tr>
    //                     <td><div style=\"text-align: left; font-size:10px\">" . $company['CDBO_no'] . "</div></td>
    //                 </tr>
    //                 <tr>
    //                     <td><div style=\"text-align: left; font-size:10px\">" . $company['distribution_no'] . "</div></td>
    //                 </tr>
    //                 <tr>
    //                     <td><div style=\"text-align: left; font-size:10px\">SIKA: 449.2/16/DPM-PTSP/SIKA.16/11/2019</div></td>
    //                 </tr>
    //             </table>
    //         </td>

    //         <td>
    //             <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
    //                 <tr>
    //                     <td style=\"text-align: right; font-size:14px; font-weight: bold\">
    //                     K W I T A N S I
    //                     </td>
                   
    //                 </tr>
    //             </table>
    //         </td>

    //         </tr>

    //     </table>
    //     <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
    //     <tr>
    //         <td>-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td>
    //     </tr>
    //     <tr>
    //         <td>
    //             Telah Terima Dari ".$this->getCustomerName($saleskwitansi['customer_id'])." 
    //         </td>
    //     </tr>
    //     <tr>
    //         <td>
    //             Guna Pembayaran Permintaan Barang dengan Rincian :
    //         </td>
    //     </tr>
    //     <tr>
    //     <td>-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td>
    // </tr>
    // </table>
    //     ";
    //     $pdf::writeHTML($tbl, true, false, false, false, '');

    //     $html2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
    //                     <tr style=\"text-align: center;\">
    //                         <td width=\"4%\" ><div style=\"text-align: center;\"></div></td>
    //                         <td width=\"20%\" ><div style=\"text-align: center;\"></div></td>
    //                         <td width=\"10%\" ><div style=\"text-align: center;\">Qty</div></td>
    //                         <td width=\"10%\" ><div style=\"text-align: center;\">Harga </div></td>
    //                         <td width=\"10%\" ><div style=\"text-align: center;\">JML</div></td>
    //                         <td width=\"9%\" ><div style=\"text-align: center;\">Diskon </div></td>
    //                         <td width=\"10%\" ><div style=\"text-align: center;\">Jumlah(DPP) </div></td>
    //                         <td width=\"10%\" ><div style=\"text-align: center;\">PPN </div></td>
    //                         <td width=\"10%\" ><div style=\"text-align: center;\">JML BAYAR </div></td>
    //                     </tr>";
    //     $no = 1;
    //     $totalppn = 0;
    //     $totalbayar = 0;
    //     $totaldpp = 0;
    //     foreach ($saleskwitansiItem as $key => $val) {
    //         $total = $val['item_unit_price'] * $val['quantity'];
    //         $diskon = $val['discount_A'] + $val['discount_B'];
    //         $dpp = $total - $diskon ; 
    //         $totaldpp += $total - $diskon ;
    //         $ppn = $this->getPpnItem($val['sales_delivery_note_item_id']);
    //         $totalppn += $this->getPpnItem($val['sales_delivery_note_item_id']);
    //         $totalbayar += $total - $diskon  + $ppn;
    //         $html2 .= "<tr>
    //                         <td>" . $no . "</td>
    //                         <td>".$this->getBpbNo($val['sales_invoice_id']) ."<br>".$this->getInvItemTypeName($val['item_type_id'])."</td>
    //                         <td style=\"text-align: center;\">".$val['quantity']."</td>
    //                         <td style=\"text-align: right;\">".$val['item_unit_price']."</td>
    //                         <td style=\"text-align: right;\">".$val['item_unit_price'] * $val['quantity']."</td>
    //                         <td style=\"text-align: right;\">".$val['discount_A'] + $val['discount_B']." </td>
    //                         <td style=\"text-align: right;\">".$total - $diskon."</td>
    //                         <td style=\"text-align: right;\">".$ppn."</td>
    //                         <td style=\"text-align: right;\">".$dpp."</td>
    //                     </tr> 
    //                     ";
    //         $no++;
    //     }

    //     $html2  .= "
    //                 <tr>
    //                     <td colspan=\"6\" style=\"text-align: left;font-weight: bold\";></td>
    //                     <td style=\"text-align: right;\">".$totaldpp."</td>
    //                     <td style=\"text-align: right;\">".$totalppn."</td>
    //                     <td style=\"text-align: right;\">".$totalbayar."</td>
    //                 </tr>
    //                 <tr>
    //                     <td colspan=\"6\" style=\"text-align: right;font-weight: bold\";>TOTAL PPN</td>
    //                     <td style=\"text-align: right;\"></td>
    //                     <td style=\"text-align: right;\"></td>
    //                     <td style=\"text-align: right;\">".$totalppn."</td>
    //                 </tr>
    //                 <tr>
    //                     <td colspan=\"6\" style=\"text-align: right;font-weight: bold\";>TOTAL BAYAR</td>
    //                     <td style=\"text-align: right;\"></td>
    //                     <td style=\"text-align: right;\"></td>
    //                     <td style=\"text-align: right;\">".$totalbayar."</td>
    //                 </tr>
    //                 ";
    //     $html2 .= "</table>";
    //     $path = '<img width="60"; height="60" src="resources/assets/img/ttd.png">';
    //     //dd($path);        
    //     $html2 .= "
    //                 <table style=\"text-align: center;font-weight: bold\" cellspacing=\"20\";>
    //                     <tr>
    //                         <th style=\"text-align: left; font-size:12px; font-weight: bold\">KASIR TERBILANG</th>
    //                         <th></th>
    //                         <th></th>
    //                     </tr>
    //                 </table>
    //                 <table style=\"text-align: left;\" cellspacing=\"0\";>
    //                     <tr>
    //                         <th>".$path."</th>
    //                         <th></th>
    //                         <th></th>
    //                     </tr>
    //                 </table>
    //                 <table style=\"text-align: center;font-weight: bold\" cellspacing=\"0\";>
    //                 <tr>
    //                     <th></th>
    //                     <th></th>
    //                     <th></th>
    //                 </tr>
    //             </table>
    //                 ";
    //     $pdf::writeHTML($html2, true, false, true, false, '');
    //     // $pdf::Image($path, 98, 98, 15, 15, 'PNG', '', 'LT', false, 300, '', false, false, 1, false, false, false);
    //     // ob_clean();
    //     $filename = 'SK_'.$saleskwitansi['sales_kwitansi_no'].'.pdf';
    //     $pdf::Output($filename, 'I');
    // }


    public function calculateDateDifference($startDate, $endDate)
    {
        // Konversi string tanggal ke objek Carbon
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Hitung jarak antara dua tanggal
        $differenceInDays = $end->diffInDays($start);
        $differenceInWeeks = $end->diffInWeeks($start);
        $differenceInMonths = $end->diffInMonths($start);
        $differenceInYears = $end->diffInYears($start);

        // Hasil
        return [
            'days' => $differenceInDays,
            'weeks' => $differenceInWeeks,
            'months' => $differenceInMonths,
            'years' => $differenceInYears,
        ];
    }

    




    //get
    public function getSupplierName($supplier_id){
        $supplier = CoreSupplier::select('supplier_name')
        ->where('data_state', 0)
        ->where('supplier_id', $supplier_id)
        ->first();

        return $supplier['supplier_name'];
    }
}
