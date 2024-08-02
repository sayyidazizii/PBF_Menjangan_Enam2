<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\InvItemType;
use App\Models\InvItemUnit;
use App\Models\CoreCustomer;
use App\Models\InvItemStock;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use App\Models\SalesKwitansi;
use App\Models\SalesOrderItem;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Models\SalesInvoiceItem;
use App\Models\PreferenceCompany;
use App\Models\SalesKwitansiItem;
use Illuminate\Support\Facades\DB;
use App\Models\BuyersAcknowledgment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SalesDeliveryNoteItem;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\SalesDeliveryNoteItemStock;

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

    public function filterKwitansiAdd(Request $request)
    {
        $customer_id    = $request->customer_id;
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/print-kwitansi/add/'.$customer_id);
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
        
        $salesinvoice = SalesInvoice::select('*')
            ->where('sales_invoice.customer_id', $customer_id)
            ->where('sales_invoice.sales_invoice_date', '>=', $start_date)
            ->where('sales_invoice.sales_invoice_date', '<=', $end_date)
            ->get();

        return view('content/Kwitansi/FormAddKwitansi', compact('salesinvoice','customer_id', 'start_date', 'end_date'));
    }

    public function processAddKwitansi(Request $request)
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

        $saleskwitansi = array(
            'customer_id'                   => $request->customer_id,
            // 'print_type'                 => $request->print_type,
            'start_date'                    => $start_date,
            'end_date'                      => $end_date,
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
            
            }
        }
         if($data){
            $msg = 'Tambah Kwitansi Penjualan Berhasil';
                return redirect('/print-kwitansi')->with('msg',$msg);
            }else{
                $msg = 'Tambah Kwitansi Penjualan Gagal';
                return redirect('/print-kwitansi')->with('msg',$msg);
            }
    }

    public function  processDeleteKwitansi($sales_kwitansi_id)
    {
        $saleskwitansi = SalesKwitansi::findOrFail($sales_kwitansi_id);
        $saleskwitansi->data_state = 1;

        if ($saleskwitansi->save()) {
            $msg = 'Hapus Kwitansi Berhasil';
            return redirect('/print-kwitansi')->with('msg', $msg);
        } else {
            $msg = 'Hapus Kwitansi Gagal';
            return redirect('/print-kwitansi')->with('msg', $msg);
        }
    }


    function doone2($onestr) {
	    $tsingle = array("","satu ","dua ","tiga ","empat ","lima ",
		"enam ","tujuh ","delapan ","sembilan ");
	      return strtoupper($tsingle[$onestr]);
	}	
	 
	function doone($onestr) {
	    $tsingle = array("","se","dua ","tiga ","empat ","lima ", "enam ","tujuh ","delapan ","sembilan ");
	      return strtoupper($tsingle[$onestr]);
	}	

	function dotwo($twostr) {
	    $tdouble = array("","puluh ","dua puluh ","tiga puluh ","empat puluh ","lima puluh ", "enam puluh ","tujuh puluh ","delapan puluh ","sembilan puluh ");
	    $teen = array("sepuluh ","sebelas ","dua belas ","tiga belas ","empat belas ","lima belas ", "enam belas ","tujuh belas ","delapan belas ","sembilan belas ");
	    if ( substr($twostr,1,1) == '0') {
			$ret = $this->doone2(substr($twostr,0,1));
	    } else if (substr($twostr,1,1) == '1') {
			$ret = $teen[substr($twostr,0,1)];
	    } else {
			$ret = $tdouble[substr($twostr,1,1)] . $this->doone2(substr($twostr,0,1));
	    }
	    return strtoupper($ret);
	}
    

	function numtotxt($num) {
		$tdiv 	= array("","","ratus ","ribu ", "ratus ", "juta ", "ratus ","miliar ");
		$divs 	= array( 0,0,0,0,0,0,0);
		$pos 	= 0; // index into tdiv;
		// make num a string, and reverse it, because we run through it backwards
		// bikin num ke string dan dibalik, karena kita baca dari arah balik
		$num 	= strval(strrev(number_format($num, 2, '.',''))); 
		$answer = ""; // mulai dari sini
		while (strlen($num)) {
			if ( strlen($num) == 1 || ($pos >2 && $pos % 2 == 1))  {
				$answer = $this->doone(substr($num, 0, 1)) . $answer;
				$num 	= substr($num,1);
			} else {
				$answer = $this->dotwo(substr($num, 0, 2)) . $answer;
				$num 	= substr($num,2);
				if ($pos < 2)
					$pos++;
			}

			if (substr($num, 0, 1) == '.') {
				if (! strlen($answer)){
					$answer = "";
				}

				$answer = "" . $answer . "";
				$num 	= substr($num,1);
				// kasih tanda "nol" jika tidak ada
				if (strlen($num) == 1 && $num == '0') {
					$answer = "" . $answer;
					$num 	= substr($num,1);
				}
			}
		    // add separator
		    if ($pos >= 2 && strlen($num)) {
				if (substr($num, 0, 1) != 0  || (strlen($num) >1 && substr($num,1,1) != 0
					&& $pos %2 == 1)  ) {
					// check for missed millions and thousands when doing hundreds
					// cek kalau ada yg lepas pada juta, ribu dan ratus
					if ( $pos == 4 || $pos == 6 ) {
						if ($divs[$pos -1] == 0)
							$answer = $tdiv[$pos -1 ] . $answer;
					}
					// standard
					$divs[$pos] = 1;
					$answer 	= $tdiv[$pos++] . $answer;
				} else {
					$pos++;
				}
			}
	    }
	    return strtoupper($answer.'rupiah');
	}

    public function printKwitansi($sales_kwitansi_id){
        $saleskwitansi = SalesKwitansi::select('*')
        ->where('data_state', '=', 0)
        ->where('sales_kwitansi_id', '=', $sales_kwitansi_id)
        ->first();

        $saleskwitansiItem = SalesKwitansiItem::select('*')
        ->join('sales_invoice_item','sales_invoice_item.sales_invoice_id','sales_kwitansi_item.sales_invoice_id')
        ->where('sales_kwitansi_item.sales_kwitansi_id', '=', $sales_kwitansi_id)
        ->where('checked', '=', 1)
        ->groupBy('sales_invoice_item.sales_invoice_item_id')
        ->get();


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
                        <td><div style=\"text-align: left; font-size:10px\">Jl.Puspowarno Raya No 55D Bojong Salaman, Semarang Barat</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">APA : ISTI RAHMADANI,S.Farm, Apt</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">" . $company['CDBO_no'] . "</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">" . $company['distribution_no'] . "</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">SIKA: 449.2/16/DPM-PTSP/SIKA.16/11/2019</div></td>
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
            $total = $val['item_unit_price'] * $val['quantity'];
            $diskon = $val['discount_A'] + $val['discount_B'];
            $dpp = $total - $diskon ; 
            $totaldpp += $total - $diskon ;
            $ppn = $this->getPpnItem($val['sales_delivery_note_item_id']);
            $totalppn += $this->getPpnItem($val['sales_delivery_note_item_id']);
            $totalbayar += $total - $diskon  + $ppn;
            $html2 .= "<tr>
                            <td>" . $no . "</td>
                            <td>".$this->getBpbNo($val['sales_invoice_id']) ."<br>".$this->getInvItemTypeName($val['item_type_id'])."</td>
                            <td style=\"text-align: center;\">".$val['quantity']."</td>
                            <td style=\"text-align: right;\">".number_format($val['item_unit_price'],2)."</td>
                            <td style=\"text-align: right;\">".number_format($val['item_unit_price'] * $val['quantity'],2)."</td>
                            <td style=\"text-align: right;\">".number_format($val['discount_A'] + $val['discount_B'],2)." </td>
                            <td style=\"text-align: right;\">".number_format($total - $diskon,2)."</td>
                            <td style=\"text-align: right;\">".number_format($ppn,2)."</td>
                            <td style=\"text-align: right;\">".number_format($dpp,2)."</td>
                        </tr> 
                        ";
            $no++;
        }

        $html2  .= "
                    <tr>
                        <td colspan=\"6\" style=\"text-align: left;font-weight: bold\";></td>
                        <td style=\"text-align: right;\">".number_format($totaldpp,2)."</td>
                        <td style=\"text-align: right;\">".number_format($totalppn,2)."</td>
                        <td style=\"text-align: right;\">".number_format($totalbayar,2)."</td>
                    </tr>
                    <tr>
                        <td colspan=\"6\" style=\"text-align: right;font-weight: bold\";>TOTAL PPN</td>
                        <td style=\"text-align: right;\"></td>
                        <td style=\"text-align: right;\"></td>
                        <td style=\"text-align: right;\">".number_format($totalppn,2)."</td>
                    </tr>
                    <tr>
                        <td colspan=\"6\" style=\"text-align: right;font-weight: bold\";>TOTAL BAYAR</td>
                        <td style=\"text-align: right;\"></td>
                        <td style=\"text-align: right;\"></td>
                        <td style=\"text-align: right;\">".number_format($totalbayar,2)."</td>
                    </tr>
                    ";
        $html2 .= "</table>";
        $path = '<img width="60"; height="60" src="resources/assets/img/ttd.png">';
        //dd($path);        
        $html2 .= "
                    <table style=\"text-align: center;font-weight: bold\" cellspacing=\"20\";>
                        <tr>
                            <th style=\"text-align: left; font-size:12px; font-weight: bold\">KASIR TERBILANG </th>
                        </tr>
                        <tr style=\"text-align: left; font-size:12px; font-weight: bold\">
                            <th>".$this->numtotxt($totalbayar)."</th>
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

    public function printKwitansiSingle($sales_kwitansi_id) {
        $saleskwitansi = SalesKwitansi::select('*')
        ->where('data_state', '=', 0)
        ->where('sales_kwitansi_id', '=', $sales_kwitansi_id)
        ->first();

        $saleskwitansiItem = SalesKwitansiItem::select('*')
        ->join('sales_invoice_item','sales_invoice_item.sales_invoice_id','sales_kwitansi_item.sales_invoice_id')
        ->where('sales_kwitansi_item.sales_kwitansi_id', '=', $sales_kwitansi_id)
        ->where('checked', '=', 1)
        ->groupBy('sales_invoice_item.sales_invoice_item_id')
        ->get();


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
                        <td><div style=\"text-align: left; font-size:10px\">Jl.Puspowarno Raya No 55D Bojong Salaman, Semarang Barat</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">APA : ISTI RAHMADANI,S.Farm, Apt</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">" . $company['CDBO_no'] . "</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">" . $company['distribution_no'] . "</div></td>
                    </tr>
                    <tr>
                        <td><div style=\"text-align: left; font-size:10px\">SIKA: 449.2/16/DPM-PTSP/SIKA.16/11/2019</div></td>
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
                <td style=\"text-align: center;\">". "@Rp".number_format($saleskwitansiItem[$i]['item_unit_price'],2)."</td>
                <td style=\"text-align: right;\">".number_format($saleskwitansiItem[$i]['subtotal_price_B'],2)."</td>
            </tr> 
            ";
            $no++;
        //

        $html2  .= " 
                    <tr>
                        <td colspan=\"5\" style=\"text-align: right;font-weight: bold\";>TOTAL</td>
                        <td style=\"text-align: right;\">".number_format($saleskwitansiItem[$i]['subtotal_price_B'],2)."</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan=\"5\" style=\"text-align: right;font-weight: bold\";>POTONGAN</td>
                        <td style=\"text-align: right;\">".number_format($saleskwitansiItem[$i]['discount_A'] + $saleskwitansiItem[$i]['discount_B'],2)."</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan=\"5\" style=\"text-align: right;font-weight: bold\";>NETTO</td>
                        <td style=\"text-align: right;\">".number_format($saleskwitansiItem[$i]['subtotal_price_B'] - $saleskwitansiItem[$i]['discount_A'] + $saleskwitansiItem[$i]['discount_B'],2)."</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan=\"5\" style=\"text-align: right;font-weight: bold\";>PPN</td>
                        <td style=\"text-align: right;\">".number_format($this->getPpnItem($saleskwitansiItem[$i]['sales_delivery_note_item_id']),2)."</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan=\"5\" style=\"text-align: right;font-weight: bold\";>TOTAL BAYAR</td>
                        <td style=\"text-align: right;\">".number_format($saleskwitansiItem[$i]['subtotal_price_B'] - ($saleskwitansiItem[$i]['discount_A'] + $saleskwitansiItem[$i]['discount_B'])+$saleskwitansiItem[$i]['ppn_amount_item'],2)."</td>
                        <td></td>
                    </tr>
                    ";
        $html2 .= "</table>";
        $path = '<img width="60"; height="60" src="resources/assets/img/ttd.png">';
        //dd($path);        
        $html2 .= "
                    <table style=\"text-align: left;font-weight: bold\" cellspacing=\"20\";>
                        <tr>
                            <th style=\"text-align: left; font-size:12px; font-weight: bold\">KASIR TERBILANG  </th>
                        </tr>
                        <tr>
                            <th tyle=\"text-align: left; font-size:12px; font-weight: bold\">".$this->numtotxt($saleskwitansiItem[$i]['subtotal_price_B'] - ($saleskwitansiItem[$i]['discount_A'] + $saleskwitansiItem[$i]['discount_B'])+$saleskwitansiItem[$i]['ppn_amount_item'])."</th>
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


    // Metode 2: Mapping manual bulan
    public function bulanIndo($month) {
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        return $bulan[$month];
    }

    //Pengantar
    public function printKwitansiPengantar($sales_kwitansi_id){
        $saleskwitansi = SalesKwitansi::select('*')
        ->where('data_state', '=', 0)
        ->where('sales_kwitansi_id', '=', $sales_kwitansi_id)
        ->first();

        $saleskwitansiItem = SalesKwitansiItem::select('*')
        ->join('sales_invoice_item','sales_invoice_item.sales_invoice_id','sales_kwitansi_item.sales_invoice_id')
        ->where('sales_kwitansi_item.sales_kwitansi_id', '=', $sales_kwitansi_id)
        ->where('checked', '=', 1)
        ->groupBy('sales_invoice_item.sales_invoice_item_id')
        ->get();


        $company = PreferenceCompany::select('*')
            ->first();


        //pdf
        $pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(10, 10, 10, 10); // put space of 10 on top

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $no = 1;
        $totalppn = 0;
        $totalbayar = 0;
        $totaldpp = 0;
        $totalDiskon = 0;

        foreach ($saleskwitansiItem as $key => $val) {
            $total = $val['item_unit_price'] * $val['quantity'];
            $diskon = $val['discount_A'] + $val['discount_B'];
            $dpp = $total - $diskon ; 
            $totaldpp += $total - $diskon ;
            $ppn = $this->getPpnItem($val['sales_delivery_note_item_id']);
            $totalppn += $this->getPpnItem($val['sales_delivery_note_item_id']);
            $totalbayar += $total - $diskon  + $ppn;
            $totalDiskon += $val['discount_A'] + $val['discount_B'];
            
           
            $no++;
        }
        $materai = 0;
        if($totalDiskon > 5000000){
            $materai = 10000;
        }else{
            $materai = 0;
        }

        // Asumsi $saleskwitansi['start_date'] dan $saleskwitansi['end_date'] berformat 'YYYY-MM-DD'
        $startDate = new DateTime($saleskwitansi['start_date']);
        $endDate = new DateTime($saleskwitansi['end_date']);

        $salesKwitansiDate = $saleskwitansi['sales_kwitansi_date'];
        $formattedDate = date('d-m-Y', strtotime($salesKwitansiDate));
        // Pilih salah satu metode di atas
        // Metode 1: Menggunakan setlocale dan strftime
        // setlocale(LC_TIME, 'id_ID.UTF-8');
        // $startFormatted = strftime('%d %B %Y', $startDate->getTimestamp());
        // $endFormatted = strftime('%d %B %Y', $endDate->getTimestamp());


        $startFormatted = $startDate->format('d') . ' ' . $this->bulanIndo($startDate->format('n')) . ' ' . $startDate->format('Y');
        $endFormatted = $endDate->format('d') . ' ' . $this->bulanIndo($endDate->format('n')) . ' ' . $endDate->format('Y');


        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 8);
        if($materai == 10000){
            $tbl = "
            <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                <tr>
    
                <td>
                    <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                        <tr>
                            <td><div style=\"text-align: left; font-size:12px; font-weight: bold\">PBF MENJANGAN ENAM</div></td>
                        </tr>
                        <tr>
                            <td><div style=\"text-align: left; font-size:10px\">Jl.Puspowarno Raya No 55D Bojong Salaman, Semarang Barat</div></td>
                        </tr>
                        <tr>
                            <td><div style=\"text-align: left; font-size:10px\">APA : ISTI RAHMADANI,S.Farm, Apt</div></td>
                        </tr>
                        <tr>
                            <td><div style=\"text-align: left; font-size:10px\">" . $company['CDBO_no'] . "</div></td>
                        </tr>
                        <tr>
                            <td><div style=\"text-align: left; font-size:10px\">" . $company['distribution_no'] . "</div></td>
                        </tr>
                        <tr>
                            <td><div style=\"text-align: left; font-size:10px\">SIKA: 449.2/16/DPM-PTSP/SIKA.16/11/2019</div></td>
                        </tr>
                    </table>
                </td>
    
                <td>
                    <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                       
                    </table>
                </td>
    
                </tr>
                <tr>
    
                <td>
                    <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                      
                    </table>
                </td>
    
                <td>
                    <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                        <tr>
                            <td style=\"text-align: right; font-size:20px; font-weight: bold\">
                            KWITANSI
                            </td>
                        </tr>
                        <tr>
                        <td style=\"text-align: right; font-size:10px;\">
                        ".
                        $saleskwitansi['sales_kwitansi_no']."
                            </td>
                        </tr>
                    </table>
                </td>
    
                </tr>
    
            </table>
            <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td>
                    Telah Terima Dari 
                </td>
                <td colspan=\"4\" style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">PT. PHAPROS TBK</td>
            </tr>
            <tr>
                <td>
                    Uang Sebanyak
                </td>
                <td  colspan=\"4\" style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">".$this->numtotxt($totalDiskon)."</td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
            </tr>
            <tr>
                <td>
                    Guna Membayar
                </td>
                <td  colspan=\"4\" style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">Biaya Promosi Penjualan Obat Tanggal  ".$startFormatted. "  s.d.  ".$endFormatted."  </td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
            </tr>
            <tr>
                <td></td>
                <td  colspan=\"4\" style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">".$this->getCustomerName($saleskwitansi['customer_id'])
                ." dan Materai ".$materai." </td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
            </tr>
            </table>
            <table style=\"text-align: left;\" cellspacing=\"0\";>
                            <tr>
                                <th style=\"text-align: left; font-size:12px;\"></th>
                                <th style=\"text-align: left; font-size:12px; font-weight: bold\"></th>
                                <th style=\"text-align: center; font-size:12px;\">Semarang , ".$formattedDate." &nbsp;&nbsp;</th>
                            </tr>
                            <tr>
                                <th style=\"text-align: left; font-size:12px;\"></th>
                                <th style=\"text-align: left; font-size:12px;\"></th>
                                <th style=\"text-align: center; font-size:12px;\">Hormat Kami</th>
                            </tr>
            </table>
            ";
        }else{
            $tbl = "
            <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                <tr>

                <td>
                    <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                        <tr>
                            <td><div style=\"text-align: left; font-size:12px; font-weight: bold\">PBF MENJANGAN ENAM</div></td>
                        </tr>
                        <tr>
                            <td><div style=\"text-align: left; font-size:10px\">Jl.Puspowarno Raya No 55D Bojong Salaman, Semarang Barat</div></td>
                        </tr>
                        <tr>
                            <td><div style=\"text-align: left; font-size:10px\">APA : ISTI RAHMADANI,S.Farm, Apt</div></td>
                        </tr>
                        <tr>
                            <td><div style=\"text-align: left; font-size:10px\">" . $company['CDBO_no'] . "</div></td>
                        </tr>
                        <tr>
                            <td><div style=\"text-align: left; font-size:10px\">" . $company['distribution_no'] . "</div></td>
                        </tr>
                        <tr>
                            <td><div style=\"text-align: left; font-size:10px\">SIKA: 449.2/16/DPM-PTSP/SIKA.16/11/2019</div></td>
                        </tr>
                    </table>
                </td>

                <td>
                    <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                    
                    </table>
                </td>

                </tr>
                <tr>

                <td>
                    <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                    
                    </table>
                </td>

                <td>
                    <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                        <tr>
                            <td style=\"text-align: right; font-size:20px; font-weight: bold\">
                            KWITANSI
                            </td>
                        </tr>
                        <tr>
                        <td style=\"text-align: right; font-size:10px;\">
                        ".
                        $saleskwitansi['sales_kwitansi_no']."
                            </td>
                        </tr>
                    </table>
                </td>

                </tr>

            </table>
            <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td>
                    Telah Terima Dari 
                </td>
                <td colspan=\"4\" style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">PT. PHAPROS TBK</td>
            </tr>
            <tr>
                <td>
                    Uang Sebanyak
                </td>
                <td  colspan=\"4\" style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">".$this->numtotxt($totalDiskon)."</td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
            </tr>
            <tr>
                <td>
                    Guna Membayar
                </td>
                <td  colspan=\"4\" style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">Biaya Promosi Penjualan Obat Tanggal    ".
                $saleskwitansi['start_date']." S/D ".
                $saleskwitansi['end_date']." </td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
            </tr>
            <tr>
                <td></td>
                <td  colspan=\"4\" style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">".$this->getCustomerName($saleskwitansi['customer_id'])
                ." </td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
                <td style=\"text-align: left; font-size:10px;\"></td>
            </tr>
            </table>
            <table style=\"text-align: left;\" cellspacing=\"0\";>
                            <tr>
                                <th style=\"text-align: left; font-size:12px;\"></th>
                                <th style=\"text-align: left; font-size:12px; font-weight: bold\"></th>
                                <th style=\"text-align: center; font-size:12px;\">Semarang , ".$saleskwitansi['sales_kwitansi_date']." &nbsp;&nbsp;</th>
                            </tr>
                            <tr>
                                <th style=\"text-align: left; font-size:12px;\"></th>
                                <th style=\"text-align: left; font-size:12px;\"></th>
                                <th style=\"text-align: center; font-size:12px;\">Hormat Kami</th>
                            </tr>
            </table>
            ";
        }
        $pdf::writeHTML($tbl, true, false, false, false, '');

       
        $path = '<img width="60"; height="60" src="resources/assets/img/ttd.png">';
        $html2 = "
                    <table style=\"text-align: left;\" cellspacing=\"20\";>
                        <tr>
                            <th style=\"text-align: left; font-size:12px;border-top-width:0.5px;border-bottom-width:0.5px;\">Rp.#". number_format($totalDiskon)."#</th>
                            <th style=\"text-align: left; font-size:12px; \"></th>
                            <th style=\"text-align: left; font-size:12px; font-weight: bold\"></th>
                        </tr>
                    </table>
                    <table style=\"text-align: left;\" cellspacing=\"0\";>
                        <tr>
                            <th style=\"text-align: left; font-size:12px;\"></th>
                            <th style=\"text-align: left; font-size:12px; font-weight: bold\"></th>
                            <th style=\"text-align: center; font-size:12px;\"></th>
                        </tr>
                        <tr>
                            <th style=\"text-align: left; font-size:12px;\"></th>
                            <th style=\"text-align: left; font-size:12px;\"></th>
                            <th style=\"text-align: center; font-size:12px;\"></th>
                        </tr>
                        <tr>
                            <th style=\"text-align: left; font-size:12px;\"></th>
                            <th style=\"text-align: left; font-size:12px;\"></th>
                            <th style=\"text-align: center; font-size:12px;\"></th>
                        </tr>
                        <tr>
                            <th style=\"text-align: left; font-size:12px;\">Catatan</th>
                            <th style=\"text-align: left; font-size:12px;\"></th>
                            <th style=\"text-align: center; font-size:12px;border-bottom-width:0.5px;\">Isti Rahmadani, SFarm,Apt</th>
                        </tr><tr>
                            <th  colspan=\"2\"  style=\"text-align: left; font-size:8px;\">Jatuh Tempo Pembayaran 7 (tujuh) hari kerja terhitung dari tanggal kwitansi</th>
                            <th style=\"text-align: center; font-size:12px;\">Apoteker</th>
                        </tr>
                    </table>
                    ";


        $pdf::writeHTML($html2, true, false, true, false, '');
        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 8); 
        
        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>

            <td>
                <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                   
                </table>
            </td>

            <td>
                <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                <tr>
                <td><div style=\"text-align: right; font-size:12px; font-weight: bold\">PBF MENJANGAN ENAM</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: right; font-size:10px\">Jl.Puspowarno Raya No 55D Bojong Salaman, Semarang Barat</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: right; font-size:10px\">APA : ISTI RAHMADANI,S.Farm, Apt</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: right; font-size:10px\">" . $company['CDBO_no'] . "</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: right; font-size:10px\">" . $company['distribution_no'] . "</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: right; font-size:10px\">SIKA: 449.2/16/DPM-PTSP/SIKA.16/11/2019</div></td>
            </tr>
                </table>
            </td>

            </tr>
        </table>
        <table>
        <tr>
                <td>
                    <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                    <tr>
                        <td style=\"text-align:left;width:10%\"><div style=\"text-align: left; font-size:11px\">Hal</div> </td>
                        <td style=\"text-align:left;width:2%\"> : </td>
                        <td style=\"text-align:left;width:50    %\"><div style=\"text-align: left; font-size:11px\">Tagihan Biaya Promosi Penjualan Obat</div></td>
                        <td style=\"text-align:left;width:20%\"></td>
                    </tr>
                    <tr>
                        <td style=\"text-align:left;width:10%\"><div style=\"text-align: left; font-size:11px\">No. </div></td>
                        <td style=\"text-align:left;width:2%\"> : </td>
                        <td style=\"text-align:left;width:45%\"><div style=\"text-align: left; font-size:11px\">".$saleskwitansi['sales_tagihan_no']."</div></td>
                        <td style=\"text-align:left;width:5%\"></td>
                        <td style=\"text-align:left;width:12%\"></td>
                        <td style=\"text-align:left;width:2%\"> </td>
                        <td style=\"text-align:left;width:20%\"><div style=\"font-size:13.5px\"></div></td>
                    </tr>
                    </table>
                </td>
            </tr>
            <br/>
            <tr>
                <td  style=\"text-align:left;width:50%;margin-top:15%\">
                <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: left; font-size:10px; \">Kepada Yth.</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px\">PT. PHAPROS TBK</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px;\">JL.SIMONGAN 131 SEMARANG</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px;\"></div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px;width:40%\">UP. Bp. Rahmat Prayoga</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px\">MANAJER KEUANGAN</div></td>
                </tr>
              
            </table>
                </td>
              
            </tr>
            <br/>
            <tr>
                <td  style=\"text-align:left;width:60%;margin-top:15%\">
                <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: left; font-size:10px; \">Dengan Hormat</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px\">Bersama ini kami sampaikan tagihan atas Biaya Promosi Penjualan obat kepada:</div></td>
                </tr>
            </table>
                </td>
            </tr>
        </table>
        <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
        <tr>
            <td>
                Client
            </td>
            <td colspan=\"4\" style=\"text-align: left; font-size:10px;\">:  ". $this->getCustomerName($saleskwitansi['customer_id']) ."</td>
        </tr>
        <tr>
            <td>
                Periode
            </td>
            <td  colspan=\"4\" style=\"text-align: left; font-size:10px;\">: ".$startFormatted. "  s.d.  ".$endFormatted."</td>
            <td style=\"text-align: left; font-size:10px;\"></td>
            <td style=\"text-align: left; font-size:10px;\"></td>
            <td style=\"text-align: left; font-size:10px;\"></td>
        </tr>
        <tr>
            <td>
                Total
            </td>
            <td  style=\"text-align: left; font-size:10px;\">: Rp.". number_format($totalDiskon)."</td>
            <td style=\"text-align: left; font-size:10px;\"></td>
            <td style=\"text-align: left; font-size:10px;\"></td>
            <td style=\"text-align: left; font-size:10px;\"></td>
        </tr>
        <tr>
            <td  style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">
                Materai
            </td>
            <td   style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">: Rp.". number_format($materai)."</td>
            <td style=\"text-align: left; font-size:10px;\"></td>
            <td style=\"text-align: left; font-size:10px;\"></td>
            <td style=\"text-align: left; font-size:10px;\"></td>
        </tr>
        <tr>
            <td>
                Total Tagihan
            </td>
            <td  colspan=\"4\" style=\"text-align: left; font-size:10px;\">: Rp.". number_format($totalDiskon + $materai)."</td>
            <td style=\"text-align: left; font-size:10px;\"></td>
            <td style=\"text-align: left; font-size:10px;\"></td>
            <td style=\"text-align: left; font-size:10px;\"></td>
        </tr>
        </table>
        <table style=\"text-align: left;\" cellspacing=\"2\";>
                        <tr>
                            <th style=\"text-align: left; font-size:12px;font-weight: bold\">Terbilang</th>
                            <th style=\"text-align: center; font-size:12px;\"></th>
                        </tr>
                        <tr>
                            <th style=\"text-align: left; font-size:12px;font-weight: bold\">#".$this->numtotxt($totalDiskon)."#</th>
                            <th style=\"text-align: center; font-size:12px;\"></th>
                        </tr>
        </table>
        ";
        $pdf::writeHTML($tbl, true, false, false, false, '');

       
        $path = '<img width="60"; height="60" src="resources/assets/img/ttd.png">';
        $html2 = "
        <table style=\"text-align: center;\" cellspacing=\"0\";>
            <tr>
                <th width=\"60%\">
                <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: left; font-size:10px; \">Pembayaran mohon ditransfer ke :</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px\">Bank&nbsp;: Mandiri Mpu Tantular Semarang</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px\">A/n &nbsp;&nbsp;&nbsp;: " . $company['company_name'] . " </div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px\">A/c &nbsp;&nbsp;&nbsp;: 136.007.663270.9 </div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px\">Demikian Tagihan ini kami sampaikan atas kerjasamanya kami ucapakan terimakasih.</div></td>
                </tr>
            
                </table>
                </th>
            </tr>
        </table>
        <table style=\"text-align: left;\" cellspacing=\"5\";>            
            <tr>
                <th>Semarang , ".date('d M Y')." &nbsp;&nbsp;</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">Isti Ramadhani S.Farm.,Apt</th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th style=\"text-align: left; font-size:10px;\">Apoteker</th>
                <th></th>
                <th></th>
            </tr>
        </table>";
                                      
        $pdf::writeHTML($html2, true, false, true, false, '');
        $filename = 'SK_'.$saleskwitansi['sales_kwitansi_no'].'.pdf';
        $pdf::Output($filename, 'I');

    }

    public function getCustomerName($customer_id)
    {
        $unit = CoreCustomer::select('customer_code')
            ->where('customer_id', $customer_id)
            ->where('data_state', 0)
            ->first();

        return $unit['customer_code'] ?? '';
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

    public function getPpnItem($sales_delivery_note_item_id){
        $item = SalesDeliveryNoteItem::where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
        ->where('data_state', 0)
        ->first();

        $salesorderitem = SalesOrderItem::where('sales_order_item_id',$item['sales_order_item_id'])
        ->where('data_state', 0)
        ->first();
        return $salesorderitem['ppn_amount_item'];
    }
}
