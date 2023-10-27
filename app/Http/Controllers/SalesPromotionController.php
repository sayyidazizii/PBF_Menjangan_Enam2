<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\CoreCustomer;
use App\Models\CoreExpedition;
use App\Models\InvGoodsReceivedNote;
use App\Models\InvItem;
use App\Models\InvItemCategory;
use App\Models\InvItemType;
use App\Models\InvItemUnit;
use App\Models\InvItemStock;
use App\Models\PreferenceCompany;
use App\Models\PurchaseOrderItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesCollectionPiece;
use App\Models\SalesDeliveryNote;
use App\Models\SalesDeliveryNoteItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SystemLogUser;
use App\Models\SalesDeliveryNoteItemStock;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PhpParser\Node\Expr\Cast\Object_;
use stdClass;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalesPromotionController extends Controller
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

        $customer_id = Session::get('customer_id');

        Session::forget('salesinvoiceitem');
        Session::forget('salesinvoiceelements');

        $salescolectionpiece = SalesCollectionPiece::select('sales_collection_piece.*','sales_invoice.sales_invoice_date')
        ->join('sales_invoice', 'sales_invoice.sales_invoice_id', 'sales_collection_piece.sales_invoice_id')
        ->where('sales_collection_piece.data_state','=',0)
        // ->where('sales_collection_piece.claim_status', 0)
        ->where('sales_collection_piece.sales_collection_piece_type_id', 1)
        ->where('sales_invoice.sales_invoice_date', '>=', $start_date)
        ->where('sales_invoice.sales_invoice_date', '<=', $end_date);
        if($customer_id||$customer_id!=null||$customer_id!=''){
            $salescolectionpiece   = $salescolectionpiece->where('sales_collection_piece.customer_id', $customer_id);
        }

        $salescolectionpiece       = $salescolectionpiece->get();

        $customer = CoreCustomer::select('customer_id', 'customer_name')
        ->where('data_state', 0)
        ->pluck('customer_name', 'customer_id');
       
        return view('content/SalesPromotion/ListSalesPromotion',compact('salescolectionpiece', 'start_date', 'end_date', 'customer_id', 'customer'));
    }
    
    public function filterSalesPromotion(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $customer_id    = $request->customer_id;
    

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('customer_id', $customer_id);

        return redirect('/sales-promotion');
    }

    public function resetFilterSalesCollectionPiece(){
        Session::forget('start_date');
        Session::forget('end_date');
        Session::forget('customer_id');

        return redirect('/sales-promotion');

    }

   



// -----------------------------------------------------------------------------------------------------------------------------------------------------------



    public function getItemStock($sales_delivery_note_item_id){
        $item = SalesDeliveryNoteItemStock::select('item_stock_id')
        ->where('data_state', 0)
        ->where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
        ->first();

        return $item['item_stock_id'];
    }


    public function getCustomerName($customer_id){
        $customer = CoreCustomer::select('customer_name')
        ->where('data_state', 0)
        ->where('customer_id', $customer_id)
        ->first();

        return $customer['customer_name']??'';
    }

    public function getCustomerNameSalesOrderId($sales_order_id){
        $customer = SalesOrder::select('core_customer.customer_name')
        ->join('core_customer', 'core_customer.customer_id', 'sales_order.customer_id')
        ->where('sales_order.data_state', 0)
        ->where('sales_order.sales_order_id', $sales_order_id)
        ->first();

        if($customer == null){
            return "-";
        }

        return $customer['customer_name'];
    }

    public function getExpeditionName($expedition_id){
        $expedition = CoreExpedition::select('expedition_name')
        ->where('data_state', 0)
        ->where('expedition_id', $expedition_id)
        ->first();

        return $expedition['expedition_name'];
    }

    public function getSalesDeliveryNoteItem($sales_delivery_note_item_id){
        $salesdeliverynoteitem = SalesDeliveryNoteItem::where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
        ->first();

        return $salesdeliverynoteitem;
    }

    public function getItemCategoryName($item_category_id){
        $item = InvItemCategory::select('item_category_name')
        ->where('data_state', 0)
        ->where('item_category_id', $item_category_id)
        ->first();

        return $item['item_category_name'];
    }

    public function getItemTypeName($item_type_id){
        $item = InvItemType::select('item_type_name')
        ->where('data_state', 0)
        ->where('item_type_id', $item_type_id)
        ->first();

        return $item['item_type_name'];
    }

    public function getItemUnitName($item_unit_id){
        $item = InvItemUnit::select('item_unit_name')
        ->where('data_state', 0)
        ->where('item_unit_id', $item_unit_id)
        ->first();

        if($item == null){
            return '-';
        }

        return $item['item_unit_name'];
    }

    public function getDiscountType($item_type_id)
    {
        $data = PurchaseOrderItem::select('discount_percentage')
            ->where('data_state', 0)
            ->where('item_type_id', $item_type_id)
            ->first();

        return $data['discount_percentage'] ?? '';
    }

    public function getItemName($item_type_id){
        $invitem = InvItemType::select('inv_item_type.item_type_id', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name) AS item_name'))
        ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
        ->where('item_type_id', $item_type_id)
        ->where('inv_item_type.data_state','=',0)
        ->first();

        //dd($invitem);
        if($invitem == null){
            return '-';
        }


        return $invitem['item_name'];
    }

    public function changeSalesDeliveryNote(Request $request){
        $sales_delivery_note_id = $request->sales_delivery_note_id;
        
        $data = new stdClass;

        $salesdeliverynote = SalesDeliveryNote::select('sales_delivery_order.sales_order_id', 'sales_delivery_note.*')
        ->join('sales_delivery_order', 'sales_delivery_order.sales_delivery_order_id', 'sales_delivery_note.sales_delivery_order_id')
        ->where('sales_delivery_note_id', $sales_delivery_note_id)
        ->where('sales_delivery_note.data_state', 0)
        ->first();

        $salesdeliverynoteitem = SalesDeliveryNoteItem::where('sales_delivery_note_id', $salesdeliverynote['sales_delivery_note_id'])
        ->where('sales_delivery_note_item.data_state', 0)
        ->first();

        $salesorder = SalesOrder::where('sales_order_id', $salesdeliverynote['sales_order_id'])
        ->first();

        $customer = CoreCustomer::where('customer_id', $salesorder['customer_id'])
        ->where('data_state', 0)
        ->first();

        $expedition = CoreExpedition::where('expedition_id', $salesdeliverynote['expedition_id'])
        ->where('data_state', 0)
        ->first();

        $data->salesdeliverynote        = $salesdeliverynote;
        $data->salesdeliverynoteitem    = $salesdeliverynoteitem;
        $data->salesorder               = $salesorder;
        $data->customer                 = $customer;
        $data->expedition               = $expedition;

        return response()->json(json_encode($data));
    }
    public function getUnitCost($item_type_id)
    {
        $data = PurchaseOrderItem::select('item_unit_cost')
            ->where('data_state', 0)
            ->where('item_type_id', $item_type_id)
            ->first();

        return $data['item_unit_cost'] ?? '';
    }
    public function getSOitemId($sales_delivery_note_item_id)
    {
        $data = SalesDeliveryNoteItem::select('sales_order_item_id')
            ->where('data_state', 0)
            ->where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
            ->first();

        return $data['sales_order_item_id'] ?? '';
    }

    public function getDiscountAmt($sales_order_item_id)
    {
        $data = SalesOrderItem::select('discount_percentage_item')
            ->where('sales_order_item_id', $sales_order_item_id)
            ->first();

        return $data['discount_percentage_item'] ?? '';
    }

    public function getDiscountAmtB($sales_order_item_id)
    {
        $data = SalesOrderItem::select('discount_percentage_item_b')
            ->where('sales_order_item_id', $sales_order_item_id)
            ->first();

        return $data['discount_percentage_item_b'] ?? '';
    }

    
	public function set_log($user_id, $username, $id, $class, $pk, $remark){

		date_default_timezone_set("Asia/Jakarta");

		$log = array(
			'user_id'		=>	$user_id,
			'username'		=>	$username,
			'id_previllage'	=> 	$id,
			'class_name'	=>	$class,
			'pk'			=>	$pk,
			'remark'		=> 	$remark,
			'log_stat'		=>	'1',
			'log_time'		=>	date("Y-m-d G:i:s")
		);
		return SystemLogUser::create($log);
	}
    
    public function export(){
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

        $customer_id = Session::get('customer_id');

        Session::forget('salesinvoiceitem');
        Session::forget('salesinvoiceelements');

        $salescolectionpiece = SalesCollectionPiece::select('sales_collection_piece.*','sales_invoice.sales_invoice_date','sales_invoice.*','sales_invoice_item.*')
        ->join('sales_invoice', 'sales_invoice.sales_invoice_id', 'sales_collection_piece.sales_invoice_id')
        ->join('sales_invoice_item', 'sales_invoice_item.sales_invoice_id', 'sales_invoice.sales_invoice_id')
        ->where('sales_collection_piece.data_state','=',0)
        // ->where('sales_collection_piece.claim_status', 0)
        ->where('sales_collection_piece.sales_collection_piece_type_id', 1)
        ->where('sales_invoice.sales_invoice_date', '>=', $start_date)
        ->where('sales_invoice.sales_invoice_date', '<=', $end_date);
        if($customer_id||$customer_id!=null||$customer_id!=''){
            $salescolectionpiece   = $salescolectionpiece->where('sales_collection_piece.customer_id', $customer_id);
        }

        $salescolectionpiece       = $salescolectionpiece->get();

        $customer = CoreCustomer::select('customer_id', 'customer_name')
        ->where('data_state', 0)
        ->pluck('customer_name', 'customer_id');

        $preference_company         = PreferenceCompany::first();
        //dd($salescolectionpiece);

       $spreadsheet = new Spreadsheet();

       if(count($salescolectionpiece)>=0){
           $spreadsheet->getProperties()->setCreator("TRADING SYSTEM")
               ->setLastModifiedBy("TRADING SYSTEM")
               ->setTitle("REKAP BIAYA PROMOSI")
               ->setSubject("")
               ->setDescription("REKAP BIAYA PROMOSI")
               ->setKeywords("REKAP BIAYA PROMOSI")
               ->setCategory("REKAP BIAYA PROMOSI");

           $sheet = $spreadsheet->getActiveSheet(0);
           $spreadsheet->getActiveSheet()->setTitle("REKAP BIAYA PROMOSI");
           $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
           $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
           $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
           $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
           $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(25);
           $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);
           $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);


           $spreadsheet->getActiveSheet()->mergeCells("B5:L5");
           $spreadsheet->getActiveSheet()->mergeCells("B6:L6");
           $spreadsheet->getActiveSheet()->mergeCells("B7:L7");
           $spreadsheet->getActiveSheet()->mergeCells("B8:L8");
           $spreadsheet->getActiveSheet()->mergeCells("B9:L9");
           $spreadsheet->getActiveSheet()->mergeCells("B10:L10");
           $spreadsheet->getActiveSheet()->mergeCells("B11:L11");
           $spreadsheet->getActiveSheet()->getStyle('B5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
           $spreadsheet->getActiveSheet()->getStyle('B6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
           $spreadsheet->getActiveSheet()->getStyle('B7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
           $spreadsheet->getActiveSheet()->getStyle('B8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
           $spreadsheet->getActiveSheet()->getStyle('B9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
           $spreadsheet->getActiveSheet()->getStyle('B10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
           $spreadsheet->getActiveSheet()->getStyle('B11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
           $spreadsheet->getActiveSheet()->getStyle('B11')->getFont()->setBold(true)->setSize(16);

           $spreadsheet->getActiveSheet()->getStyle('B12:L12')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
           $spreadsheet->getActiveSheet()->getStyle('B12:L12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);




           $sheet->setCellValue('B5', "PBF MENJANGAN ENAM ");	
           $sheet->setCellValue('B6', "Jl.Puspowarno Raya No 55D RT 06 RW 09");
           $sheet->setCellValue('B7', "APA : ".Auth::user()->name."");
           $sheet->setCellValue('B8', " SIKA: 449.2/16/DPM-PTSP/SIKA.16/III/2019 ");
           $sheet->setCellValue('B9', "");
           $sheet->setCellValue('B10', "");
           $sheet->setCellValue('B11', "REKAP BIAYA PROMOSI Periode ".$start_date." - ".$end_date);	
           $sheet->setCellValue('B12', "No");
           $sheet->setCellValue('C12', "TGL Faktur");
           $sheet->setCellValue('D12', "Nama Barang");
           $sheet->setCellValue('E12', "HNA");
           $sheet->setCellValue('F12', "QTY");
           $sheet->setCellValue('G12', "NETTO");
           $sheet->setCellValue('H12', "DPP");
           $sheet->setCellValue('I12', "PPN");
           $sheet->setCellValue('J12', "VALUE");
           $sheet->setCellValue('K12', "DISKON");
           $sheet->setCellValue('L12', "%DISKON");
           
           $j  = 13;
           $no = 1;

           if(count($salescolectionpiece)==0){
            $lastno = 2;
            $lastj = 13;
           }else{
           
           foreach($salescolectionpiece as $key => $val){

            $itemunitcost = $this->getUnitCost($val['item_type_id'] ?? '') ;
            $diskon = $this->getDiscountType($val['item_type_id']);
            $diskonAmount =  (int)$diskon * 1/100; 
            $unitCost =  $diskonAmount * (int)$itemunitcost;
            $uniCostAmount = (int)$itemunitcost - $unitCost; 

            
            $sales_delivery_note_item_id = $this->getSOitemId($val['sales_delivery_note_item_id']);

            $diskonPersen =  (int)$this->getDiscountAmt($sales_delivery_note_item_id) + (int)$this->getDiscountAmtB($sales_delivery_note_item_id) ;

               $sheet = $spreadsheet->getActiveSheet(0);
               $spreadsheet->getActiveSheet()->setTitle("REKAP BIAYA PROMOSI");
               $spreadsheet->getActiveSheet()->getStyle('B'.$j.':L'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
               $spreadsheet->getActiveSheet()->getStyle('N'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
             
               $sheet->setCellValue('B'.$j, $no);
               $sheet->setCellValue('C'.$j, $val['sales_invoice_date']);
               $sheet->setCellValue('D'.$j, $this->getItemTypeName($val['item_type_id']));
               $sheet->setCellValue('E'.$j, $uniCostAmount);
               $sheet->setCellValue('F'.$j, $val['quantity']);
               $sheet->setCellValue('G'.$j, $val['quantity'] * (int)$uniCostAmount);
               $sheet->setCellValue('H'.$j, $val['subtotal_price_B']);
               $sheet->setCellValue('I'.$j, $val['tax_amount']);
               $sheet->setCellValue('J'.$j, $val['tax_amount'] + $val['subtotal_price_B']);
               $sheet->setCellValue('K'.$j, $val['discount_A'] + $val['discount_B'] );
               $sheet->setCellValue('L'.$j, " $diskonPersen  %");
         
               $no++;
               $j++;
               $lastno = $no;
               $lastj = $j;
               
           }

            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->getStyle('B'.$lastj.':L'.$lastj)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setCellValue('G' . $lastj , 'Jumlah Total:');
            $sumrangeQty = 'H'. $lastno - 1 .':H'.$j;
            $sheet->setCellValue('H' . $lastj , '=SUM('.$sumrangeQty.')');

            $sumrangehpp = 'I'. $lastno - 1 .':I'.$j;
            $sheet->setCellValue('I' . $lastj , '=SUM('.$sumrangehpp.')');

            $sumrangediskon = 'J'. $lastno - 1 .':J'.$j;
            $sheet->setCellValue('J' . $lastj , '=SUM('.$sumrangediskon.')');

            $sumrangedpp = 'K'. $lastno - 1 .':K'.$j;
            $sheet->setCellValue('K' . $lastj , '=SUM('.$sumrangedpp.')');

            // $sumrangeppn = 'L'. $lastno - 1 .':L'.$j;
            // $sheet->setCellValue('L' . $lastj , '=SUM('.$sumrangeppn.')');

            $sheet->setCellValue('F' . $lastj + 1, 'Mengetahui');
            $sheet->setCellValue('K' . $lastj + 1, 'Dibuat Oleh');


            $spreadsheet->getActiveSheet()->getStyle('E'.$lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('E'.$lastj + 5)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('G'.$lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('K'.$lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
           
           
            $sheet->setCellValue('E' . $lastj + 5, 'Apoteker');
            $sheet->setCellValue('G' . $lastj + 5, 'Administrasi Pajak');
            $sheet->setCellValue('K' . $lastj + 5, 'Dibuat Oleh');


     }

           ob_clean();
           $filename='SALES PROMOTION '.date('d M Y').'.xls';
           header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
           header('Content-Disposition: attachment;filename="'.$filename.'"');
           header('Cache-Control: max-age=0');

           $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
           $writer->save('php://output');
       }else{
           echo "Maaf data yang di eksport tidak ada !";
       }


    }



    
}
