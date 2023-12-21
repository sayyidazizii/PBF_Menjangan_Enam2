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

class SalesCollectionDiscountController extends Controller
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
        $sales_collection_piece_type_id = Session::get('sales_collection_piece_type_id');

        Session::forget('salesinvoiceitem');
        Session::forget('salesinvoiceelements');

        $salescolectionpiece = SalesCollectionPiece::select('sales_collection_piece.*','sales_invoice.sales_invoice_date')
        ->join('sales_invoice', 'sales_invoice.sales_invoice_id', 'sales_collection_piece.sales_invoice_id')
        ->where('sales_collection_piece.data_state','=',0)
        ->where('sales_collection_piece.claim_status', 1)
        ->where('sales_collection_piece.claim_date', '>=', $start_date)
        ->where('sales_collection_piece.claim_date', '<=', $end_date);
        if($customer_id||$customer_id!=null||$customer_id!=''){
            $salescolectionpiece   = $salescolectionpiece->where('sales_collection_piece.customer_id', $customer_id);
        }

        if($sales_collection_piece_type_id||$sales_collection_piece_type_id!=null||$sales_collection_piece_type_id!=''){
            $salescolectionpiece   = $salescolectionpiece->where('sales_collection_piece.sales_collection_piece_type_id', $sales_collection_piece_type_id);
        }

        $salescolectionpiece       = $salescolectionpiece->get();

        $customer = CoreCustomer::select('customer_id', 'customer_name')
        ->where('data_state', 0)
        ->pluck('customer_name', 'customer_id');
        $sales_collection_piece_type = array(
            '1' => 'promosi',
            '2' => 'biasa'
        );
        return view('content/SalesCollectionDiscount/ListSalesCollectionDiscount',compact('sales_collection_piece_type_id','sales_collection_piece_type','salescolectionpiece', 'start_date', 'end_date', 'customer_id', 'customer'));
    }
    
    public function filterSalesCollectionPiece(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $customer_id    = $request->customer_id;
        $sales_collection_piece_type_id    = $request->sales_collection_piece_type_id;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('customer_id', $customer_id);
        Session::put('sales_collection_piece_type_id', $sales_collection_piece_type_id);

        return redirect('/sales-collection-piece');
    }

    public function resetFilterSalesCollectionPiece(){
        Session::forget('start_date');
        Session::forget('end_date');
        Session::forget('customer_id');
        Session::forget('sales_collection_piece_type_id');

        return redirect('/sales-collection-piece');

    }

   
    public function searchCoreCustomer(){

        Session::forget('salescollectionelements');
        Session::forget('datasalescollectiontransfer');
        
        $corecustomer = SalesInvoice::select('sales_invoice.customer_id', 'core_customer.customer_name', 'core_customer.customer_address', DB::raw("SUM(sales_invoice.owing_amount) as total_owing_amount"))
        ->join('core_customer', 'core_customer.customer_id', 'sales_invoice.customer_id')
        ->where('sales_invoice.data_state', 0)
        ->where('core_customer.data_state', 0)
        ->groupBy('sales_invoice.customer_id')
        ->orderBy('core_customer.customer_name', 'ASC')
        ->get();
        
        return view('content/SalesCollectionDiscount/searchCoreCustomer',compact('corecustomer'));
    }

    public function ClaimSalesCollectionPiece($sales_collection_piece_id)
    {
        $salescolectionpiece = SalesCollectionPiece::select('sales_collection_piece.*','sales_invoice.sales_invoice_date')
        ->join('sales_invoice', 'sales_invoice.sales_invoice_id', 'sales_collection_piece.sales_invoice_id')
        ->where('sales_collection_piece.sales_collection_piece_id',$sales_collection_piece_id)
        ->where('sales_collection_piece.data_state','=',0)
        ->where('claim_status', 0)
        ->get();

        return view('content/SalesCollection/FormAddSalesCollectionPiece',compact('salescolectionpiece'));
    }


    public function processClaimSalesCollectionPiece(Request $request){

       SalesCollectionPiece::where('sales_collection_piece_id',$request->sales_collection_piece_id)
        ->Update(['claim_status' => 1,'claim_date' =>  \Carbon\Carbon::now()]);
    
        
        return redirect('/sales-collection-piece');
    }



    public function detailClaimSalesCollectionPiece($sales_collection_piece_id)
    {
        $salescolectionpiece = SalesCollectionPiece::select('sales_collection_piece.*','sales_invoice.sales_invoice_date')
        ->join('sales_invoice', 'sales_invoice.sales_invoice_id', 'sales_collection_piece.sales_invoice_id')
        ->where('sales_collection_piece.sales_collection_piece_id',$sales_collection_piece_id)
        ->where('sales_collection_piece.data_state','=',0)
        ->get();

        return view('content/SalesCollection/FormDetailSalesCollectionPiece',compact('salescolectionpiece'));
    }

    public function detailClaimSalesCollection($sales_invoice_id)
    {
        $salesinvoice = SalesInvoice::where('sales_invoice_id',$sales_invoice_id)
        ->where('data_state','=',0)
        ->first();
        $salescolectionpiece = SalesCollectionPiece::select('sales_collection_piece.*','sales_invoice.sales_invoice_date')
        ->join('sales_invoice', 'sales_invoice.sales_invoice_id', 'sales_collection_piece.sales_invoice_id')
        ->where('sales_collection_piece.sales_invoice_id',$sales_invoice_id)
        ->where('sales_collection_piece.data_state','=',0)
        ->where('sales_collection_piece.claim_status','=',0)
        ->get();

        return view('content/SalesCollection/FormdetailListPiece',compact('salescolectionpiece','salesinvoice'));
    }

    public function CancelClaimSalesCollectionPiece($sales_collection_piece_id)
    {
        
        $salescolectionpiece = SalesCollectionPiece::select('sales_collection_piece.*','sales_invoice.sales_invoice_date')
        ->join('sales_invoice', 'sales_invoice.sales_invoice_id', 'sales_collection_piece.sales_invoice_id')
        ->where('sales_collection_piece.sales_collection_piece_id',$sales_collection_piece_id)
        ->where('sales_collection_piece.data_state','=',0)
        ->get();

        return view('content/SalesCollection/FormCancelSalesCollectionPiece',compact('salescolectionpiece'));
    }
    

    public function processCancelClaimSalesCollectionPiece(Request $request){

        SalesCollectionPiece::where('sales_collection_piece_id',$request->sales_collection_piece_id)
         ->Update(['claim_status' => 0,'claim_date' => 0]);
     
         
         return redirect('/sales-collection-piece');
     }


    public function processAddSalesCollectionPiece(Request $request){

        $data = array(
                'sales_invoice_id'	            => $request->sales_invoice_id,
                'sales_invoice_no'	            => $request->sales_invoice_no,
                'customer_id'	                => $request->customer_id,
                'total_amount'	                => $request->total_amount,
                'piece_amount'	                => $request->piece_amount,
                'total_amount_after_piece'	    => $request->total_amount - $request->piece_amount,
                'sales_collection_piece_type_id'=> $request->sales_collection_piece_type_id,
                'promotion_no'	                => $request->promotion_no,
                'memo_no'	                    => $request->memo_no,
                'claim_status'	                => 0,
                'created_id'                    => Auth::id(),
        );


        $invoice = SalesInvoice::findOrfail($request->sales_invoice_id);
       // dd($invoice);
       if ($request->piece_amount > $invoice->owing_amount){
            $msg = 'Potongan Tidak Boleh lebih Besar';
       }else{
            $invoice->owing_amount =  $invoice->owing_amount - $request->piece_amount;
            $invoice->save();
        //dd($request->all());
        if(SalesCollectionPiece::create($data)){
            $msg = 'Tambah Potongan Berhasil';
        }else{
            $msg = 'Tambah Potongan Gagal';
        }
       }      
        return redirect()->back()->with('msg',$msg);
    }

    public function processDeleteSalesCollectionPiece(Request $request){ 
        
        $item1 = SalesCollectionPiece::select('*')
        ->where('sales_collection_piece_id', $request->sales_collection_piece_id)
        ->first();

        $invoice = SalesInvoice::findOrfail($item1->sales_invoice_id);
        $invoice->owing_amount =  $invoice->owing_amount + $item1->piece_amount;
        $invoice->save();

        $item = SalesCollectionPiece::where('sales_collection_piece_id', $request->sales_collection_piece_id);

        //dd($request->sales_delivery_order_item_stock_temporary_id);

        if($item->delete()){
            $msg = 'Hapus Stock Sales Delivery Order Berhasil';
        }else{
            $msg = 'Hapus Stock Sales Delivery Order Gagal';
        }
        return redirect()->back()->with('msg',$msg);
    }

    


    public function addSalesInvoicePiece(Request $request)
    {
        $salesinvoiceowing = SalesInvoice::select('sales_invoice.sales_invoice_id', 'sales_invoice.customer_id', 'sales_invoice.owing_amount', 'sales_invoice.sales_invoice_date', 'sales_invoice.paid_amount', 'sales_invoice.sales_invoice_no', 'sales_invoice.subtotal_amount', 'sales_invoice.discount_percentage', 'sales_invoice.discount_amount', 'sales_invoice.total_amount')
        ->where('sales_invoice.customer_id', $request->customer_id)
        ->where('sales_invoice.owing_amount', '>', 0)
        ->where('sales_invoice.data_state', 0)
        ->get();

        return view('content/SalesInvoice/FormAddSalesInvoice',compact('salesdeliverynote', 'salesdeliverynoteitem', 'sales_delivery_note_id', 'coreexpedition'));
    }









// -----------------------------------------------------------------------------------------------------------------------------------------------------------





    public function editSalesInvoice($sales_invoice_id)
    {
        $salesinvoice = SalesInvoice::findOrFail($sales_invoice_id);

        $salesorder = SalesOrder::where('sales_order_id', $salesinvoice['sales_order_id'])
        ->where('data_state', 0)
        ->first();
        
        $salesinvoiceitem = SalesInvoiceItem::select('sales_invoice_item.*')
        ->where('data_state', 0)
        ->where('sales_invoice_id', $sales_invoice_id)
        ->get();

        $salesdeliverynote = SalesDeliveryNote::select('sales_delivery_note.*','inv_warehouse.*')
        ->join('inv_warehouse', 'inv_warehouse.warehouse_id', 'sales_delivery_note.warehouse_id')
        ->where('sales_delivery_note.data_state', 0)
        ->where('sales_delivery_note.sales_delivery_note_id', $salesinvoice['sales_delivery_note_id'])
        ->first();

        return view('content/SalesInvoice/FormEditSalesInvoice',compact('salesinvoice', 'salesinvoiceitem', 'salesdeliverynote', 'salesorder', 'sales_invoice_id'));
    }

    public function detailSalesInvoice($sales_invoice_id)
    {
        $salesinvoice = SalesInvoice::findOrFail($sales_invoice_id);

        $salesorder = SalesOrder::where('sales_order_id', $salesinvoice['sales_order_id'])
        ->where('data_state', 0)
        ->first();
        
        $salesinvoiceitem = SalesInvoiceItem::select('sales_invoice_item.*')
        ->where('data_state', 0)
        ->where('sales_invoice_id', $sales_invoice_id)
        ->get();

        $salesdeliverynote = SalesDeliveryNote::select('sales_delivery_note.*','inv_warehouse.*')
        ->join('inv_warehouse', 'inv_warehouse.warehouse_id', 'sales_delivery_note.warehouse_id')
        ->where('sales_delivery_note.data_state', 0)
        ->where('sales_delivery_note.sales_delivery_note_id', $salesinvoice['sales_delivery_note_id'])
        ->first();

        return view('content/SalesInvoice/FormDetailSalesInvoice',compact('salesinvoice', 'salesinvoiceitem', 'salesdeliverynote', 'salesorder', 'sales_invoice_id'));
    }

    public function voidSalesInvoice($sales_invoice_id)
    {
        $salesinvoice = SalesInvoice::findOrFail($sales_invoice_id);

        $salesorder = SalesOrder::where('sales_order_id', $salesinvoice['sales_order_id'])
        ->where('data_state', 0)
        ->first();
        
        $salesinvoiceitem = SalesInvoiceItem::select('sales_invoice_item.*')
        ->where('data_state', 0)
        ->where('sales_invoice_id', $sales_invoice_id)
        ->get();

        $salesdeliverynote = SalesDeliveryNote::select('sales_delivery_note.*')
        ->where('data_state', 0)
        ->where('sales_delivery_note_id', $salesinvoice['sales_delivery_note_id'])
        ->first();

        return view('content/SalesInvoice/FormVoidSalesInvoice',compact('salesinvoice', 'salesinvoiceitem', 'salesdeliverynote', 'salesorder', 'sales_invoice_id'));
    }

    public function closedSalesInvoice($sales_invoice_id)
    {
        $salesinvoice = SalesInvoice::findOrFail($sales_invoice_id);

        $salesorder = SalesOrder::where('sales_order_id', $salesinvoice['sales_order_id'])
        ->where('data_state', 0)
        ->first();
        
        $salesinvoiceitem = SalesInvoiceItem::select('sales_invoice_item.*')
        ->where('data_state', 0)
        ->where('sales_invoice_id', $sales_invoice_id)
        ->get();

        $salesdeliverynote = SalesDeliveryNote::select('sales_delivery_note.*')
        ->where('data_state', 0)
        ->where('sales_delivery_note_id', $salesinvoice['sales_delivery_note_id'])
        ->first();

        return view('content/SalesInvoice/FormClosedSalesInvoice',compact('salesinvoice', 'salesinvoiceitem', 'salesdeliverynote', 'salesorder', 'sales_invoice_id'));
    }

    public function  processClosedSalesInvoice(Request $request){
        $salesinvoice = SalesInvoice::findOrFail($request->sales_invoice_id);
        $salesinvoice->sales_invoice_status = 1;

        if($salesinvoice->save()){
            $msg = 'Closing Sales Invoice Berhasil';
            return redirect('/sales-invoice')->with('msg',$msg);
        }else{
            $msg = 'Closing Sales Invoice Gagal';
            return redirect('/sales-invoice/closed/'.$request->sales_invoice_id)->with('msg',$msg);
        }
    }



    public function processVoidSalesInvoice(Request $request){
        $fields = $request->validate([
            'sales_invoice_id'   => 'required',
        ]);

        print_r($fields['sales_invoice_id']);
        
        $salesinvoice = SalesInvoice::findOrFail($request->sales_invoice_id);
        $salesinvoice->voided_id     = Auth::id();
        $salesinvoice->voided_on     = date('Y-m-d');
        $salesinvoice->data_state    = 1;

        if($salesinvoice->save()){
            $salesinvoiceitem = SalesInvoiceItem::where('sales_invoice_id', $request->sales_invoice_id)->get();

            $salesdeliverynote = SalesDeliveryNote::findOrFail($salesinvoice['sales_delivery_note_id']);
            $salesdeliverynote->sales_invoice_status = 0;
            $salesdeliverynote->save();

            foreach($salesinvoiceitem as $val){
                $dataitem = SalesInvoiceItem::where('sales_invoice_item_id', $val['sales_invoice_item_id'])->first();
                $dataitem->data_state = 1;
                $dataitem->save();
            }

            $msg = 'Void Sales Invoice Berhasil';
            return redirect('/sales-invoice')->with('msg',$msg);
        }else{
            $msg = 'Void Sales Invoice Gagal';
            return redirect('/sales-invoice/void/'.$fields['sales_invoice_id'])->with('msg',$msg);
        }
    }


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
    
    public function processPrintingSalesInvoice($sales_invoice_id){
        $preference_company 		= PreferenceCompany::first();

        $salesinvoice				= SalesInvoice::select('sales_invoice.*', 'sales_order.*', 'sales_delivery_note.*', 'core_customer.*')
        ->join('sales_order', 'sales_order.sales_order_id', 'sales_invoice.sales_order_id')
        ->join('sales_delivery_note', 'sales_delivery_note.sales_delivery_note_id', 'sales_invoice.sales_delivery_note_id')
        ->join('core_customer', 'core_customer.customer_id', 'sales_invoice.customer_id')
        ->where('sales_invoice.sales_invoice_id', $sales_invoice_id)
        ->first();
        
        $salesinvoiceitem			= SalesInvoiceItem::where('sales_invoice_item.sales_invoice_id', $sales_invoice_id)
        ->get();

        $customer_tax_no 			= $salesinvoice['customer_tax_no'];
        $ppn_percentage 			= $preference_company['ppn'];

        $this->set_log(Auth::id(), Auth::user()->name, '2141', 'SalesInvoice.printSalesInvoice', Auth::user()->name, 'Print Sales Invoice');


        // create new PDF document
        $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 6, 6, 6); // put space of 10 on top

        // set image scale factor
        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        $pdf::SetFont('helvetica', 'B', 20);

        // add a page
        $pdf::AddPage();

        /*$pdf::Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);*/

        $pdf::SetFont('helvetica', '', 10);

        // -----------------------------------------------------------------------------

        /*print_r($preference_company);*/
        $tbla = "";
            if(trim($customer_tax_no) != ''){
                $tbla = "
                    <table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" >
                        <tr>
                            <td style=\"text-align:center;width:25%\">
                                <table id=\"items\" width=\"100%\" cellpadding=\"1\" >
                                    <tr>
                                        <td colspan=\"2\" rowspan=\"2\">
                                            <div style=\"font-size:25px\";><b>
                                                I N V O I C E
                                            </b></div>
                                        </td>
                                    </tr>
                                </table>
                            </td>

                            <td style=\"text-align:center;width:35%\">
                                <table id=\"items\" width=\"100%\" cellpadding=\"1\">
                                    <tr>
                                        <td style=\"text-align:left;width:30%\">
                                            <div style=\"font-size:13.5px\">
                                                NO. INV
                                            </div>
                                        </td>
                                        <td style=\"text-align:left;width:70%\">
                                            <div style=\"font-size:13.5px\">
                                                : ".$salesinvoice['sales_invoice_no']."
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style=\"text-align:left;width:30%\">
                                            <div style=\"font-size:13.5px\">
                                                TGL
                                            </div>
                                        </td>
                                        <td style=\"text-align:left;width:70%\">
                                            <div style=\"font-size:13.5px\">
                                                : ".date('d M Y',strtotime($salesinvoice['sales_invoice_date']))."
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style=\"text-align:left; height:20%;\">".$preference_company['company_name']."
                                <br>".$preference_company['company_address']."<br>Telp./Fax :
                                ".$preference_company['company_home_phone1']."<br>N.P.W.P :
                                ".$preference_company['company_tax_number']."
                            </td>
                        </tr>
                    </table>
                    <br><br>
                ";
            } else {
                $tbla = "
                    <table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\">
                        <tr>
                            <td style=\"text-align:center;width:25%\">
                                <table id=\"items\" width=\"100%\" cellpadding=\"1\" >
                                    <tr>
                                        <td colspan=\"2\" rowspan=\"2\">
                                            <div style=\"font-size:25px\";><b>
                                                I N V O I C E
                                            </b></div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style=\"text-align:center;width:35%\">
                                <table id=\"items\" width=\"100%\" cellpadding=\"1\" border=\"0\">
                                    <tr>
                                        <td style=\"text-align:left;width:30%\">
                                            <div style=\"font-size:13.5px\">
                                                NO. INV
                                            </div>
                                        </td>
                                        <td style=\"text-align:left;width:70%\">
                                            <div style=\"font-size:13.5px\">
                                                : ".$salesinvoice['sales_invoice_no']."
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style=\"text-align:left;width:30%\">
                                            <div style=\"font-size:13.5px\">
                                                TGL
                                            </div>
                                        </td>
                                        <td style=\"text-align:left;width:70%\">
                                            <div style=\"font-size:13.5px\">
                                                : ".date('d M Y',strtotime($salesinvoice['sales_invoice_date']))."
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style=\"text-align:left;width:40%; height:20%;\">
                                
                            </td>
                        </tr>
                    </table>
                    <br><br>
                ";
            }

            // $pdf->writeHTML($tbl, true, false, false, false, '');
        

        $tbl = "
        <table cellspacing=\"0\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td style=\"text-align:left;width:10%\">Pelanggan </td>
                <td style=\"text-align:left;width:5%\"> : </td>
                <td style=\"text-align:left;width:45%\">".$salesinvoice['customer_name']."</td>
                <td style=\"text-align:left;width:5%\"></td>
                <td style=\"text-align:left;width:12%\">No. SJ</td>
                <td style=\"text-align:left;width:2%\"> : </td>
                <td style=\"text-align:left;width:20%\"><div style=\"font-size:13.5px\">".$salesinvoice['sales_delivery_note_no']."</div></td>
            </tr>
            <tr>
                <td style=\"text-align:left;width:10%\">Alamat</td>
                <td style=\"text-align:left;width:5%\"> : </td>
                <td style=\"text-align:left;width:45%\">".$salesinvoice['customer_address']."</td>
                <td style=\"text-align:left;width:5%\"></td>
                <td style=\"text-align:left;width:12%\">TGL. SJ</td>
                <td style=\"text-align:left;width:2%\"> : </td>
                <td style=\"text-align:left;width:20%\"><div style=\"font-size:13.5px\">".date('d M Y', strtotime($salesinvoice['sales_delivery_note_date']))."</div></td>
            </tr>
            <tr>
                <td style=\"text-align:left;width:10%\"></td>
                <td style=\"text-align:left;width:5%\"></td>
                <td style=\"text-align:left;width:45%\"></td>
                <td style=\"text-align:left;width:5%\"></td>
                <td style=\"text-align:left;width:12%\">Jatuh Tempo</td>
                <td style=\"text-align:left;width:2%\"> : </td>
                <td style=\"text-align:left;width:20%\"><div style=\"font-size:13.5px\">".date('d M Y', strtotime($salesinvoice['sales_invoice_due_date']))."</div></td>
            </tr>
            <tr>
                <td style=\"text-align:left;width:10%\"></td>
                <td style=\"text-align:left;width:5%\"></td>
                <td style=\"text-align:left;width:45%\"></td>
                <td style=\"text-align:left;width:5%\"></td>
                <td style=\"text-align:left;width:12%\"></td>
                <td style=\"text-align:left;width:2%\"></td>
                <td style=\"text-align:left;width:20%\"><div style=\"font-size:13.5px\"></div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tbla.$tbl, true, false, false, false, '');


        $tbl1 = "
        <table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\">			        
            <tr>
                <th style=\"text-align:center;border-top: 1px solid black;border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;\" width=\"5%\"><div style=\"font-size:14px\">No</div></th>
                <th style=\"text-align:center;border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;\" width=\"20%\"><div style=\"font-size:14px\">Item Name</div></th> 
                <th style=\"text-align:center;border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;\" width=\"15%\"><div style=\"font-size:14px\">Unit</div></th>
                <th style=\"text-align:center;border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;\" width=\"9%\"><div style=\"font-size:14px\">Curr</div></th>
                <th style=\"text-align:center;border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;\" width=\"10%\"><div style=\"font-size:14px\">Qty</div></th> 
                <th style=\"text-align:center;border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;\" width=\"25%\"><div style=\"font-size:14px\">Harga</div></th>
                <th style=\"text-align:center;border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;\" width=\"16%\"><div style=\"font-size:14px\">Total</div></th>
            </tr>
            ";
        
        
            $no =1;
            $tbl2 = "";
            $total_price = 0;
                foreach($salesinvoiceitem as $key => $val){
                    if($val['quantity'] != 0){
                        $cur = 'IDR';
                        $rate = 1;
                        
                            $tbl2 .= "
                            <tr>
                                <td style=\"text-align:center;border-left: 1px solid black;\"><div style=\"font-size:13.5px\">".$no."</div></td>
                                <td style=\"text-align:left;border-left: 1px solid black;\"><div style=\"font-size:13.5px\">&nbsp;".$this->getItemName($val['item_id'])."</div></td>
                                <td style=\"text-align:left;border-left: 1px solid black;\"><div style=\"font-size:13.5px\">&nbsp;".$this->getItemUnitName($val['item_unit_id'])."</div></td>
                                <td style=\"text-align:left;border-left: 1px solid black;\"><div style=\"font-size:13.5px\">&nbsp;".$cur." </div></td>
                                <td style=\"text-align:right;border-left: 1px solid black;\"><div style=\"font-size:13.5px\">".$val['quantity']." &nbsp;</div></td>
                                <td style=\"text-align:right;border-left: 1px solid black;\"><div style=\"font-size:13.5px\">".number_format($val['item_unit_price'], 2)." &nbsp;</div></td>
                                <td style=\"text-align:right;border-left: 1px solid black;border-right: 1px solid black;\"><div style=\"font-size:13.5px\">".number_format(($val['quantity']*$val['item_unit_price']), 2)." &nbsp;</div></td>
                            </tr>
                            ";		
                        $total_price += ($val['quantity']*$val['item_unit_price']);
                        $dpp = $salesinvoice['subtotal'];							
                        if($customer_tax_no != ''){
                            // $ppn = $dpp * 0.1;
                            if($salesinvoice['customer_kawasan_berikat'] == 1){
                                $ppn = $salesinvoice['ppn_amount'];
                                $total = $salesinvoice['total_amount'] + $ppn;
                            } else if($salesinvoice['customer_kawasan_berikat'] == 0) {
                                
                                $total = $salesinvoice['total_amount'];
                                $ppn = $total - $dpp;
                            }
                        } else{
                            $ppn = 0;
                            $total = $salesinvoice['total_amount'];
                        }

                        $no++;	
                    }
                }		
                
        $tbl3 = "
            <tr>
                <td style=\"text-align:center;border-left: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-right: 1px solid black;\"></td>

            </tr>
            <tr>
                <td style=\"text-align:center;border-bottom: 1px solid black;border-left: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;\"></td>
                <td style=\"text-align:center;border-left: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;\"></td>
            </tr>";

        $tbl4 ="";
            if($customer_tax_no != ''){
                $tbl4 = "
                    <tr>
                        <td style=\"text-align:left;\" colspan=\"5\">Ket : ".$salesinvoice['sales_invoice_remark']."</td>
                        <td style=\"text-align:right;\" ><div style=\"font-size:13.5px\">Total &nbsp;</div></td>
                        <td style=\"text-align:right;border-left: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;\" ><div style=\"font-size:13.5px\">".number_format($total_price, 2)." &nbsp;</div></td>
                    </tr>
                    <tr>
                        <td style=\"text-align:left;\" colspan=\"4\"></td>
                        <td style=\"text-align:right;\" colspan=\"2\"></td>
                        <td style=\"text-align:right;\" ></td>
                    </tr>
                    <tr>
                        <td style=\"text-align:left;\" colspan=\"5\" ></td>
                        <td style=\"text-align:right;\" ></td>
                        <td style=\"text-align:right;\" ></td>
                    </tr>
                                
                ";
            } else {
                $tbl4 = "
                    <tr>
                        <td style=\"text-align:left;\" colspan=\"7\">Ket : ".$salesinvoice['sales_invoice_remark']."
                        </td>

                        <td style=\"text-align:right;\" ><div style=\"font-size:13.5px\">Total &nbsp;</div></td>
                        <td style=\"text-align:right;border-left: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;\" ><div style=\"font-size:13.5px\">".number_format($total, 2)." &nbsp;</div></td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                            
                ";
            }

            $tbl5 = "";
                
            if($salesinvoice['section_id'] == 1){
                $tbl5 = "
                    <tr>
                        <td style=\"text-align:left;\" colspan=\"5\"><div style=\"font-size:13.5px\">".$preference_company['company_bank_name_nonppn1']."<br>A/C:".$preference_company['company_bank_account_no_nonppn1'].", A/N ".$preference_company['company_bank_account_name_nonppn']."</div>
                        </td>
                        <td style=\"text-align:center;\" width=\"5%\"></td>
                        <td style=\"text-align:center;\" width=\"15%\" colspan=\"2\">Approved</td>
                    </tr>
                    <tr>
                        <td style=\"text-align:left;\" colspan=\"5\"><div style=\"font-size:13.5px\">".$preference_company['company_bank_name_nonppn2']."<br>A/C:".$preference_company['company_bank_account_no_nonppn2'].", A/N ".$preference_company['company_bank_account_name_nonppn']."</div></td>
                        <td style=\"text-align:center;\" width=\"5%\" ></td>
                        <td style=\"text-align:center;\" width=\"15%\" colspan=\"2\"><br><br><br>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
                        
                    </tr>
                ";
            } else {
                $tbl5 = "
                    <tr>
                        <td style=\"text-align:left;\" colspan=\"5\"><div style=\"font-size:13.5px\">".$preference_company['company_bank_name_nonppn2']."<br>A/C:".$preference_company['company_bank_account_no_nonppn2'].", A/N ".$preference_company['company_bank_account_name_nonppn']."</div>
                        </td>
                        <td style=\"text-align:center;\" width=\"5%\"></td>
                        <td style=\"text-align:center;\" width=\"15%\" colspan=\"2\">Approved</td>
                    </tr>
                    <tr>
                        <td style=\"text-align:left;\" colspan=\"5\"></td>
                        <td style=\"text-align:center;\" width=\"5%\"></td>
                        <td style=\"text-align:center;\" width=\"15%\" colspan=\"2\"><br><br><br>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
                        
                    </tr>
                    
                            
                ";
            }

        $tbl6 = "</table>";

        $pdf::writeHTML($tbl1.$tbl2.$tbl3.$tbl4.$tbl5.$tbl6, true, false, false, false, '');

        // ob_clean();

        if (ob_get_contents()) ob_end_clean();
        // -----------------------------------------------------------------------------
        
        //Close and output PDF document
        $filename = 'Sales_Invoice_'.$salesinvoice['sales_invoice_no'].'.pdf';
        $pdf::Output($filename, 'I');

        //============================================================+
        // END OF FILE
        //============================================================+
    }

}
