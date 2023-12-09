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
use App\Models\SalesDeliveryNote;
use App\Models\SalesDeliveryNoteItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\PurchaseOrderItem;
use App\Models\SystemLogUser;
use App\Models\SalesDeliveryNoteItemStock;
use App\Models\PreferenceTransactionModule;
use App\Models\AcctJournalVoucher;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucherItem;
use App\Models\BuyersAcknowledgment;
use App\Models\BuyersAcknowledgmentItem;
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

        $customer_id = Session::get('customer_id');

        Session::forget('salesinvoiceitem');
        Session::forget('salesinvoiceelements');

        $salesinvoice = SalesInvoice::where('data_state', '=', 0)
            ->where('sales_invoice_date', '>=', $start_date)
            ->where('sales_invoice_date', '<=', $end_date);
        if ($customer_id || $customer_id != null || $customer_id != '') {
            $salesinvoice   = $salesinvoice->where('customer_id', $customer_id);
        }
        $salesinvoice       = $salesinvoice->get();

        $customer = CoreCustomer::select('customer_id', 'customer_name')
            ->where('data_state', 0)
            ->pluck('customer_name', 'customer_id');

        return view('content/SalesInvoice/ListSalesInvoice', compact('salesinvoice', 'start_date', 'end_date', 'customer_id', 'customer'));
    }

    public function filterSalesInvoice(Request $request)
    {
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $customer_id    = $request->customer_id;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('customer_id', $customer_id);

        return redirect('/sales-invoice');
    }

    public function resetFilterSalesInvoice()
    {
        Session::forget('start_date');
        Session::forget('end_date');
        Session::forget('customer_id');

        return redirect('/sales-invoice');
    }

    // public function search()
    // {
    //     $salesdeliverynote = SalesDeliveryNote::where('data_state', 0)
    //     ->where('sales_invoice_status', 0)
    //     ->get();


    //     return view('content/SalesInvoice/SearchSalesDeliveryNote',compact('salesdeliverynote'));
    // }


    public function search()
    {
        $buyersAcknowledgment = BuyersAcknowledgment::where('data_state', 0)
            ->where('sales_invoice_status', 0)
            ->get();
        //dd($BuyersAcknowledgment);

        return view('content/SalesInvoice/SearchBuyersAcknowledgment', compact('buyersAcknowledgment'));
    }

    // public function getP($sales_delivery_note_id)
    // {

    //     $sales_delivery_note = SalesDeliveryNote::select('ppn_out_amount')
    //         ->where('sales_delivery_note_id', $sales_delivery_note_id)
    //         ->where('data_state', 0)
    //         ->first();

    //     return $sales_delivery_note['ppn_out_amount'];
    // }


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

    public function addSalesInvoice($buyers_acknowledgment_id)
    {
        // $salesdeliverynote = SalesDeliveryNote::select('sales_delivery_note.*', 'sales_order.*','inv_warehouse.*')
        // ->where('sales_delivery_note_id', $sales_delivery_note_id)
        // ->join('sales_order', 'sales_order.sales_order_id', 'sales_delivery_note.sales_order_id')
        // ->join('inv_warehouse', 'inv_warehouse.warehouse_id', 'sales_delivery_note.warehouse_id')
        // // ->join('core_expedition', 'core_expedition.expedition_id', 'sales_delivery_note.expedition_id')
        // ->where('sales_delivery_note.data_state', 0)
        // ->first();

        $buyersAcknowledgment = BuyersAcknowledgment::select('buyers_acknowledgment.*', 'sales_order.*', 'sales_delivery_note.*')
            ->where('buyers_acknowledgment.buyers_acknowledgment_id', $buyers_acknowledgment_id)
            ->join('sales_delivery_note', 'sales_delivery_note.sales_delivery_note_id', 'buyers_acknowledgment.sales_delivery_note_id')
            ->join('sales_order', 'sales_order.sales_order_id', 'buyers_acknowledgment.sales_order_id')
            // ->join('inv_warehouse', 'inv_warehouse.warehouse_id', 'buyers_acknowledgment.warehouse_id')
            ->where('buyers_acknowledgment.data_state', 0)
            ->first();
	// dd($buyersAcknowledgment);

        $coreexpedition = CoreExpedition::where('expedition_id', $buyersAcknowledgment['expedition_id'])
            ->first();

        $buyersAcknowledgmentitem = BuyersAcknowledgmentItem::select('*')
            ->where('buyers_acknowledgment_item.buyers_acknowledgment_id', $buyers_acknowledgment_id)
            ->where('data_state', 0)
            ->get();


        // $salesdeliverynoteitem = SalesDeliveryNoteItem::select()
        // ->where('sales_delivery_note_id', $sales_delivery_note_id)
        // ->where('data_state', 0)
        // ->get();
        //dd($buyersAcknowledgment);

        return view('content/SalesInvoice/FormAddSalesInvoice', compact('buyersAcknowledgment', 'buyersAcknowledgmentitem', 'buyers_acknowledgment_id', 'coreexpedition'));
    }

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

        $salesdeliverynote = SalesDeliveryNote::select('sales_delivery_note.*', 'inv_warehouse.*')
            ->join('inv_warehouse', 'inv_warehouse.warehouse_id', 'sales_delivery_note.warehouse_id')
            ->where('sales_delivery_note.data_state', 0)
            ->where('sales_delivery_note.sales_delivery_note_id', $salesinvoice['sales_delivery_note_id'])
            ->first();

        return view('content/SalesInvoice/FormEditSalesInvoice', compact('salesinvoice', 'salesinvoiceitem', 'salesdeliverynote', 'salesorder', 'sales_invoice_id'));
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
        // $salesdeliverynoteitem = SalesDeliveryNoteItem::where('sales_delivery_note_id', $request->sales_delivery_note_id)
        // ->where('data_state', 0)
        // ->get();
        //dd($salesdeliverynoteitem);

        $buyersAcknowledgmentitem = BuyersAcknowledgmentItem::select('*')
            ->where('buyers_acknowledgment_item.buyers_acknowledgment_id', $request->buyers_acknowledgment_id)
            ->where('data_state', 0)
            ->get();

        //dd($buyersAcknowledgmentitem);

        $salesinvoice = array(
            'sales_invoice_date'            => $request->sales_invoice_date,
            'customer_id'                   => $request->customer_id,
            'buyers_acknowledgment_id'      => $request->buyers_acknowledgment_id,
            'subtotal_item'                 => $request->total_item,
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
        //dd($salesinvoice);

        if (SalesInvoice::create($salesinvoice)) {
            // $salesdeliverynote = SalesDeliveryNote::findOrFail($request->sales_delivery_note_id);
            // $salesdeliverynote->sales_invoice_status = 1;
            // $salesdeliverynote->save();

            // $buyersAcknowledgment = BuyersAcknowledgment::findOrFail($request->sales_delivery_note_id);
            // $buyersAcknowledgment->sales_invoice_status = 1;
            // $buyersAcknowledgment->save();

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


                //dd($data);
                // $itemstock = InvItemStock::findOrfail($dataItem['item_stock_id_'.$no]);
                // //dd($itemstock,$data);
                // //pengurangan stock
                // $itemstock->quantity_unit =  (int)$itemstock['quantity_unit'] -  (int)$itemstock['quantity_unit'];
                // $itemstock->save();

                // $no++;

            }
            // $buyersAcknowledgment = BuyersAcknowledgment::findOrFail($request->sales_delivery_note_id);
            // $buyersAcknowledgment->sales_invoice_status = 1;
            // $buyersAcknowledgment->save();
            DB::table('buyers_acknowledgment')
		        ->where('buyers_acknowledgment_id',$request->buyers_acknowledgment_id)
                ->update(['sales_invoice_status' => 1]);


            //----------------------------------------------------------Journal Voucher-------------------------------------------------------------------//

            // $preferencecompany             = PreferenceCompany::first();

            // $transaction_module_code     = "SI";

            // $transactionmodule             = PreferenceTransactionModule::where('transaction_module_code', $transaction_module_code)
            //     ->first();

            // $transaction_module_id         = $transactionmodule['transaction_module_id'];

            // $journal_voucher_period     = date("Ym", strtotime($sales_invoice_id['sales_invoice_date']));

            // $data_journal = array(
            //     'branch_id'                        => 1,
            //     'journal_voucher_period'         => $journal_voucher_period,
            //     'journal_voucher_date'            => $sales_invoice_id['sales_invoice_date'],
            //     'journal_voucher_title'            => 'Invoice Penjualan ' . $sales_invoice_id['sales_invoice_no'],
            //     'journal_voucher_no'            => $sales_invoice_id['sales_invoice_no'],
            //     'journal_voucher_description'    => $sales_invoice_id['sales_invoice_remark'],
            //     'transaction_module_id'            => $transaction_module_id,
            //     'transaction_module_code'        => $transaction_module_code,
            //     'transaction_journal_id'         => $sales_invoice_id['sales_invoice_id'],
            //     'transaction_journal_no'         => $sales_invoice_id['sales_invoice_no'],
            //     'created_id'                     => Auth::id(),
            // );

            // AcctJournalVoucher::create($data_journal);
            //---------------------------------------------------------End Journal Voucher----------------------------------------------------------------//


            //----------------------------------------------------------Journal Voucher Item-------------------------------------------------------------------//


            // $total_amount               = $request->total_amount;

            // $journalvoucher = AcctJournalVoucher::where('created_id', Auth::id())
            //     ->orderBy('journal_voucher_id', 'DESC')
            //     ->first();


            // $journal_voucher_id     = $journalvoucher['journal_voucher_id'];


            // //------account_id Persediaan Barang Dagang------//
            // $preference_company = PreferenceCompany::first();

            // $account = AcctAccount::where('account_id', $preference_company['account_sales_id'])
            //     ->where('data_state', 0)
            //     ->first();

            // $account_id_default_status         = $account['account_default_status'];


            // $data_debit1 = array(
            //     'journal_voucher_id'            => $journal_voucher_id,
            //     'account_id'                    => $account['account_id'],
            //     'journal_voucher_description'    => $data_journal['journal_voucher_description'],
            //     'journal_voucher_amount'        => ABS($total_amount),
            //     'journal_voucher_debit_amount'    => ABS($total_amount),
            //     'account_id_default_status'        => $account_id_default_status,
            //     'account_id_status'                => 1,
            // );

            // // dd($data_debit1);

            // AcctJournalVoucherItem::create($data_debit1);

            // //------account_id PPN Masukan------//
            // $account = AcctAccount::where('account_id', $preference_company['account_vat_out_id'])
            //     ->where('data_state', 0)
            //     ->first();

            // $ppn_out_amount = $request->tax_amount;
            // // +
            // $account_id_default_status         = $account['account_default_status'];



            // $data_debit2 = array(
            //     'journal_voucher_id'            => $journal_voucher_id,
            //     'account_id'                    => $account['account_id'],
            //     'journal_voucher_description'    => $data_journal['journal_voucher_description'],
            //     'journal_voucher_amount'        => ABS((int)$ppn_out_amount),
            //     'journal_voucher_debit_amount'    => ABS((int)$ppn_out_amount),
            //     'account_id_default_status'        => $account_id_default_status,
            //     'account_id_status'                => 1,
            // );

            // // dd($data_debit2);

            // AcctJournalVoucherItem::create($data_debit2);


            // $account         = AcctAccount::where('account_id', $preferencecompany['account_payable_id'])
            //     ->where('data_state', 0)
            //     ->first();

            // $subtotal_after_ppn_out = $request->total_amount;
            // // dd($request->all());
            // //  dd($subtotal_after_ppn_out);

            // $account_id_default_status         = $account['account_default_status'];

            // $data_credit = array(
            //     'journal_voucher_id'            => $journal_voucher_id,
            //     'account_id'                    => $preferencecompany['account_receivable_id'],
            //     'journal_voucher_description'    => $data_journal['journal_voucher_description'],
            //     'journal_voucher_amount'        => ABS((int)$subtotal_after_ppn_out),
            //     'journal_voucher_credit_amount'    => ABS((int)$subtotal_after_ppn_out),
            //     'account_id_default_status'        => $account_id_default_status,
            //     'account_id_status'                => 0,
            // );
            // // dd($data_credit);


            // AcctJournalVoucherItem::create($data_credit);


            // //--------------------------------------------------------End Journal Voucher-----------------------------------------------------------------//




            // return($itemstock);

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

        if ($salesinvoice->save()) {
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
        $customer = CoreCustomer::select('customer_name')
            ->where('data_state', 0)
            ->where('customer_id', $customer_id)
            ->first();

        return $customer['customer_name'] ?? '';
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
    public function getUnitCost($item_type_id)
    {
        $data = PurchaseOrderItem::select('item_unit_cost')
            ->where('data_state', 0)
            ->where('item_type_id', $item_type_id)
            ->first();

        return $data['item_unit_cost'] ?? '';
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
            ->groupBy('sales_invoice_item.sales_delivery_note_item_id')
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
                <td><div style=\"text-align: right; font-size:10px\">Jl.Puspowarno Raya No 55D RT 06 RW 09</div></td>
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
                <td style=\"text-align:left;width:8%\"><div style=\"text-align: left; font-size:11px\">No. INV</div></td>
   		<td style=\"text-align:left;width:2%\">:</td>
                <td style=\"text-align:left;width:20%\"><div style=\"font-size:11px\">" . $salesinvoice['sales_invoice_no'] . "</div></td>
                <td style=\"text-align:left;width:20%\"></td>
            </tr>
	    <tr>
                <td style=\"text-align:left;width:10%\"><div style=\"text-align: left; font-size:11px\">TGL . INV </div></td>
                <td style=\"text-align:left;width:2%\"> : </td>
                <td style=\"text-align:left;width:45%\"><div style=\"text-align: left; font-size:11px\">" . date('d M Y', strtotime($salesinvoice['sales_invoice_date'])) . "</div></td>
                <td style=\"text-align:left;width:5%\"></td>
                <td style=\"text-align:left;width:12%\"></td>
                <td style=\"text-align:left;width:2%\"> </td>
                <td style=\"text-align:left;width:20%\"><div style=\"font-size:13.5px\"></div></td>
            </tr>
            <tr>
                <td style=\"text-align:left;width:10%\"><div style=\"text-align: left; font-size:11px\">Jatuh Tempo</div></td>
                <td style=\"text-align:left;width:2%\"> : </td>
                <td style=\"text-align:left;width:45%\"><div style=\"text-align: left; font-size:11px\">" . date('d M Y', strtotime($salesinvoice['sales_invoice_due_date'])) . "</div></td>
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
                    <td><div style=\"text-align: left; font-size:10px;font-weight: bold\">APJ : " .  $salesinvoice['customer_name'] . "</div></td>
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
            <td width=\"10%\" ><div style=\"text-align: center;\">Nama Item</div></td>
            <td width=\"5%\" ><div style=\"text-align: center;\">Unit</div></td>
            <td width=\"10%\" ><div style=\"text-align: center;\">Batch</div></td>
            <td width=\"10%\" ><div style=\"text-align: center;\">Exp Date</div></td>
            <td width=\"5%\" ><div style=\"text-align: center;\">Qty</div></td>
            <td width=\"10%\" ><div style=\"text-align: center;\">Harga</div></td>
            <td width=\"10%\" ><div style=\"text-align: center;\">Pot A </div></td>
            <td width=\"12%\" ><div style=\"text-align: center;\">Total A</div></td>
            <td width=\"10%\" ><div style=\"text-align: center;\">Pot B </div></td>
            <td width=\"13%\" ><div style=\"text-align: center;\">Total Akhir Item</div></td>
        </tr>";
        $no = 1;
        $tbl2 = "";
        $total_price = 0;
        foreach ($salesinvoiceitem as $key => $val) {   
            if ($val['quantity'] != 0) {
                $cur = 'IDR';
                $rate = 1;
                $html2 .= "<tr>
                    <td style=\"text-align: center;\">" . $no . "</td>
                    <td>" . $this->getItemTypeName($val['item_type_id']) . "</td>
                    <td style=\"text-align: center;\">" . $this->getItemUnitName($val['item_unit_id']) . "</td>
                    <td style=\"text-align: center;\">" . $this->getBatchNum($val['item_stock_id']) . "</td>
                    <td style=\"text-align: center;\">" . $this->getExpDate($val['item_stock_id']) . "</td>
                    <td style=\"text-align: center;\">" . $this->getQtyBpb($val['sales_order_item_id']) . "</td>
                    <td style=\"text-align: right;\">" . number_format($val['item_unit_price'], 2) . "</td>
                    <td style=\"text-align: right;\">" . number_format($val['discount_A'], 2) . "</td>
                    <td style=\"text-align: right;\">" . number_format($val['subtotal_price_A'], 2) . "</td>
                    <td style=\"text-align: right;\">" . number_format($val['discount_B'], 2) . "</td>
                    <td style=\"text-align: right;\">" . number_format(($val['subtotal_price_A'] - $val['discount_B'], 2) . "</td>
                </tr> 
                ";
                
                $total_price += ($val['subtotal_price_B']);
                $dpp = $salesinvoice['subtotal'];
                if ($customer_tax_no != '') {
                    // $ppn = $dpp * 0.1;
                    if ($salesinvoice['customer_kawasan_berikat'] == 1) {
                        $ppn = $salesinvoice['ppn_amount'];
                        $total = $salesinvoice['total_amount'] + $ppn;
                    } else if ($salesinvoice['customer_kawasan_berikat'] == 0) {

                        $total = $salesinvoice['total_amount'];
                        $ppn = $total - $dpp;
                    }
                } else {
                    $ppn = 0;
                    $total = $salesinvoice['total_amount'];
                }
                $no++;
            }
        }

            $html2  .= "
            <tr>
                <td colspan=\"10\" style=\"text-align: right;font-weight: bold\";>Discount Nota</td>
                <td style=\"text-align: right;\">" . number_format($salesinvoice['discount_amount'], 2) . "</td>
                <td></td>
            </tr>
            <tr>
                <td colspan=\"10\" style=\"text-align: right;font-weight: bold\";>PPN</td>
                <td style=\"text-align: right;\">" . number_format($salesinvoice['ppn_out_amount'], 2) . "</td>
                <td></td>
            </tr>
            <tr>
                <td colspan=\"10\" style=\"text-align: right;font-weight: bold\";>Jumlah Total</td>
                <td style=\"text-align: right;\">" . number_format($total_price - $salesinvoice['discount_amount'] + $salesinvoice['ppn_out_amount'], 2) . "</td>
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
            $path = '<img width="80"; height="80" src="resources/assets/img/ttd.png">';
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
            <table style=\"text-align: center;\" cellspacing=\"20\";>
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
            <table style=\"text-align: center;\" cellspacing=\"10\";>            
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

    public function export(){
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

        $customer_id = Session::get('customer_id');

        Session::forget('salesinvoiceitem');
        Session::forget('salesinvoiceelements');

        $salesinvoice = SalesInvoice::select('*')
            ->join('sales_invoice_item','sales_invoice_item.sales_invoice_id','sales_invoice.sales_invoice_id')
            ->join('sales_order','sales_order.sales_order_id','sales_invoice.sales_order_id')
            ->where('sales_invoice.data_state', '=', 0)
            ->where('sales_invoice.sales_invoice_date', '>=', $start_date)
            ->where('sales_invoice.sales_invoice_date', '<=', $end_date);
        if ($customer_id || $customer_id != null || $customer_id != '') {
            $salesinvoice   = $salesinvoice->where('customer_id', $customer_id);
        }
        $salesinvoice       = $salesinvoice->get();

        $customer = CoreCustomer::select('customer_id', 'customer_name')
            ->where('data_state', 0)
            ->pluck('customer_name', 'customer_id');

        $preference_company         = PreferenceCompany::first();
    //   dd($salesinvoice);

       $spreadsheet = new Spreadsheet();

       if(count($salesinvoice)>=0){
           $spreadsheet->getProperties()->setCreator("TRADING SYSTEM")
               ->setLastModifiedBy("TRADING SYSTEM")
               ->setTitle("Sales Promotion")
               ->setSubject("") 
               ->setDescription("Sales Promotion")
               ->setKeywords("Sales Promotion")
               ->setCategory("Sales Promotion");

           $sheet = $spreadsheet->getActiveSheet(0);
           $spreadsheet->getActiveSheet()->setTitle("Sales Promotion");
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


           $spreadsheet->getActiveSheet()->mergeCells("B5:N5");
           $spreadsheet->getActiveSheet()->mergeCells("B6:N6");
           $spreadsheet->getActiveSheet()->mergeCells("B7:N7");
           $spreadsheet->getActiveSheet()->mergeCells("B8:N8");
           $spreadsheet->getActiveSheet()->mergeCells("B9:N9");
           $spreadsheet->getActiveSheet()->mergeCells("B10:N10");
           $spreadsheet->getActiveSheet()->mergeCells("B11:N11");
           $spreadsheet->getActiveSheet()->getStyle('B5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           $spreadsheet->getActiveSheet()->getStyle('B11')->getFont()->setBold(true)->setSize(16);

           $spreadsheet->getActiveSheet()->getStyle('B12:N12')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
           $spreadsheet->getActiveSheet()->getStyle('B12:N12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);




           $sheet->setCellValue('B5', "PBF MENJANGAN ENAM ");	
           $sheet->setCellValue('B6', "Jl.Puspowarno Raya No 55D RT 06 RW 09");
           $sheet->setCellValue('B7', "APA : ".Auth::user()->name."");
           $sheet->setCellValue('B8', " SIKA: 449.2/16/DPM-PTSP/SIKA.16/III/2019 ");
           $sheet->setCellValue('B9', "");
           $sheet->setCellValue('B10', "");
           $sheet->setCellValue('B11', "Sales Promotion Periode ".$start_date." - ".$end_date);	
           $sheet->setCellValue('B12', "No");
           $sheet->setCellValue('C12', "TGL INV");
           $sheet->setCellValue('D12', "NOMOR FPP");
           $sheet->setCellValue('E12', "CABANG");
           $sheet->setCellValue('F12', "NO INVOICE");
           $sheet->setCellValue('G12', "NAMA OBAT");
           $sheet->setCellValue('H12', "JUMLAH");
           $sheet->setCellValue('I12', "HPP");
           $sheet->setCellValue('J12', "DISKON");
           $sheet->setCellValue('K12', "DPP");
           $sheet->setCellValue('L12', "PPN");
           $sheet->setCellValue('M12', "TOTAL BAYAR");
           $sheet->setCellValue('N12', "%DISKON");
           
           $j  = 13;
           $no = 1;

           if(count($salesinvoice)==0){
            $lastno = 2;
            $lastj = 13;
           }else{
           
           foreach($salesinvoice as $key => $val){

            $itemunitcost = $this->getUnitCost($val['item_type_id'] ?? '') ;
            $diskon = $this->getDiscountType($val['item_type_id']);
            $diskonAmount =  (int)$diskon * 1/100; 
            $unitCost =  $diskonAmount * (int)$itemunitcost;
            $uniCostAmount = (int)$itemunitcost - $unitCost; 

            
            $sales_delivery_note_item_id = $this->getSOitemId($val['sales_delivery_note_item_id']);

            $diskonPersen =  (int)$this->getDiscountAmt($sales_delivery_note_item_id) + (int)$this->getDiscountAmtB($sales_delivery_note_item_id) ;

               $sheet = $spreadsheet->getActiveSheet(0);
               $spreadsheet->getActiveSheet()->setTitle("SURAT JALAN");
               $spreadsheet->getActiveSheet()->getStyle('B'.$j.':N'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
               $spreadsheet->getActiveSheet()->getStyle('N'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
             
               $sheet->setCellValue('B'.$j, $no);
               $sheet->setCellValue('C'.$j, $val['sales_invoice_date']);
               $sheet->setCellValue('D'.$j, $val['purchase_order_no']);
               $sheet->setCellValue('E'.$j, $this->getCustomerName($val['customer_id']));
               $sheet->setCellValue('F'.$j, $val['sales_invoice_no']);
               $sheet->setCellValue('G'.$j, $this->getItemTypeName($val['item_type_id']));
               $sheet->setCellValue('H'.$j, $val['quantity']);
               $sheet->setCellValue('I'.$j, $uniCostAmount);
               $sheet->setCellValue('J'.$j, $val['discount_A'] + $val['discount_B']);
               $sheet->setCellValue('K'.$j, $val['subtotal_price_B']);
               $sheet->setCellValue('L'.$j, $val['tax_amount']);
               $sheet->setCellValue('M'.$j, $val['total_amount']);
               $sheet->setCellValue('N'.$j, " $diskonPersen  %");
         
               $no++;
               $j++;
               $lastno = $no;
               $lastj = $j;
               
           }

            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->getStyle('B'.$lastj.':M'.$lastj)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setCellValue('G' . $lastj , 'Jumlah Total:');
            $sumrangeQty = 'H'. $lastno - 1 .':H'.$j;
            $sheet->setCellValue('H' . $lastj , '=SUM('.$sumrangeQty.')');

            $sumrangehpp = 'I'. $lastno - 1 .':I'.$j;
            $sheet->setCellValue('I' . $lastj , '=SUM('.$sumrangehpp.')');

            $sumrangediskon = 'J'. $lastno - 1 .':J'.$j;
            $sheet->setCellValue('J' . $lastj , '=SUM('.$sumrangediskon.')');

            $sumrangedpp = 'K'. $lastno - 1 .':K'.$j;
            $sheet->setCellValue('K' . $lastj , '=SUM('.$sumrangedpp.')');

            $sumrangeppn = 'L'. $lastno - 1 .':L'.$j;
            $sheet->setCellValue('L' . $lastj , '=SUM('.$sumrangeppn.')');

            $sumrangetotal = 'M'. $lastno - 1 .':M'.$j;
            $sheet->setCellValue('M' . $lastj , '=SUM('.$sumrangetotal.')');

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
           $filename='SALES INVOICE '.date('d M Y').'.xls';
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
