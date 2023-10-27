<?php

namespace App\Http\Controllers;

use App\Models\BuyersAcknowledgment;
use App\Models\CoreCustomer;
use App\Models\InvItemStock;
use App\Models\InvItemType;
use App\Models\InvItemUnit;
use App\Models\PreferenceCompany;
use App\Models\SalesDeliveryNoteItemStock;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesKwitansi;
use App\Models\SalesKwitansiItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\Request;

class KwitansiController extends Controller
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
        if (!Session::get('start_date')) {
            $start_date     = date('Y-m-d');
        } else {
            $start_date = Session::get('start_date');
        }

        if (!Session::get('end_date')) {
            $end_date     = date('Y-m-d');
        } else {
            $end_date = Session::get('end_date');
        }

        $saleskwitansi = SalesKwitansi::where('data_state', '=', 0)
            ->where('sales_kwitansi_date', '>=', $start_date)
            ->where('sales_kwitansi_date', '<=', $end_date)
            ->get();

        //dd($buyersacknowledgment);

        return view('content/Kwitansi/ListKwitansi', compact('saleskwitansi', 'start_date', 'end_date'));
    }

    public function getBpbNo($sales_invoice_id)
    {
        $data = SalesInvoice::select('buyers_acknowledgment_no')
            ->where('data_state', 0)
            ->where('sales_invoice_id', $sales_invoice_id)
            ->first();

        return $data['buyers_acknowledgment_no'] ?? '';
    }


 


    public function filterKwitansi(Request $request)
    {
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/print-kwitansi');
    }

    public function searchCustomer()
    {

        $corecustomer = SalesInvoice::select('sales_invoice.customer_id', 'core_customer.customer_name', 'core_customer.customer_address', DB::raw("SUM(sales_invoice.owing_amount) as total_owing_amount"))
            ->join('core_customer', 'core_customer.customer_id', 'sales_invoice.customer_id')
            ->where('sales_invoice.data_state', 0)
            // ->where('sales_invoice.kwitansi_status', 0)
            ->where('core_customer.data_state', 0)
            ->groupBy('sales_invoice.customer_id')
            ->orderBy('core_customer.customer_name', 'ASC')
            ->get();
            

        return view('content/Kwitansi/SearchCoreCustomer', compact('corecustomer',));
    }


    public function addKwitansi($customer_id)
    {

        $salesinvoice = SalesInvoice::select('*')
            ->where('sales_invoice.customer_id', $customer_id)
            // ->where('sales_invoice.kwitansi_status', 0)
            ->get();

        return view('content/Kwitansi/FormAddKwitansi', compact('salesinvoice','customer_id'));
    }



    public function processAddKwitansi(Request $request)
    {
        // dd($request->all());

        $saleskwitansi = array(
            'customer_id'                   => $request->customer_id,
            // 'print_type'                    => $request->print_type,
            'sales_kwitansi_date'           =>  \Carbon\Carbon::now(), # new \Datetime()
            'created_id'                    => Auth::id(),
        );
        if (SalesKwitansi::create($saleskwitansi)) {
            $saleskwitansi_id = SalesKwitansi::select('*')
                ->orderBy('created_at', 'DESC')
                ->first();

            $dataitem = $request->all();
            $total_no = $request->total_no;
            for ($i = 1; $i <= $total_no; $i++) {

                $data = SalesKwitansiItem::create([
                    'sales_invoice_id'              => $dataitem['sales_invoice_id_' . $i],
                    'buyers_acknowledgment_id'      => $dataitem['buyers_acknowledgment_id_' . $i],
                    'sales_kwitansi_id'             => $saleskwitansi_id['sales_kwitansi_id'],
                    'checked'                       => $dataitem['checkbox_' . $i],
                    'created_id'                    => Auth::id(),
                ]);
            


           // $salesinvoice = SalesInvoice::findOrFail($dataitem['sales_invoice_id_' . $i]);
           // $salesinvoice->kwitansi_status = $dataitem['checkbox_' . $i];
           // $salesinvoice->save();
            }


            $empty = DB::table('sales_kwitansi')
            ->join('sales_kwitansi_item','sales_kwitansi_item.sales_kwitansi_id','sales_kwitansi.sales_kwitansi_id')
            ->where('sales_kwitansi_item.checked','=',0)
            ->where('sales_kwitansi.sales_kwitansi_id','=',$saleskwitansi_id['sales_kwitansi_id'])
            ->delete();
            SalesKwitansiItem::where("sales_kwitansi_id",$saleskwitansi_id['sales_kwitansi_id'])
            ->where('sales_kwitansi_item.checked','=',0)
            ->delete();
        }
        if($empty){
            $msg = 'Tambah Kwitansi Tidak Tersimpan';
            return redirect('/print-kwitansi')->with('msg',$msg);
        }else if($data){
            $msg = 'Tambah Kwitansi Penjualan Berhasil';
                return redirect('/print-kwitansi')->with('msg',$msg);
            }else{
                $msg = 'Tambah Kwitansi Penjualan Gagal';
                return redirect('/print-kwitansi')->with('msg',$msg);
            }
    }

    public function printKwitansi(Request $request){
        $saleskwitansi = SalesKwitansi::select('*')
        ->where('data_state', '=', 0)
        ->where('sales_kwitansi_id', '=', $request->sales_kwitansi_id)
        ->first();

        $saleskwitansiItem = SalesKwitansiItem::select('*')
        ->join('sales_invoice_item','sales_invoice_item.sales_invoice_id','sales_kwitansi_item.sales_invoice_id')
        ->join('sales_order_item','sales_order_item.sales_order_id','sales_invoice_item.sales_order_id')
        ->where('sales_kwitansi_item.sales_kwitansi_id', '=', $request->sales_kwitansi_id)
        ->where('checked', '=', 1)
        ->get();

    //dd($saleskwitansiItem);


        $company = PreferenceCompany::select('*')
            ->first();


        //pdf

        $pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);
        //$path = public_path('resources/assets/img/TTD.png');

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(10, 10, 10, 10); // put space of 10 on top

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 8);

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>

            <td>
                <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                    <tr>
                        <td><div style=\"text-align: left; font-size:12px; font-weight: bold\">PBF MENJANGAN ENAM</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">Jl.Puspowarno Raya No 55D RT 06 RW 09</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">APJ : " . Auth::user()->name . "</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">" . $company['CDBO_no'] . "</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">" . $company['distribution_no'] . "</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">SIPA: 449.2/16/DPM-PTSP/SIKA.16/11/2019</div></td>
                    </tr>
                </table>
            </td>

            <td>
                <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                    <tr>
                        <td style=\"text-align: right; font-size:14px; font-weight: bold\">
                        K W I T A N S I
                        </td>
                   
                    </tr>
                </table>
            </td>

            </tr>

        </table>
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
        <tr>
            <td>-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td>
        </tr>
        <tr>
            <td>
                Telah Terima Dari ".$this->getCustomerName($saleskwitansi['customer_id'])." 
            </td>
        </tr>
        <tr>
            <td>
                Guna Pembayaran Permintaan Barang dengan Rincian :
            </td>
        </tr>
        <tr>
        <td>-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td>
    </tr>
    </table>
        ";
        $pdf::writeHTML($tbl, true, false, false, false, '');

        $html2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                        <tr style=\"text-align: center;\">
                            <td width=\"4%\" ><div style=\"text-align: center;\"></div></td>
                            <td width=\"20%\" ><div style=\"text-align: center;\"></div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\">Qty</div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\">Harga </div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\">JML</div></td>
                            <td width=\"9%\" ><div style=\"text-align: center;\">Diskon </div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\">Jumlah(DPP) </div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\">PPN </div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\">JML BAYAR </div></td>
                        </tr>";
        $no = 1;
        $totalppn = 0;
        $totalbayar = 0;
        $totaldpp = 0;
        foreach ($saleskwitansiItem as $key => $val) {
            $totaldpp += $val['subtotal_price_B'];
            $totalppn += $val['ppn_amount_item'];
            $totalbayar += $val['subtotal_price_B'] + $val['ppn_amount_item'];
            $html2 .= "<tr>
                            <td>" . $no . "</td>
                            <td>".$this->getBpbNo($val['sales_invoice_id']) ."<br>".$this->getInvItemTypeName($val['item_type_id'])."</td>
                            <td style=\"text-align: center;\">".$val['quantity']."</td>
                            <td style=\"text-align: right;\">".$val['item_unit_price']."</td>
                            <td style=\"text-align: right;\">".$val['item_unit_price'] * $val['quantity']."</td>
                            <td style=\"text-align: right;\">".$val['discount_A'] + $val['discount_B']." </td>
                            <td style=\"text-align: right;\">".$val['subtotal_price_B']."</td>
                            <td style=\"text-align: right;\">".$val['ppn_amount_item']."</td>
                            <td style=\"text-align: right;\">".$val['subtotal_price_B'] + $val['ppn_amount_item']."</td>
                        </tr> 
                        ";
            $no++;
        }

        $html2  .= "
                    <tr>
                        <td colspan=\"6\" style=\"text-align: left;font-weight: bold\";></td>
                        <td style=\"text-align: right;\">".$totaldpp."</td>
                        <td style=\"text-align: right;\">".$totalppn."</td>
                        <td style=\"text-align: right;\">".$totalbayar."</td>
                    </tr>
                    <tr>
                        <td colspan=\"6\" style=\"text-align: right;font-weight: bold\";>TOTAL BAYAR</td>
                        <td style=\"text-align: right;\"></td>
                        <td style=\"text-align: right;\"></td>
                        <td style=\"text-align: right;\">".$totalbayar."</td>
                    </tr>
                    ";
        $html2 .= "</table>";
        $path = '<img width="60"; height="60" src="resources/assets/img/ttd.png">';
        //dd($path);        
        $html2 .= "
                    <table style=\"text-align: center;font-weight: bold\" cellspacing=\"20\";>
                        <tr>
                            <th style=\"text-align: left; font-size:12px; font-weight: bold\">KASIR TERBILANG</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </table>
                    <table style=\"text-align: left;\" cellspacing=\"0\";>
                        <tr>
                            <th>".$path."</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </table>
                    <table style=\"text-align: center;font-weight: bold\" cellspacing=\"0\";>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </table>
                    ";
        $pdf::writeHTML($html2, true, false, true, false, '');
       // $pdf::Image($path, 98, 98, 15, 15, 'PNG', '', 'LT', false, 300, '', false, false, 1, false, false, false);



        // ob_clean();

        $filename = 'SK_'.$saleskwitansi['sales_kwitansi_no'].'.pdf';
        $pdf::Output($filename, 'I');

        
    }

    public function printKwitansiSingle(Request $request) {
        $saleskwitansi = SalesKwitansi::select('*')
        ->where('data_state', '=', 0)
        ->where('sales_kwitansi_id', '=', $request->sales_kwitansi_id)
        ->first();

        $saleskwitansiItem = SalesKwitansiItem::select('*')
        ->join('sales_invoice_item','sales_invoice_item.sales_invoice_id','sales_kwitansi_item.sales_invoice_id')
        ->join('sales_order_item','sales_order_item.sales_order_id','sales_invoice_item.sales_order_id')
        ->where('sales_kwitansi_item.sales_kwitansi_id', '=', $request->sales_kwitansi_id)
        ->where('checked', '=', 1)
        ->get();

       //dd($saleskwitansiItem[0]);


        $company = PreferenceCompany::select('*')
            ->first();


        //pdf

        $pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);
        //$path = public_path('resources/assets/img/TTD.png');

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(10, 10, 10, 10); // put space of 10 on top

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);
        
        $total_no = count($saleskwitansiItem);
        //dd($total_no);
        $row = $total_no - 1;
        for ($i = 0; $i <= $row; $i++) {
        
        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 8);

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>

            <td>
                <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                    <tr>
                        <td><div style=\"text-align: left; font-size:12px; font-weight: bold\">PBF MENJANGAN ENAM</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">Jl.Puspowarno Raya No 55D RT 06 RW 09</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">APJ : " . Auth::user()->name . "</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">" . $company['CDBO_no'] . "</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">" . $company['distribution_no'] . "</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">SIPA: 449.2/16/DPM-PTSP/SIKA.16/11/2019</div></td>
                    </tr>
                </table>
            </td>

            <td>
                <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                    <tr>
                        <td style=\"text-align: right; font-size:14px; font-weight: bold\">
                        K W I T A N S I
                        </td>
                   
                    </tr>
                </table>
            </td>

            </tr>

        </table>
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
        <tr>
            <td>-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td>
        </tr>
        <tr>
            <td>
                Telah Terima Dari ".$this->getCustomerName($saleskwitansi['customer_id'])." 
            </td>
        </tr>
        <tr>
            <td>
                Guna Pembayaran Permintaan Barang dengan Rincian :
            </td>
        </tr>
        <tr>
        <td>-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td>
    </tr>
    </table>
        ";
        $pdf::writeHTML($tbl, true, false, false, false, '');

        $html2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                        <tr style=\"text-align: center;\">
                            <td width=\"4%\" ><div style=\"text-align: center;\"></div></td>
                            <td width=\"50%\" ><div style=\"text-align: center;\"></div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\"> </div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\"></div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\"> </div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\"></div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\"> </div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\"></div></td>
                        </tr>";
        $no = 1;
        // // $total = 0;
        // $totalJumlah = 0;
        // foreach ($saleskwitansiItem as $Key => $val) {
        //     // $Jumlah = $val['quantity'] * $val['item_unit_cost'] - $val['discount_amount'] ;
        //     // $totalJumlah += $Jumlah;
        $html2 .= "<tr>
                <td>" . $no . "</td>
                <td>". $this->getInvItemTypeName($saleskwitansiItem[$i]['item_type_id'])."</td>
                <td style=\"text-align: center;\">".$this->getItemBatchNumber($this->getNoteStokID($saleskwitansiItem[$i]['sales_delivery_note_item_id']))."</td>
                <td style=\"text-align: center;\">".$saleskwitansiItem[$i]['quantity']."".$this->getItemUnitName($saleskwitansiItem[$i]['item_unit_id']) ."</td>
                <td style=\"text-align: center;\">". "@Rp".$saleskwitansiItem[$i]['item_unit_price']."</td>
                <td style=\"text-align: center;\">".$saleskwitansiItem[$i]['subtotal_price_B']."</td>
            </tr> 
            ";
            $no++;
        //

        $html2  .= " 
                    <tr>
                        <td colspan=\"5\" style=\"text-align: right;font-weight: bold\";>TOTAL</td>
                        <td style=\"text-align: center;\">".$saleskwitansiItem[$i]['subtotal_price_B']."</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan=\"5\" style=\"text-align: right;font-weight: bold\";>POTONGAN</td>
                        <td style=\"text-align: center;\">".$saleskwitansiItem[$i]['discount_A'] + $saleskwitansiItem[$i]['discount_B']."</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan=\"5\" style=\"text-align: right;font-weight: bold\";>NETTO</td>
                        <td style=\"text-align: center;\">".$saleskwitansiItem[$i]['subtotal_price_B'] - $saleskwitansiItem[$i]['discount_A'] + $saleskwitansiItem[$i]['discount_B']."</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan=\"5\" style=\"text-align: right;font-weight: bold\";>PPN</td>
                        <td style=\"text-align: center;\">".$saleskwitansiItem[$i]['ppn_amount_item']."</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan=\"5\" style=\"text-align: right;font-weight: bold\";>TOTAL BAYAR</td>
                        <td style=\"text-align: center;\">".$saleskwitansiItem[$i]['subtotal_price_B'] - ($saleskwitansiItem[$i]['discount_A'] + $saleskwitansiItem[$i]['discount_B'])+$saleskwitansiItem[$i]['ppn_amount_item']."</td>
                        <td></td>
                    </tr>
                    ";
        $html2 .= "</table>";
        $path = '<img width="60"; height="60" src="resources/assets/img/ttd.png">';
        //dd($path);        
        $html2 .= "
                    <table style=\"text-align: center;font-weight: bold\" cellspacing=\"20\";>
                        <tr>
                            <th style=\"text-align: left; font-size:12px; font-weight: bold\">KASIR &nbsp; &nbsp; &nbsp; TERBILANG</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </table>
                    <table style=\"text-align: left;\" cellspacing=\"0\";>
                        <tr>
                            <th>".$path."</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </table>
                    <table style=\"text-align: center;font-weight: bold\" cellspacing=\"0\";>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </table>
                    ";
        $pdf::writeHTML($html2, true, false, true, false, '');
       // $pdf::Image($path, 98, 98, 15, 15, 'PNG', '', 'LT', false, 300, '', false, false, 1, false, false, false);
        // $row++;
    }



        // ob_clean();

        $filename = 'SK_'.$saleskwitansi['sales_kwitansi_no'].'.pdf';
        $pdf::Output($filename, 'I');
    }



    public function getCustomerName($customer_id)
    {
        $unit = CoreCustomer::select('customer_name')
            ->where('customer_id', $customer_id)
            ->where('data_state', 0)
            ->first();

        return $unit['customer_name'] ?? '';
    }
    
    public function getInvItemTypeName($item_type_id){
        $item = InvItemType::select('item_type_name')
        ->where('item_type_id', $item_type_id)
        ->where('data_state', 0)
        ->first();

        return $item['item_type_name'];
    }


    public function getNoteStokID($sales_delivery_note_item_id)
    {
        $unit = SalesDeliveryNoteItemStock::where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
            ->where('data_state', 0)
            ->first();

        return $unit['item_stock_id'] ?? '';
    }

       
    public function getItemBatchNumber($item_stock_id){
        $item = InvItemStock::select('item_batch_number')
        ->where('item_stock_id', $item_stock_id)
        ->where('data_state', 0)
        ->first();

        return $item['item_batch_number']?? '';
    }
    public function getItemUnitName($item_unit_id){
        $item = InvItemUnit::where('item_unit_id', $item_unit_id)
        ->where('data_state', 0)
        ->first();

        return $item['item_unit_name'];
    }
}
