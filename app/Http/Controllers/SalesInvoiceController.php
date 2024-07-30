<?php

namespace App\Http\Controllers;

use DateTime;
use stdClass;
use App\Models\User;
use App\Models\InvItem;
use App\Models\SalesOrder;
use App\Models\AcctAccount;
use App\Models\InvItemType;
use App\Models\InvItemUnit;
use App\Models\CoreCustomer;
use App\Models\InvItemStock;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use App\Models\SalesKwitansi;
use App\Models\SystemLogUser;
use App\Models\CoreExpedition;
use App\Models\SalesOrderItem;
use App\Models\InvItemCategory;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Models\SalesInvoiceItem;
use App\Models\PreferenceCompany;
use App\Models\PurchaseOrderItem;
use App\Models\SalesDeliveryNote;
use App\Models\SalesKwitansiItem;
use App\Models\AcctJournalVoucher;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\BuyersAcknowledgment;
use App\Models\InvGoodsReceivedNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SalesDeliveryNoteItem;
use PhpParser\Node\Expr\Cast\Object_;
use App\Models\AcctJournalVoucherItem;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use App\Models\BuyersAcknowledgmentItem;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\SalesDeliveryNoteItemStock;
use App\Models\PreferenceTransactionModule;

class SalesInvoiceController extends Controller
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

        // $customer_id = Session::get('customer_id');
        $customer_code = Session::get('customer_code');

        Session::forget('salesinvoiceitem');
        Session::forget('salesinvoiceelements');

        $salesinvoice = SalesInvoice::where('sales_invoice.data_state', '=', 0)
            ->join('core_customer','core_customer.customer_id','sales_invoice.customer_id')
            ->where('sales_invoice.sales_invoice_date', '>=', $start_date)
            ->where('sales_invoice.sales_invoice_date', '<=', $end_date);
        if ($customer_code || $customer_code != null || $customer_code != '') {
            $salesinvoice   = $salesinvoice->where('core_customer.customer_code', $customer_code);
        }
        $salesinvoice       = $salesinvoice->get();

        $customer = CoreCustomer::select('customer_code', 'customer_name')
            ->where('data_state', 0)
            ->pluck('customer_code', 'customer_code');

        return view('content/SalesInvoice/ListSalesInvoice', compact('salesinvoice', 'start_date', 'end_date', 'customer_code', 'customer'));
    }

    public function filterSalesInvoice(Request $request)
    {
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $customer_code  = $request->customer_code;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('customer_code', $customer_code);

        return redirect('/sales-invoice');
    }

    public function resetFilterSalesInvoice()
    {
        Session::forget('start_date');
        Session::forget('end_date');
        Session::forget('customer_code');

        return redirect('/sales-invoice');
    }

    public function search()
    {
        $buyersAcknowledgment = BuyersAcknowledgment::where('data_state', 0)
            ->where('sales_invoice_status', 0)
            ->get();
        //dd($BuyersAcknowledgment);

        return view('content/SalesInvoice/SearchBuyersAcknowledgment', compact('buyersAcknowledgment'));
    }

    public function getPpnOut($sales_delivery_note_id)
    {

        $sales_delivery_note = SalesDeliveryNote::select('ppn_out_amount')
            ->where('sales_delivery_note_id', $sales_delivery_note_id)
            ->where('data_state', 0)
            ->first();

        return $sales_delivery_note['ppn_out_amount'];
    }
    
    public function getPpnItem($sales_order_item_id)
    {

        $sales_delivery_note = SalesOrderItem::select('ppn_amount_item')
            ->where('sales_order_item_id', $sales_order_item_id)
            ->where('data_state', 0)
            ->first();

        return $sales_delivery_note['ppn_amount_item'];
    }

	public function getSalesOrderItem($sales_delivery_note_item_id)
    {

        $sales_delivery_note = SalesDeliveryNoteItem::select('*')
            ->where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
            ->where('data_state', 0)
            ->first();

        return $sales_delivery_note['sales_order_item_id'];
    }

    public function getDiscount($sales_order_item_id)
    {

        $salesorder = SalesOrderItem::select('discount_amount_item')
            ->where('sales_order_item_id', $sales_order_item_id)
            ->where('data_state', 0)
            ->first();

        return $salesorder['discount_amount_item'];
    }

    public function getDiscountB($sales_order_item_id)
    {

        $salesorder = SalesOrderItem::select('discount_amount_item_b')
            ->where('sales_order_item_id', $sales_order_item_id)
            ->where('data_state', 0)
            ->first();

        return $salesorder['discount_amount_item_b'];
    }

    public function getTotalAfterPpn($sales_order_id)
    {

        $salesorder = SalesOrder::select('subtotal_after_ppn_out')
            ->where('sales_order_id', $sales_order_id)
            ->where('data_state', 0)
            ->first();

        return $salesorder['subtotal_after_ppn_out'];
    }

    public function getNoBpb($buyers_acknowledgment_id)
    {

        $salesorder = BuyersAcknowledgment::select('buyers_acknowledgment_no')
            ->where('buyers_acknowledgment_id', $buyers_acknowledgment_id)
            ->where('data_state', 0)
            ->first();

        return $salesorder['buyers_acknowledgment_no'] ?? '';
    }
    public function getQtyBpb($sales_order_item_id)
    {

        $salesorder = BuyersAcknowledgmentItem::select('quantity_received')
            ->where('sales_order_item_id', $sales_order_item_id)
            ->where('data_state', 0)
            ->first();

        return $salesorder['quantity_received'] ?? '';
    }

    public function getPpn($sales_order_item_id)
    {

        $salesorder = SalesOrderItem::select('ppn_amount_item')
            ->where('sales_order_item_id', $sales_order_item_id)
            ->where('data_state', 0)
            ->first();

        return $salesorder['ppn_amount_item'] ?? '';
    }

    public function addSalesInvoice($buyers_acknowledgment_id)
    {
        $buyersAcknowledgment = BuyersAcknowledgment::select('buyers_acknowledgment.*', 'sales_order.*', 'sales_delivery_note.*')
            ->where('buyers_acknowledgment.buyers_acknowledgment_id', $buyers_acknowledgment_id)
            ->join('sales_delivery_note', 'sales_delivery_note.sales_delivery_note_id', 'buyers_acknowledgment.sales_delivery_note_id')
            ->join('sales_order', 'sales_order.sales_order_id', 'buyers_acknowledgment.sales_order_id')
            // ->join('inv_warehouse', 'inv_warehouse.warehouse_id', 'buyers_acknowledgment.warehouse_id')
            ->where('buyers_acknowledgment.data_state', 0)
            ->first();

        $coreexpedition = CoreExpedition::where('expedition_id', $buyersAcknowledgment['expedition_id'])
            ->first();

        $buyersAcknowledgmentitem = BuyersAcknowledgmentItem::select('*')
            ->where('buyers_acknowledgment_item.buyers_acknowledgment_id', $buyers_acknowledgment_id)
            ->where('data_state', 0)
            ->get();
    
        return view('content/SalesInvoice/FormAddSalesInvoice', compact('buyersAcknowledgment', 'buyersAcknowledgmentitem', 'buyers_acknowledgment_id', 'coreexpedition'));
    }

    public function editSalesInvoice($sales_invoice_id)
    {

        $invitemtype = InvItemType::where('inv_item_type.data_state','=',0)
            ->select('*')
            ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
            ->join('inv_item_stock', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
            ->pluck('inv_item_type.item_type_name','inv_item_type.item_type_id');

        $salesinvoice = SalesInvoice::findOrFail($sales_invoice_id);

        $salesorder = SalesOrder::where('sales_order_id', $salesinvoice['sales_order_id'])
            ->where('data_state', 0)
            ->first();

        $salesinvoiceitem = SalesInvoiceItem::select('sales_invoice_item.*')
            ->where('data_state', 0)
            ->where('sales_invoice_id', $sales_invoice_id)
            ->get();

        $salesdeliverynote = SalesDeliveryNote::select('sales_delivery_note.*', 'inv_warehouse.*')
            ->join('inv_warehouse', 'inv_warehouse.warehouse_id', 'sales_delivery_note.warehouse_id')
            ->where('sales_delivery_note.data_state', 0)
            ->where('sales_delivery_note.sales_delivery_note_id', $salesinvoice['sales_delivery_note_id'])
            ->first();

        return view('content/SalesInvoice/FormEditSalesInvoice', compact('invitemtype','salesinvoice', 'salesinvoiceitem', 'salesdeliverynote', 'salesorder', 'sales_invoice_id'));
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

        $salesdeliverynote = SalesDeliveryNote::select('sales_delivery_note.*', 'inv_warehouse.*')
            ->join('inv_warehouse', 'inv_warehouse.warehouse_id', 'sales_delivery_note.warehouse_id')
            ->where('sales_delivery_note.data_state', 0)
            ->where('sales_delivery_note.sales_delivery_note_id', $salesinvoice['sales_delivery_note_id'])
            ->first();

        return view('content/SalesInvoice/FormDetailSalesInvoice', compact('salesinvoice', 'salesinvoiceitem', 'salesdeliverynote', 'salesorder', 'sales_invoice_id'));
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

        return view('content/SalesInvoice/FormVoidSalesInvoice', compact('salesinvoice', 'salesinvoiceitem', 'salesdeliverynote', 'salesorder', 'sales_invoice_id'));
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

        return view('content/SalesInvoice/FormClosedSalesInvoice', compact('salesinvoice', 'salesinvoiceitem', 'salesdeliverynote', 'salesorder', 'sales_invoice_id'));
    }

    public function  processClosedSalesInvoice(Request $request)
    {
        $salesinvoice = SalesInvoice::findOrFail($request->sales_invoice_id);
        $salesinvoice->sales_invoice_status = 1;

        if ($salesinvoice->save()) {
            $msg = 'Closing Sales Invoice Berhasil';
            return redirect('/sales-invoice')->with('msg', $msg);
        } else {
            $msg = 'Closing Sales Invoice Gagal';
            return redirect('/sales-invoice/closed/' . $request->sales_invoice_id)->with('msg', $msg);
        }
    }

    public function processAddSalesInvoice(Request $request)
    {

        $buyersAcknowledgmentitem = BuyersAcknowledgmentItem::select('*')
            ->where('buyers_acknowledgment_item.buyers_acknowledgment_id', $request->buyers_acknowledgment_id)
            ->where('data_state', 0)
            ->get();


        $salesinvoice = array(
            'sales_invoice_date'            => $request->sales_invoice_date,
            'customer_id'                   => $request->customer_id,
            'buyers_acknowledgment_id'      => $request->buyers_acknowledgment_id,
            'subtotal_item'                 => $request->total_item,
            'subtotal_after_discount'       => $request->subtotal_after_discount,
            'subtotal_amount'               => $request->total_amount,
            'total_amount'                  => $request->total_amount,
            'owing_amount'                  => $request->total_amount,
            'sales_delivery_note_id'        => $request->sales_delivery_note_id,
            'sales_order_id'                => $request->sales_order_id,
            'sales_invoice_remark'          => $request->sales_invoice_remark,
            'sales_invoice_date'            => date('Y-m-d'),
            'sales_invoice_due_date'        => $request->sales_invoice_due_date,
            'warehouse_id'                  => $request->warehouse_id,
            'faktur_tax_no'                 => $request->faktur_tax_no,
            'tax_amount'                    => $request->tax_amount,
            'buyers_acknowledgment_no'      => $request->buyers_acknowledgment_no,
            'branch_id'                     => Auth::user()->branch_id,
            'created_id'                    => Auth::id(),
        );

        if (SalesInvoice::create($salesinvoice)) {

            $sales_invoice_id = SalesInvoice::select('*')
                ->orderBy('created_at', 'DESC')
                ->first();

            $dataItem = $request->all();
            // $no = 1;
            $total_no = $request->total_no;
            for ($i = 1; $i <= $total_no; $i++) {
                $data = array(
                    'sales_invoice_id'              => $sales_invoice_id['sales_invoice_id'],
                    'sales_order_id'                => $request['sales_order_id_' . $i],
                    'sales_order_item_id'           => $request['sales_order_item_id_' . $i],
                    'sales_delivery_note_id'        => $request->sales_delivery_note_id,
                    'sales_delivery_note_item_id'   => $request['sales_delivery_note_item_id_' . $i],
                    'item_type_id'                  => $request['item_type_id_' . $i],
                    'item_unit_id'                  => $request['item_unit_id_' . $i],
                    'quantity'                      => $request['quantity_' . $i],
                    'item_unit_price'               => $request['item_unit_price_' . $i],
                    'discount_A'                    => $request['discount_A_' . $i],
                    'discount_B'                    => $request['discount_B_' . $i],
                    'subtotal_price_A'              => $request['subtotal_price_A_' . $i],
                    'subtotal_price_B'              => $request['subtotal_price_B_' . $i],
                    'item_stock_id'                 => $request['item_stock_id_' . $i],
                    'created_id'                    => Auth::id(),
                );
                //dd($data);
                SalesInvoiceItem::create($data);

            }
            
            DB::table('buyers_acknowledgment')
		        ->where('buyers_acknowledgment_id',$request->buyers_acknowledgment_id)
                ->update(['sales_invoice_status' => 1]);
            

                //Update Discount Sumary
                $diskonA = DB::table('sales_order_item')
		        ->where('sales_order_id', $request->sales_order_id)
                ->sum('discount_amount_item');
        
                $diskonB = DB::table('sales_order_item')
		        ->where('sales_order_id', $request->sales_order_id)
                ->sum('discount_amount_item_b');

                $totalDiscount = $diskonA + $diskonB;
                if($totalDiscount){
                    DB::table('sales_invoice as a')
		            ->where('sales_order_id', $request->sales_order_id)
                    ->update([ 
                        'a.total_discount_amount' => $totalDiscount, 
                        'a.owing_discount_amount' => $totalDiscount 
                    ]);
                }

            $msg = 'Tambah Sales Invoice Berhasil';
            return redirect('/sales-invoice')->with('msg', $msg);
        } else {
            $msg = 'Tambah Sales Invoice Gagal';
            return redirect('/sales-invoice/add/' . $request->sales_delivery_note_id)->with('msg', $msg);
        }
    }

    public function processEditSalesInvoice(Request $request)
    {
        $salesinvoice = SalesInvoice::findOrFail($request->sales_invoice_id);
        $salesinvoice->sales_invoice_due_date   = $request->sales_invoice_due_date;
        $salesinvoice->sales_invoice_remark     = $request->sales_invoice_remark;
        $salesinvoice->faktur_tax_no            = $request->faktur_tax_no;
        $salesinvoice->goods_received_note_no   = $request->goods_received_note_no;
        $salesinvoice->ttf_no                   = $request->ttf_no;
        $salesinvoice->subtotal_after_discount  = $request->total_amount;
        $salesinvoice->total_amount             = $request->subtotal_after_ppn_out;
        $salesinvoice->owing_amount             = $request->subtotal_after_ppn_out;
        $salesinvoice->subtotal_amount          = $request->subtotal_after_ppn_out;

        if ($salesinvoice->save()) {

            $total_no = $request->total_no;
            for ($i = 1; $i <= $total_no; $i++) {

                $salesinvoiceitem                   = SalesInvoiceItem::findOrFail($request['sales_invoice_item_id_'.$i]);
                $salesinvoiceitem->item_type_id     = $request['item_type_id_'.$i];
                $salesinvoiceitem->quantity         = $request['quantity_'.$i];
                $salesinvoiceitem->item_unit_price  = $request['item_unit_price_'.$i];
                $salesinvoiceitem->discount_A       = $request['discount_A_'.$i];
                $salesinvoiceitem->subtotal_price_A = $request['subtotal_price_A_'.$i];
                $salesinvoiceitem->discount_B       = $request['discount_B_'.$i];
                $salesinvoiceitem->subtotal_price_B = $request['bayar_'.$i];
                $salesinvoiceitem->save();
                
            }

            $msg = 'Edit Sales Invoice Berhasil';
            return redirect('/sales-invoice')->with('msg', $msg);
        } else {
            $msg = 'Edit Sales Invoice Gagal';
            return redirect('/sales-invoice/add/' . $request->sales_invoice_id)->with('msg', $msg);
        }
    }

    public function processVoidSalesInvoice(Request $request)
    {
        $fields = $request->validate([
            'sales_invoice_id'   => 'required',
        ]);

        print_r($fields['sales_invoice_id']);

        $salesinvoice = SalesInvoice::findOrFail($request->sales_invoice_id);
        $salesinvoice->voided_id     = Auth::id();
        $salesinvoice->voided_on     = date('Y-m-d');
        $salesinvoice->data_state    = 1;

        if ($salesinvoice->save()) {
            $salesinvoiceitem = SalesInvoiceItem::where('sales_invoice_id', $request->sales_invoice_id)->get();

            $salesdeliverynote = SalesDeliveryNote::findOrFail($salesinvoice['sales_delivery_note_id']);
            $salesdeliverynote->sales_invoice_status = 0;
            $salesdeliverynote->save();

            foreach ($salesinvoiceitem as $val) {
                $dataitem = SalesInvoiceItem::where('sales_invoice_item_id', $val['sales_invoice_item_id'])->first();
                $dataitem->data_state = 1;
                $dataitem->save();
            }

            $msg = 'Void Sales Invoice Berhasil';
            return redirect('/sales-invoice')->with('msg', $msg);
        } else {
            $msg = 'Void Sales Invoice Gagal';
            return redirect('/sales-invoice/void/' . $fields['sales_invoice_id'])->with('msg', $msg);
        }
    }

    public function getItemStock($sales_delivery_note_item_id)
    {
        $item = SalesDeliveryNoteItemStock::select('item_stock_id')
            ->where('data_state', 0)
            ->where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
            ->first();

        return $item['item_stock_id'];
    }

    public function getCustomerName($customer_id)
    {
        $customer = CoreCustomer::select('customer_code')
            ->where('data_state', 0)
            ->where('customer_id', $customer_id)
            ->first();

        return $customer['customer_code'] ?? '';
    }

    public function getCustomerNameSalesOrderId($sales_order_id)
    {
        $customer = SalesOrder::select('core_customer.customer_name')
            ->join('core_customer', 'core_customer.customer_id', 'sales_order.customer_id')
            ->where('sales_order.data_state', 0)
            ->where('sales_order.sales_order_id', $sales_order_id)
            ->first();

        if ($customer == null) {
            return "-";
        }

        return $customer['customer_name'];
    }

    public function getExpeditionName($expedition_id)
    {
        $expedition = CoreExpedition::select('expedition_name')
            ->where('data_state', 0)
            ->where('expedition_id', $expedition_id)
            ->first();

        return $expedition['expedition_name'];
    }

    public function getSalesDeliveryNoteItem($sales_delivery_note_item_id)
    {
        $salesdeliverynoteitem = SalesDeliveryNoteItem::where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
            ->first();

        return $salesdeliverynoteitem;
    }

    public function getItemCategoryName($item_category_id)
    {
        $item = InvItemCategory::select('item_category_name')
            ->where('data_state', 0)
            ->where('item_category_id', $item_category_id)
            ->first();

        return $item['item_category_name'];
    }

    public function getItemTypeName($item_type_id)
    {
        $item = InvItemType::select('item_type_name')
            ->where('data_state', 0)
            ->where('item_type_id', $item_type_id)
            ->first();

        return $item['item_type_name'];
    }

    public function getItemUnitName($item_unit_id)
    {
        $item = InvItemUnit::select('item_unit_name')
            ->where('data_state', 0)
            ->where('item_unit_id', $item_unit_id)
            ->first();

        if ($item == null) {
            return '-';
        }

        return $item['item_unit_name'];
    }

    public function getItemName($item_type_id)
    {
        $invitem = InvItemType::select('inv_item_type.item_type_id', DB::raw('CONCAT(inv_item_category.item_category_name, " ", inv_item_type.item_type_name) AS item_name'))
            ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
            ->where('item_type_id', $item_type_id)
            ->where('inv_item_type.data_state', '=', 0)
            ->first();

        //dd($invitem);
        if ($invitem == null) {
            return '-';
        }


        return $invitem['item_name'];
    }

    public function getBpbNo($sales_delivery_note_id)
    {
        $data = BuyersAcknowledgment::select('buyers_acknowledgment_no')
            ->where('data_state', 0)
            ->where('sales_delivery_note_id', $sales_delivery_note_id)
            ->first();

        return $data['buyers_acknowledgment_no'] ?? '';
    }

    public function getPoNo($sales_order_id)
    {
        $data = SalesOrder::select('purchase_order_no')
            ->where('data_state', 0)
            ->where('sales_order_id', $sales_order_id)
            ->first();

        return $data['purchase_order_no'] ?? '';
    }

    public function getBatchNum($item_stock_id)
    {
        $data = InvItemStock::select('item_batch_number')
            ->where('data_state', 0)
            ->where('item_stock_id', $item_stock_id)
            ->first();

        return $data['item_batch_number'] ?? '';
    }
    public function getExpDate($item_stock_id)
    {
        $data = InvItemStock::select('item_stock_expired_date')
            ->where('data_state', 0)
            ->where('item_stock_id', $item_stock_id)
            ->first();

        return $data['item_stock_expired_date'] ?? '';
    }

    public function getUnitCost($sales_order_item_id)
    {
        $data = BuyersAcknowledgmentItem::select('item_unit_cost')
            ->where('data_state', 0)
            ->where('sales_order_item_id', $sales_order_item_id)
            ->first();

        return $data['item_unit_cost'] ?? '';
    }

    public function getItemUnitPrice($sales_invoce_item_id)
    {
        $item = SalesInvoiceItem::select('item_unit_price')
            ->where('data_state', 0)
            ->where('sales_invoice_item_id', $sales_invoce_item_id)
            ->first();

        return $item['item_unit_price'] ?? '';
    }

    public function getDiscountType($item_type_id)
    {
        $data = PurchaseOrderItem::select('discount_percentage')
            ->where('data_state', 0)
            ->where('item_type_id', $item_type_id)
            ->first();

        return $data['discount_percentage'] ?? '';
    }

    public function getSOitemId($sales_delivery_note_item_id)
    {
        $data = SalesDeliveryNoteItem::select('sales_order_item_id')
            ->where('data_state', 0)
            ->where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
            ->first();

        return $data['sales_order_item_id'] ?? '';
    }

    public function getDiscountAmt($sales_delivery_note_item_id)
    {
        $sales_order_item_id = SalesDeliveryNoteItem::select('sales_order_item_id')
        ->where('data_state', 0)
        ->where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
        ->first();

        $data = SalesOrderItem::select('discount_percentage_item')
            ->where('sales_order_item_id', $sales_order_item_id['sales_order_item_id'])
            ->first();

        return $data['discount_percentage_item'] ?? '';
    }

    public function getDiscountAmtB($sales_delivery_note_item_id)
    {
        $sales_order_item_id = SalesDeliveryNoteItem::select('sales_order_item_id')
        ->where('data_state', 0)
        ->where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
        ->first();

        $data = SalesOrderItem::select('discount_percentage_item_b')
            ->where('sales_order_item_id', $sales_order_item_id['sales_order_item_id'])
            ->first();

        return $data['discount_percentage_item_b'] ?? '';
    }

    public function changeSalesDeliveryNote(Request $request)
    {
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

    public function set_log($user_id, $username, $id, $class, $pk, $remark)
    {

        date_default_timezone_set("Asia/Jakarta");

        $log = array(
            'user_id'        =>    $user_id,
            'username'        =>    $username,
            'id_previllage'    =>     $id,
            'class_name'    =>    $class,
            'pk'            =>    $pk,
            'remark'        =>     $remark,
            'log_stat'        =>    '1',
            'log_time'        =>    date("Y-m-d G:i:s")
        );
        return SystemLogUser::create($log);
    }

    public function processPrintingSalesInvoice($sales_invoice_id)
    {
        $preference_company         = PreferenceCompany::first();

        $salesinvoice                = SalesInvoice::select('sales_invoice.*', 'sales_order.*', 'sales_delivery_note.*', 'core_customer.*')
            ->join('sales_order', 'sales_order.sales_order_id', 'sales_invoice.sales_order_id')
            ->join('sales_delivery_note', 'sales_delivery_note.sales_delivery_note_id', 'sales_invoice.sales_delivery_note_id')
            ->join('core_customer', 'core_customer.customer_id', 'sales_invoice.customer_id')
            ->where('sales_invoice.sales_invoice_id', $sales_invoice_id)
            ->first();

        $salesinvoiceitem            = SalesInvoiceItem::select('sales_invoice_item.*', 'sales_order_item.*', 'sales_delivery_note_item_stock.*')
            ->join('sales_order_item', 'sales_order_item.sales_order_id', 'sales_invoice_item.sales_order_id')
            ->join('sales_delivery_note_item_stock', 'sales_delivery_note_item_stock.sales_delivery_note_item_id', 'sales_invoice_item.sales_delivery_note_item_id')
            ->where('sales_invoice_item.sales_invoice_id', $sales_invoice_id)
            ->groupBy('sales_invoice_item.sales_invoice_item_id')
            ->orderBy('sales_invoice_item.sales_invoice_item_id','ASC')
            ->get();
            //  return json_encode($salesinvoiceitem);
            // exit;
        //dd($salesinvoiceitem);

        $customer_tax_no             = $salesinvoice['customer_tax_no'];
        $ppn_percentage             = $preference_company['ppn'];

        $this->set_log(Auth::id(), Auth::user()->name, '2141', 'SalesInvoice.printSalesInvoice', Auth::user()->name, 'Print Sales Invoice');


        // create new PDF document
        $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 6, 6, 6); // put space of 10 on top

        // set image scale factor
        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
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
        if (trim($customer_tax_no) != '') {
            $tbla = "
                    <table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" >
                        <tr>
                            <td style=\"text-align:center;width:25%\">
                            <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                            <tr>
                                <td><div style=\"text-align: center; font-size:20px; font-weight: bold\">I N V O I C E</div></td>
                           
                            </tr>
                        </table>
                            </td>

                            <td style=\"text-align:center;width:75%\">
                            </td>
                            <td style=\"text-align:left; height:20%;\">" . $preference_company['company_name'] . "
                                <br>" . $preference_company['company_address'] . "<br>Telp./Fax :
                                " . $preference_company['company_home_phone1'] . "<br>N.P.W.P :
                                " . $preference_company['company_tax_number'] . "
                            </td>
                        </tr>
                    </table>
                    <br><br>
                ";
        } else {
            $tbla = "
                    <table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                        <tr>
                            <td style=\"text-align:center;width:25%\">
                            <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
                            <tr>
                                <td><div style=\"text-align: center; font-size:20px; font-weight: bold\">I N V O I C E</div></td>
                           
                            </tr>
                        </table>
                            </td>
                            <td style=\"text-align:right;width:100%\">
                            </td>
                            <td style=\"text-align:left;width:0%; height:0%;\">
                            </td>
                        </tr>
                    </table>
                    <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>

            <td>
               
            </td>

            <td>
            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td><div style=\"text-align: right; font-size:12px; font-weight: bold\">PBF MENJANGAN ENAM</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: right; font-size:10px\">Jl.Puspowarno Raya No 55D Bojong Salaman,Semarang Barat</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: right; font-size:10px\">APJ : " . Auth::user()->name . "</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: right; font-size:10px\">" . $preference_company['CDBO_no'] . "</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: right; font-size:10px\">" . $preference_company['distribution_no'] . "</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: right; font-size:10px\">SIPA: 449.2/16/DPM-PTSP/SIKA.16/11/2019</div></td>
            </tr>
        </table>
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
                <td style=\"text-align:left;width:10%\"><div style=\"text-align: left; font-size:11px\">Hal</div> </td>
                <td style=\"text-align:left;width:2%\"> : </td>
                <td style=\"text-align:left;width:15%\"><div style=\"text-align: left; font-size:11px\">Tagihan Obat</div></td>
                <td style=\"text-align:left;width:20%\"></td>
            </tr>
            <tr>
                <td style=\"text-align:left;width:10%\"><div style=\"text-align: left; font-size:11px\">No. INV</div></td>
                <td style=\"text-align:left;width:2%\"> : </td>
                <td style=\"text-align:left;width:45%\"><div style=\"text-align: left; font-size:11px\">" .  $salesinvoice['sales_invoice_no']  . "</div></td>
                <td style=\"text-align:left;width:5%\"></td>
                <td style=\"text-align:left;width:12%\"></td>
                <td style=\"text-align:left;width:2%\"> </td>
                <td style=\"text-align:left;width:20%\"><div style=\"font-size:13.5px\"></div></td>
            </tr>
            <br/>
            <tr>
                <td  style=\"text-align:left;width:50%\">
                <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: left; font-size:10px; \">Kepada Yth.</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px\">Bp./Ibu Kepala Cabang</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px;font-weight: bold\">" .  $salesinvoice['customer_name'] . "</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px;width:40%\">" . $salesinvoice['customer_address'] . "</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left; font-size:10px\"></div></td>
                </tr>
              
            </table>
                </td>
              
            </tr>

            <tr>
                <td  style=\"text-align:left;width:50%\">
                <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: left; font-size:12px; \">Bersama ini kami sampaikan dengan rincian sebagai berikut : </div></td>
                </tr>
                <tr>
                <td style=\"text-align:left;width:20%\"><div style=\"text-align: left; font-size:11px; \">No . PO</div></td>
                <td style=\"text-align:left;width:2%\">:</td>
                <td style=\"text-align:left;width:45%\"><div style=\"text-align: left; font-size:11px; \">" . $this->getPoNo($salesinvoice['sales_order_id']) . "</div></td>
                <td style=\"text-align:left;width:18%\"><div style=\"text-align: left; font-size:11px; \">No . Terima</div></td>
                <td style=\"text-align:left;width:2%\">:</td>
                <td style=\"text-align:left;width:23%\"><div style=\"font-size:11px\">" . $this->getBpbNo($salesinvoice['sales_delivery_note_id']) . "</div></td>
            </tr>
            </table>
                </td>
              
            </tr>
        </table>";

        $pdf::writeHTML($tbla . $tbl, true, false, false, false, '');


        $html2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
        <tr style=\"text-align: center;\">
            <td width=\"5%\" ><div style=\"text-align: center;\">No</div></td>
            <td width=\"15%\" ><div style=\"text-align: center;\">Nama Item</div></td>
            <td width=\"8%\" ><div style=\"text-align: center;\">Satuan</div></td>
            <td width=\"10%\" ><div style=\"text-align: center;\">Batch</div></td>
            <td width=\"8%\" ><div style=\"text-align: center;\">Qty</div></td>
            <td width=\"10%\" ><div style=\"text-align: center;\">Harga</div></td>
            <td width=\"12%\" ><div style=\"text-align: center;\">Total</div></td>
            <td width=\"10%\" ><div style=\"text-align: center;\">Diskon 1</div></td>
            <td width=\"10%\" ><div style=\"text-align: center;\">Diskon 2</div></td>
            <td width=\"12%\" ><div style=\"text-align: center;\">Total Bayar</div></td>
        </tr>";
        $no = 1;
        $tbl2 = "";
        $qtyTotal = 0;
        $total_price = 0;
        $dpp = 0;
        $ppn = 0;
        $totalBayar = 0;
        foreach ($salesinvoiceitem as $key => $val) {   
            $qtyTotal = $this->getQtyBpb($val['sales_order_item_id']) * $this->getItemUnitPrice($val['sales_invoice_item_id']);
            $totalBayar = $val['subtotal_price_A'] - $val['discount_B'];
            if ($val['quantity'] != 0) {
                $cur = 'IDR';
                $rate = 1;
                $html2 .= "<tr>
                    <td style=\"text-align: center;\">" . $no . "</td>
                    <td>" . $this->getItemTypeName($val['item_type_id']) . "</td>
                    <td style=\"text-align: center;\">" . $this->getItemUnitName($val['item_unit_id']) . "</td>
                    <td style=\"text-align: center;\">" . $this->getBatchNum($val['item_stock_id']) . "</td>
                    <td style=\"text-align: center;\">" . $this->getQtyBpb($val['sales_order_item_id']) . "</td>
                    <td style=\"text-align: right;\">" . number_format($this->getItemUnitPrice($val['sales_invoice_item_id'] ), 2). "</td>
                    <td style=\"text-align: right;\">" . number_format($qtyTotal) . "</td>
                    <td style=\"text-align: right;\">" . number_format($val['discount_A']) . "</td>
                    <td style=\"text-align: right;\">" . number_format($val['discount_B']) . "</td>
                    <td style=\"text-align: right;\">" .number_format($totalBayar)."</td>
                </tr> 
                ";
                
                $total_price += ($val['subtotal_price_B']);
                
                $dpp += $totalBayar;
                $ppn += $this->getPpn($val['sales_order_item_id']);
                $no++;
            }
        }


            $html2  .= "
            <tr>
                <td colspan=\"8\" style=\"text-align: right;font-weight: bold\";>DPP</td>
                <td colspan=\"2\" style=\"text-align: right;\">" . number_format($dpp) . "</td>
                <td></td>
            </tr>
            <tr>
                <td colspan=\"8\" style=\"text-align: right;font-weight: bold\";>PPN</td>
                <td colspan=\"2\" style=\"text-align: right;\">" . number_format($ppn) . "</td>
                <td></td>
            </tr>
            <tr>
                <td colspan=\"8\" style=\"text-align: right;font-weight: bold\";>Jumlah Total</td>
                <td colspan=\"2\" style=\"text-align: right;\">" . number_format($dpp + $ppn) . "</td>
                <td></td>
            </tr>
            ";
            $html2 .= "</table>
                    <table>
                        <tr>
                            <td>Ket : " . $salesinvoice['sales_invoice_remark'] . "</td>
                        </tr>
                    </table>

        ";
            $path = '<img width="100"; height="100" src="resources/assets/img/ttd.png">';
            $date = $salesinvoice['sales_invoice_date'];
            setlocale(LC_TIME, 'id_ID.UTF-8'); // Atur locale ke bahasa Indonesia
            $formatted_date = strftime("%d %B %Y", strtotime($date));

            if ($salesinvoice['section_id'] == 1) {
                $html2 .= "
            <table style=\"text-align: center;font-weight: bold\" cellspacing=\"20\";>
                <tr>
                    <th>" . $preference_company['company_bank_name_nonppn1'] . "<br>A/C:" . $preference_company['company_bank_account_no_nonppn1'] . ", A/N " . $preference_company['company_bank_account_name_nonppn'] . "</th>
                    <th></th>
                    <th>Approved</th>
                </tr>
                <tr>
                    <th>" . $preference_company['company_bank_name_nonppn2'] . "<br>A/C:" . $preference_company['company_bank_account_no_nonppn2'] . ", A/N " . $preference_company['company_bank_account_name_nonppn'] . "</th>
                    <th></th>
                    <th>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</</th>
            </tr>
            </table>
            <table style=\"text-align: center;\" cellspacing=\"0\";>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </table>
            <table style=\"text-align: center;font-weight: bold\" cellspacing=\"0\";>
            <tr>
                <th></th>
                <th></th>
                <th>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</th>
            </tr>
        </table>
            ";
            } else {

                $html2 .= "
            <table style=\"text-align: center;\" cellspacing=\"10\";>
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
                        <td><div style=\"text-align: left; font-size:10px\">A/n &nbsp;&nbsp;&nbsp;: " . $preference_company['company_name'] . " </div></td>
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
            <table style=\"text-align: center;\" cellspacing=\"5\";>            
            <tr>
                <th>Semarang , ".$formatted_date." &nbsp;&nbsp;</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>".$path."</th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>Isti Ramadhani S.Farm.,Apt</th>
                <th></th>
                <th></th>
            </tr>
        </table>
            ";
            }


            $pdf::writeHTML($html2, true, false, true, false, '');

            // $pdf::writeHTML($tbl1.$tbl2.$tbl3.$tbl4.$tbl5.$tbl6, true, false, false, false, '');

            // ob_clean();

            if (ob_get_contents()) ob_end_clean();
            // -----------------------------------------------------------------------------

            //Close and output PDF document
            $filename = 'Sales_Invoice_' . $salesinvoice['sales_invoice_no'] . '.pdf';
            $pdf::Output($filename, 'I');

            //============================================================+
            // END OF FILE
            //============================================================+
        
    }

    //Report
    public function filterSalesInvoiceReport(Request $request)
    {
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $customer_code  = $request->customer_code;
        $checkbox       = $request->checkbox;

        //customer id
        $customer_id = CoreCustomer::where('customer_code', '=', $customer_code)->first();

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('customer_code', $customer_code);
        Session::put('checkbox', $checkbox);

        if(isset($checkbox) or $checkbox == 1){
            $salesinvoice = SalesInvoice::where('sales_invoice.data_state', '=', 0)
                ->join('core_customer','core_customer.customer_id','sales_invoice.customer_id')
                ->join('sales_invoice_item','sales_invoice_item.sales_invoice_id','sales_invoice.sales_invoice_id')
                ->join('sales_order','sales_order.sales_order_id','sales_invoice.sales_order_id')
                ->where('sales_invoice.data_state', '=', 0)
                ->where('sales_invoice.sales_invoice_date', '>=', $start_date)
                ->where('sales_invoice.sales_invoice_date', '<=', $end_date);

            if ($customer_code || $customer_code != null || $customer_code != '') {
                $salesinvoice = $salesinvoice->where('core_customer.customer_code', $customer_code);
            }

            $salesinvoice = $salesinvoice->get();

            $saleskwitansi = array(
                'customer_id'                   => $customer_id['customer_id'],
                'start_date'                    => $start_date,
                'end_date'                      => $end_date,
                'sales_kwitansi_date'           => \Carbon\Carbon::now(),
                'created_id'                    => Auth::id(),
            );

            if (SalesKwitansi::create($saleskwitansi)) {
                $saleskwitansi_id = SalesKwitansi::select('*')
                    ->orderBy('created_at', 'DESC')
                    ->first()->sales_kwitansi_id;

                foreach ($salesinvoice as $invoice) {
                    SalesKwitansiItem::create([
                        'sales_invoice_id'              => $invoice->sales_invoice_id,
                        'buyers_acknowledgment_id'      => $invoice->buyers_acknowledgment_id,
                        'sales_kwitansi_id'             => $saleskwitansi_id,
                        'checked'                       => 1,
                        'created_id'                    => Auth::id(),
                    ]);
                }
            }
        }
        return redirect('/sales-invoice-report');
    }
    //Report
    public function resetFilterSalesInvoiceReport()
    {
        Session::forget('start_date');
        Session::forget('end_date');
        Session::forget('customer_code');
        Session::forget('checkbox');

        return redirect('/sales-invoice-report');
    }
    //Report 
    public function ReportSalesInvoice()
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

        $customer_code = Session::get('customer_code');

        $checkbox = Session::get('checkbox');

        Session::forget('salesinvoiceitem');
        Session::forget('salesinvoiceelements');

        $salesinvoice = SalesInvoice::where('sales_invoice.data_state', '=', 0)
        ->join('core_customer','core_customer.customer_id','sales_invoice.customer_id')
        ->join('sales_invoice_item','sales_invoice_item.sales_invoice_id','sales_invoice.sales_invoice_id')
        ->join('sales_order','sales_order.sales_order_id','sales_invoice.sales_order_id')
        ->where('sales_invoice.data_state', '=', 0)
        ->where('sales_invoice.sales_invoice_date', '>=', $start_date)
        ->where('sales_invoice.sales_invoice_date', '<=', $end_date);
        if ($customer_code || $customer_code != null || $customer_code != '') {
            $salesinvoice   = $salesinvoice->where('core_customer.customer_code', $customer_code);
        }
        $salesinvoice       = $salesinvoice->get();

        $customer = CoreCustomer::select('customer_id', 'customer_name','customer_code')
            ->where('data_state', 0)
            ->pluck('customer_code', 'customer_code');

        return view('content/SalesInvoice/FormSalesInvoiceReport', compact('checkbox','salesinvoice', 'start_date', 'end_date', 'customer_code', 'customer'));
    }
    
    public function export(){
        if (!Session::get('start_date')) {
            $start_date = date('Y-m-d');
        } else {
            $start_date = Session::get('start_date');
        }

        if (!Session::get('end_date')) {
            $end_date = date('Y-m-d');
        } else {
            $end_date = Session::get('end_date');
        }

        $customer_code = Session::get('customer_code');

        Session::forget('salesinvoiceitem');
        Session::forget('salesinvoiceelements');

        $salesinvoice = SalesInvoice::select('sales_invoice.*', 'core_customer.customer_code','sales_invoice_item.*')
            ->join('sales_invoice_item', 'sales_invoice_item.sales_invoice_id', 'sales_invoice.sales_invoice_id')
            ->join('sales_order', 'sales_order.sales_order_id', 'sales_invoice.sales_order_id')
            ->join('core_customer', 'core_customer.customer_id', 'sales_invoice.customer_id')
            ->where('sales_invoice.data_state', '=', 0)
            ->where('sales_invoice.sales_invoice_date', '>=', $start_date)
            ->where('sales_invoice.sales_invoice_date', '<=', $end_date);

        if ($customer_code || $customer_code != null || $customer_code != '') {
            $salesinvoice = $salesinvoice->where('core_customer.customer_code', $customer_code);
        }

        $salesinvoice = $salesinvoice->get();

        $preference_company = PreferenceCompany::first();

        $spreadsheet = new Spreadsheet();

        if (count($salesinvoice) > 0) {
            $spreadsheet->getProperties()->setCreator("TRADING SYSTEM")
                ->setLastModifiedBy("TRADING SYSTEM")
                ->setTitle("SALES INVOICE REPORT")
                ->setSubject("") 
                ->setDescription("SALES INVOICE REPORT")
                ->setKeywords("SALES INVOICE REPORT")
                ->setCategory("SALES INVOICE REPORT");

            $groupedInvoices = $salesinvoice->groupBy('customer_code');

            foreach ($groupedInvoices as $customer_code => $invoices) {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle($customer_code);
                
                $sheet->mergeCells("B5:O5");
                $sheet->mergeCells("B6:O6");
                $sheet->mergeCells("B7:O7");
                $sheet->mergeCells("B8:O8");
                $sheet->mergeCells("B9:O9");
                $sheet->mergeCells("B10:O10");
                $sheet->mergeCells("B11:O11");
                $sheet->getStyle('B5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('B11')->getFont()->setBold(true)->setSize(16);

                $sheet->getStyle('B12:O12')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->getStyle('B12:O12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('B5', "PBF MENJANGAN ENAM ");	
                $sheet->setCellValue('B6', "Jl.Puspowarno Raya No 55D Bojong Salaman,Semarang Barat");
                $sheet->setCellValue('B7', "APA : ISTI RAHMADANI,S.Farm, Apt.");
                $sheet->setCellValue('B8', " SIKA: 449.2/16/DPM-PTSP/SIKA.16/III/2019 ");
                $sheet->setCellValue('B9', "");
                $sheet->setCellValue('B10', "REKAPITULASI PENJUALAN TANGGAL ".$start_date." - ".$end_date);
                $sheet->setCellValue('B11', "$customer_code");	
                $sheet->setCellValue('B12', "No");
                $sheet->setCellValue('C12', "TGL INV");
                $sheet->setCellValue('D12', "NOMOR FPP");
                $sheet->setCellValue('E12', "CABANG");
                $sheet->setCellValue('F12', "NO INVOICE");
                $sheet->setCellValue('G12', "NAMA OBAT");
                $sheet->setCellValue('H12', "QTY");
                $sheet->setCellValue('I12', "JUMLAH");
                $sheet->setCellValue('J12', "HPP");
                $sheet->setCellValue('K12', "DISKON");
                $sheet->setCellValue('L12', "DPP");
                $sheet->setCellValue('M12', "PPN");
                $sheet->setCellValue('N12', "TOTAL BAYAR");
                $sheet->setCellValue('O12', "%DISKON");
            
                $j  = 13;
                $no = 1;

                foreach ($invoices as $val) {
                    $salesorderitem = $this->getSalesOrderItem($val['sales_delivery_note_item_id']);
                    $itemunitcost   = $this->getUnitCost($salesorderitem ?? '');
                    $jumlahDiskon   = $val['discount_A'] + $val['discount_B'];
                    $ppn            = $this->getPpnItem($salesorderitem);
                    $dpp            = ($val['item_unit_price'] * $val['quantity']) - $jumlahDiskon;

                    $diskonPersen   = $this->getDiscountAmt($val['sales_delivery_note_item_id']) + $this->getDiscountAmtB($val['sales_delivery_note_item_id']) ;

                    $sheet->getStyle('B'.$j.':O'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $sheet->getStyle('O'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    $sheet->setCellValue('B'.$j, $no);
                    $sheet->setCellValue('C'.$j, $val['sales_invoice_date']);
                    $sheet->setCellValue('D'.$j, $val['faktur_tax_no']);
                    $sheet->setCellValue('E'.$j, $this->getCustomerName($val['customer_id']));
                    $sheet->setCellValue('F'.$j, $val['sales_invoice_no']);
                    $sheet->setCellValue('G'.$j, $this->getItemTypeName($val['item_type_id']));
                    $sheet->setCellValue('H'.$j, number_format($val['quantity'], 2, '.', ''));
                    $sheet->setCellValue('I'.$j, number_format($val['item_unit_price'] * $val['quantity'], 2, '.', ''));
                    $sheet->setCellValue('J'.$j, number_format($itemunitcost * $val['quantity'], 2, '.', ''));
                    $sheet->setCellValue('K'.$j, number_format($jumlahDiskon, 2, '.', ''));
                    $sheet->setCellValue('L'.$j, number_format($dpp, 2, '.', ''));
                    $sheet->setCellValue('M'.$j, number_format($ppn, 2, '.', ''));
                    $sheet->setCellValue('N'.$j, number_format($dpp + $ppn, 2, '.', ''));
                    $sheet->setCellValue('O'.$j, " $diskonPersen  %");

                    $no++;
                    $j++;
                }

                $lastj = $j;
                $lastno = $no;

                $sheet->getStyle('B'.$lastj.':N'.$lastj)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->setCellValue('G' . $lastj, 'Jumlah Total:');
                $sheet->setCellValue('H' . $lastj, '=SUM(H13:H'.($lastj-1).')');
                $sheet->setCellValue('I' . $lastj, '=SUM(I13:I'.($lastj-1).')');
                $sheet->setCellValue('J' . $lastj, '=SUM(J13:J'.($lastj-1).')');
                $sheet->setCellValue('K' . $lastj, '=SUM(K13:K'.($lastj-1).')');
                $sheet->setCellValue('L' . $lastj, '=SUM(L13:L'.($lastj-1).')');
                $sheet->setCellValue('M' . $lastj, '=SUM(M13:M'.($lastj-1).')');
                $sheet->setCellValue('N' . $lastj, '=SUM(N13:N'.($lastj-1).')');

                $sheet->setCellValue('F' . ($lastj + 1), 'Mengetahui');
                $sheet->setCellValue('K' . ($lastj + 1), 'Dibuat Oleh');

                $sheet->getStyle('E'.($lastj + 5))->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->getStyle('E'.($lastj + 5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G'.($lastj + 5))->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->getStyle('K'.($lastj + 5))->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $sheet->setCellValue('E' . ($lastj + 5), 'Apoteker');
                $sheet->setCellValue('G' . ($lastj + 5), 'Administrasi Pajak');
                $sheet->setCellValue('K' . ($lastj + 5), 'Dibuat Oleh');
            }

            $spreadsheet->removeSheetByIndex(0); // Remove default sheet created by PhpSpreadsheet

            ob_clean();
            $filename='SALES INVOICE REPORT '.date('d M Y').'.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        } else {
            echo "Maaf data yang di eksport tidak ada !";
        }
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
    public function printKwitansiPengantar(){
        $saleskwitansi = SalesKwitansi::select('*')
        ->where('data_state', '=', 0)
        ->orderBy('sales_kwitansi_id', 'desc') // Mengurutkan berdasarkan ID secara descending
        ->first();

        $saleskwitansiItem = SalesKwitansiItem::select('*')
        ->join('sales_invoice_item','sales_invoice_item.sales_invoice_id','sales_kwitansi_item.sales_invoice_id')
        ->where('sales_kwitansi_item.sales_kwitansi_id', '=', $saleskwitansi['sales_kwitansi_id'])
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
                <td  colspan=\"4\" style=\"text-align: left; font-size:10px;border-bottom-width:0.1px;\">Biaya Promosi Penjualan Obat Tanggal  ".$startFormatted. "  s.d.  ".$endFormatted." </td>
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
            <td colspan=\"4\" style=\"text-align: left; font-size:10px;\">: ". $this->getCustomerName($saleskwitansi['customer_id']) ."</td>
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
                <th>".$path."</th>
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

}
