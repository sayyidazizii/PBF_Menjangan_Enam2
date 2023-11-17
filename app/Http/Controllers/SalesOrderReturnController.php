<?php

namespace App\Http\Controllers;

use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\InvItemStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\SalesOrderReturn;
use App\Models\SalesDeliveryNote;
use App\Models\SalesDeliveryOrder;
use App\Models\SalesDeliveryOrderItem;
use App\Models\SalesOrder;
use App\Models\InvWarehouse;
use App\Models\SalesDeliveryNoteItem;
use App\Models\SalesDeliveryNoteItemStock;
use App\Models\SalesOrderReturnItem;
use App\Models\SalesOrderItem;
use App\Models\InvItemType;
use App\Models\CoreExpedition;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\CoreCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalesOrderReturnController extends Controller
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

        $salesorderreturn = SalesOrderReturn::where('data_state', 0)
            ->where('sales_order_return_date', '>=', $start_date)
            ->where('sales_order_return_date', '<=', $end_date)
            ->get();
        return  view('content/SalesOrder/ListSalesOrderReturn', compact('salesorderreturn', 'end_date', 'start_date'));
    }

    public function filterSalesOrderReturn(Request $request)
    {
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/sales-order-return');
    }

    public function resetFilterSalesOrderReturn()
    {
        Session::forget('start_date');
        Session::forget('end_date');

        return redirect('/sales-order-return');
    }

    // public function searchSalesDeliveryNote()
    // {
    //     Session::forget('purchaseorderitem');

    //     $salesdeliverynote= SalesDeliveryNote::where('data_state', 0)
    //     ->where('return_status', 0)
    //     ->get();

    //     return view('content/SalesOrder/SearchSalesDeliveryNote', compact('salesdeliverynote'));
    // }
    public function searchSalesInvoice()
    {
        Session::forget('purchaseorderitem');

        $salesInvoice = SalesInvoice::where('data_state', 0)
            ->where('return_status', 0)
            ->get();

        return view('content/SalesInvoice/SearchSalesInvoice', compact('salesInvoice'));
    }

    public function addSalesOrderReturn($sales_invoice_id)
    {

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
            ->where('data_state', 0)
            ->pluck('warehouse_name', 'warehouse_id');

        $salesInvoice = SalesInvoice::where('data_state', 0)
            ->where('sales_invoice_id', $sales_invoice_id)
            ->first();

        $salesdeliveryorder = SalesDeliveryOrder::where('data_state', 0)
            ->where('sales_order_id', $salesInvoice->sales_order_id)
            ->first();

        $salesInvoiceItem = SalesInvoiceItem::select('sales_invoice_item.*')
            // ->join('sales_order_item','sales_order_item.sales_order_id','sales_invoice_item.sales_order_id')
            ->where('sales_invoice_item.sales_invoice_id', $sales_invoice_id)
            ->where('sales_invoice_item.data_state', 0)
            ->get();
        //dd($salesInvoiceItem);
        $salesorder = SalesOrder::select('sales_order.*')
            ->where('data_state', 0)
            ->where('sales_order_id', $salesInvoice->sales_order_id)
            ->first();

        return view('content/SalesOrder/FormAddSalesOrderReturn', compact('warehouse', 'salesInvoice', 'sales_invoice_id', 'salesInvoiceItem', 'salesorder', 'salesdeliveryorder'));
    }


    public function getSalesOrderItemID($sales_delivery_note_item_id)
    {
        $salesorder = SalesDeliveryNoteItem::select('sales_order_item_id')
            ->where('sales_delivery_note_item_id', $sales_delivery_note_item_id)
            ->where('data_state', 0)
            ->first();

        return $salesorder['sales_order_item_id'];
    }

    public function getCustomerName($sales_order_id)
    {
        $salesdeliveryorder = SalesOrder::select('core_customer.customer_name')
            ->join('core_customer', 'core_customer.customer_id', 'sales_order.customer_id')
            ->where('sales_order.sales_order_id', $sales_order_id)
            ->where('sales_order.data_state', 0)
            ->first();

        if ($salesdeliveryorder == null) {
            return "-";
        }

        return $salesdeliveryorder['customer_name'];
    }


    public function getItemStockName($item_stock_id)
    {
        $invitemstock = InvItemStock::select('inv_item_stock.item_stock_id', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name, " - ", inv_item_stock.item_batch_number) AS item_name'))
            ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_stock.item_category_id')
            ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
            ->where('item_stock_id', $item_stock_id)
            ->where('inv_item_stock.data_state', '=', 0)
            ->first();

        if ($invitemstock == null) {
            return "-";
        }
        return $invitemstock['item_name'];
    }

    public function getSalesOrderNo($sales_order_id)
    {
        $salesorder = SalesOrder::select('sales_order_no')
            ->where('sales_order_id', $sales_order_id)
            ->where('data_state', 0)
            ->first();

        if ($salesorder == null) {
            return "-";
        }

        return $salesorder['sales_order_no'];
    }

    public function getSalesOrderDate($sales_order_id)
    {
        $salesorder = SalesOrder::select('sales_order_date')
            ->where('sales_order_id', $sales_order_id)
            ->where('data_state', 0)
            ->first();

        if ($salesorder == null) {
            return "-";
        }

        return date('d/m/Y', strtotime($salesorder['sales_order_date']));
    }


    public function getSalesInvoiceNo($sales_invoice_id)
    {
        $salesinvoice = SalesInvoice::select('sales_invoice_no')
            ->where('sales_invoice_id', $sales_invoice_id)
            ->where('data_state', 0)
            ->first();

        if ($salesinvoice == null) {
            return "-";
        }

        return $salesinvoice['sales_invoice_no'];
    }


    public function getSalesInvoiceDate($sales_invoice_id)
    {
        $salesinvoice = SalesInvoice::select('sales_invoice_date')
            ->where('sales_invoice_id', $sales_invoice_id)
            ->where('data_state', 0)
            ->first();

        if ($salesinvoice == null) {
            return "-";
        }

        return date('d/m/Y', strtotime($salesinvoice['sales_invoice_date']));
    }



    public function getSalesDeliveryOrderDate($sales_delivery_order_id)
    {
        $salesdeliveryorder = SalesDeliveryOrder::select('sales_delivery_order_date')
            ->where('sales_delivery_order_id', $sales_delivery_order_id)
            ->where('data_state', 0)
            ->first();

        if ($salesdeliveryorder == null) {
            return "-";
        }

        return date('d/m/Y', strtotime($salesdeliveryorder['sales_delivery_order_date']));
    }
    public function getItemBatchNumber($item_stock_id)
    {
        $item = InvItemStock::select('item_batch_number')
            ->where('item_stock_id', $item_stock_id)
            ->where('data_state', 0)
            ->first();

        return $item['item_batch_number'] ?? '';
    }


    public function getInvItemTypeName($item_type_id)
    {
        $item = InvItemType::select('inv_item_type.item_type_id', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name) AS item_name'))
            ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_type.item_category_id')
            ->where('item_type_id', $item_type_id)
            ->where('inv_item_type.data_state', 0)
            ->first();

        if ($item == null) {
            return "-";
        }

        return $item['item_name'];
    }

    public function getCustomerNameSalesOrderId($sales_order_id)
    {
        $unit = SalesOrder::select('core_customer.customer_name')
            ->join('core_customer', 'core_customer.customer_id', 'sales_order.customer_id')
            ->where('sales_order_id', $sales_order_id)
            ->where('sales_order.data_state', 0)
            ->first();

        if ($unit == null) {
            return "-";
        }

        return $unit['customer_name'];
    }
	
     public function getPoNum($sales_order_id)
    {
        $unit = SalesOrder::select('*')
            ->where('sales_order_id', $sales_order_id)
            ->where('sales_order.data_state', 0)
            ->first();

        if ($unit == null) {
            return "-";
        }

        return $unit['purchase_order_no'];
    }


    public function getWarehouseName($warehouse_id)
    {
        $warehouse = InvWarehouse::where('warehouse_id', $warehouse_id)
            ->where('data_state', 0)
            ->first();

        if ($warehouse == null) {
            return "-";
        }

        return $warehouse['warehouse_name'];
    }

    public function getWarehouseId($sales_order_return_id)
    {
        $warehouse = SalesOrderReturn::where('sales_order_return_id', $sales_order_return_id)
            ->where('data_state', 0)
            ->first();

        return $warehouse['warehouse_id'];
    }

    public function getSalesDeliveryNoteNo($sales_delivery_note_id)
    {
        $deliverynote = SalesDeliveryNote::where('sales_delivery_note_id', $sales_delivery_note_id)
            ->where('data_state', 0)
            ->first();

        if ($deliverynote == null) {
            return "-";
        }


        return $deliverynote['sales_delivery_note_no'];
    }

    public function getSalesDeliveryNoteDate($sales_delivery_note_id)
    {
        $deliverynote = SalesDeliveryNote::where('sales_delivery_note_id', $sales_delivery_note_id)
            ->where('data_state', 0)
            ->first();

        if ($deliverynote == null) {
            return "-";
        }

        return date('d/m/Y', strtotime($deliverynote['sales_delivery_note_date']));
    }
    public function getName($customer_id)
    {
        $addres = CoreCustomer::select('customer_name')
            ->where('core_customer.data_state', 0)
            ->where('core_customer.customer_id', $customer_id)
            ->first();

        return $addres['customer_name'];
    }

    public function getSalesOrderItem($sales_order_item_id)
    {
        $orderitem = SalesOrderItem::select('sales_order_item.*', 'sales_order.customer_id', 'sales_order.sales_order_no', 'sales_order.sales_order_date', DB::raw('CONCAT(inv_item_category.item_category_name, " - ", inv_item_type.item_type_name, " - ", inv_item_stock.item_batch_number) AS item_name'))
            ->where('sales_order_item_id', $sales_order_item_id)
            ->join('inv_item_stock', 'inv_item_stock.item_stock_id', 'sales_order_item.item_stock_id')
            ->join('inv_item_category', 'inv_item_category.item_category_id', 'inv_item_stock.item_category_id')
            ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item_stock.item_type_id')
            // ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
            ->join('sales_order', 'sales_order.sales_order_id', 'sales_order_item.sales_order_id')
            ->first();

        // dd($orderitem);

        return $orderitem;
    }






    public function processAddSalesOrderReturn(Request $request)
    {
        $request->validate([
            'warehouse_id'                  => 'required',
            'sales_order_return_date'       => 'required',
        ]);

        $salesorderreturn = array(
            'warehouse_id'                  => $request->warehouse_id,
            'sales_order_id'                => $request->sales_order_id,
            'sales_delivery_order_id'       => $request->sales_delivery_order_id,
            'sales_invoice_id'              => $request->sales_invoice_id,
            'sales_delivery_note_id'        => $request->sales_delivery_note_id,
            'sales_order_return_date'       => $request->sales_order_return_date,
            'sales_order_return_remark'     => $request->sales_order_return_remark,
            'customer_id'                     => $request->customer_id,
            'no_retur_barang'                 => $request->no_retur_barang,
            'nota_retur_pajak'                 => $request->nota_retur_pajak,
            'barang_kembali'                 => $request->barang_kembali,
            // 'branch_id'                     => Auth::user()->branch_id,
            'created_id'                    => Auth::id(),
        );


        if (SalesOrderReturn::create($salesorderreturn)) {
            $sales_order_return_id = SalesOrderReturn::select('sales_order_return_id', 'sales_order_return_no')
                ->orderBy('created_at', 'DESC')
                ->first();

            // dd($sales_order_return_id);

            $salesinvoiceitem = SalesInvoiceItem::select('sales_invoice_item.*', 'sales_delivery_note_item_stock.item_stock_id', 'sales_delivery_note_item_stock.quantity')
                ->join('sales_delivery_note_item_stock', 'sales_delivery_note_item_stock.sales_delivery_note_item_id', 'sales_invoice_item.sales_delivery_note_item_id')
                ->where('sales_invoice_item.sales_order_id', $request->sales_order_id)
                ->get();
            // dd($salesinvoiceitem);
            $no = 1;

            $dataitem = $request->all();


            // //----------------------------------------------------------Journal Voucher-------------------------------------------------------------------//

            $preferencecompany             = PreferenceCompany::first();

            $transaction_module_code     = "SOR";

            $transactionmodule             = PreferenceTransactionModule::where('transaction_module_code', $transaction_module_code)
                ->first();


            $transaction_module_id         = $transactionmodule['transaction_module_id'];

            $journal_voucher_period     = date("Ym", strtotime($salesorderreturn['sales_order_return_date']));

            $data_journal = array(
                'branch_id'                        => 1,
                'journal_voucher_period'         => $journal_voucher_period,
                'journal_voucher_date'            => $salesorderreturn['sales_order_return_date'],
                'journal_voucher_title'            => 'Return Penjualan Barang ' . $sales_order_return_id['sales_order_return_no'],
                'journal_voucher_no'            => $sales_order_return_id['sales_order_return_no'],
                'journal_voucher_description'    => $salesorderreturn['sales_order_return_remark'],
                'transaction_module_id'            => $transaction_module_id,
                'transaction_module_code'        => $transaction_module_code,
                'transaction_journal_id'         => $sales_order_return_id['sales_order_return_id'],
                'transaction_journal_no'         => $sales_order_return_id['sales_order_return_no'],
                'created_id'                     => Auth::id(),
            );

            // dd($data_journal);
            AcctJournalVoucher::create($data_journal);


            //---------------------------------------------------------End Journal Voucher----------------------------------------------------------------//


            foreach ($salesinvoiceitem as $item) {
                $item = SalesOrderReturnItem::create([
                    'sales_delivery_order_id'        => $sales_order_return_id['sales_delivery_order_id'],
                    'sales_order_return_id'            => $sales_order_return_id['sales_order_return_id'],
                    'sales_invoice_id'                 => $dataitem['sales_invoice_id'],
                    'sales_delivery_note_id'         => $dataitem['sales_delivery_note_id'],
                    'sales_delivery_note_item_id'   => $dataitem['sales_delivery_note_item_id_' . $no],
                    'sales_order_id'                 => $dataitem['sales_order_id'],
                    'sales_order_item_id'             => $dataitem['sales_order_item_id_' . $no],
                    'item_id'                         => $dataitem['item_id_' . $no],
                    'item_stock_id'                 => $item['item_stock_id'],
                    'item_type_id'                     => $dataitem['item_type_id_' . $no],
                    'item_unit_id'                     => $dataitem['item_unit_id_' . $no],
                    'item_unit_price'                 => $dataitem['item_unit_price_' . $no],
                    'subtotal_price'                 => $dataitem['quantity_return_' . $no] * $dataitem['item_unit_price_' . $no],
                    'quantity'                        => $dataitem['quantity_' . $no],
                    'quantity_return'                => $dataitem['quantity_return_' . $no],
                    'created_id'                    => Auth::id(),
                ]);

                //dd($item);

                $salesdeliverynote = SalesInvoice::findOrFail($dataitem['sales_invoice_id']);
                $salesdeliverynote->return_status = 1;
                $salesdeliverynote->save();

                $itemstock = InvItemStock::findOrfail($item['item_stock_id']);
                //dd($itemstock);

                if ($dataitem['barang_kembali'] == 0) {
                    InvItemStock::create([
                        'goods_received_note_id'            =>   $itemstock['goods_received_note_id'],
                        'goods_received_note_item_id'       =>   $itemstock['goods_received_note_item_id'],
                        'item_stock_date'                   =>   \Carbon\Carbon::now(), # new \Datetime()
                        'item_stock_expired_date'           =>   $itemstock['item_stock_expired_date'],
                        'item_batch_number'                 =>   $itemstock['item_batch_number'],
                        'purchase_order_item_id'            =>   $itemstock['purchase_order_item_id'],
                        'warehouse_id'                      =>   9,
                        'item_category_id'                  =>   $itemstock['item_category_id'],
                        'item_type_id'                      =>   $dataitem['item_type_id_' . $no],
                        'item_id'                           =>   $itemstock['item_id'],
                        'item_unit_id'                      =>   $dataitem['item_unit_id_' . $no],
                        'item_total'                        =>   $itemstock['item_total'],
                        'item_unit_id_default'              =>   $itemstock['item_unit_id_default'],
                        'item_default_quantity_unit'        =>   $itemstock['item_default_quantity_unit'],
                        'quantity_unit'                     =>   $dataitem['quantity_return_' . $no],
                        'item_weight_default'               =>   $itemstock['item_weight_default'],
                        'item_weight_unit'                  =>   $itemstock['item_weight_unit'],
                        'package_id'                        =>   $itemstock['package_id'],
                        'package_total'                     =>   $itemstock['package_total'],
                        'package_unit_id'                   =>   $itemstock['package_unit_id'],
                        'package_price'                     =>   $itemstock['package_price'],
                        'data_state'                        =>   $itemstock['data_state'],
                        'created_id'                        =>   $itemstock['created_id'],
                        'created_at'                        =>   $itemstock['created_at'],
                        'buyers_acknowledgment_no'          =>   $dataitem['buyers_acknowledgment_no'],
                        'no_retur_barang'                   =>   $dataitem['no_retur_barang'],
                        'nota_retur_pajak'                  =>   $dataitem['nota_retur_pajak'],

                    ]);
                } else {
                    //dd($inv_stock_note);
                    InvItemStock::create([
                        'goods_received_note_id'            =>   $itemstock['goods_received_note_id'],
                        'goods_received_note_item_id'       =>   $itemstock['goods_received_note_item_id'],
                        'item_stock_date'                   =>   \Carbon\Carbon::now(), # new \Datetime()
                        'item_stock_expired_date'           =>   $itemstock['item_stock_expired_date'],
                        'item_batch_number'                 =>   $itemstock['item_batch_number'],
                        'purchase_order_item_id'            =>   $itemstock['purchase_order_item_id'],
                        'warehouse_id'                      =>   7,
                        'item_category_id'                  =>   $itemstock['item_category_id'],
                        'item_type_id'                      =>   $dataitem['item_type_id_' . $no],
                        'item_id'                           =>   $itemstock['item_id'],
                        'item_unit_id'                      =>   $dataitem['item_unit_id_' . $no],
                        'item_total'                        =>   $itemstock['item_total'],
                        'item_default_quantity_unit'        =>   $itemstock['item_default_quantity_unit'],
                        'quantity_unit'                     =>   $dataitem['quantity_return_' . $no],
                        'item_weight_default'               =>   $itemstock['item_weight_default'],
                        'item_weight_unit'                  =>   $itemstock['item_weight_unit'],
                        'package_id'                        =>   $itemstock['package_id'],
                        'package_total'                     =>   $itemstock['package_total'],
                        'package_unit_id'                   =>   $itemstock['package_unit_id'],
                        'package_price'                     =>   $itemstock['package_price'],
                        'data_state'                        =>   $itemstock['data_state'],
                        'created_id'                        =>   $itemstock['created_id'],
                        'created_at'                        =>   $itemstock['created_at'],
                        'buyers_acknowledgment_no'          =>   $dataitem['buyers_acknowledgment_no'],
                        'no_retur_barang'                   =>   $dataitem['no_retur_barang'],
                        'nota_retur_pajak'                  =>   $dataitem['nota_retur_pajak'],
                    ]);
                }


                //----------------------------------------------------------Journal Voucher Item-------------------------------------------------------------------//


                $salesorderitem          = SalesOrderItem::where('sales_order_item_id', $item['sales_order_item_id_' . $no])
                    ->first();

                $salesorder              = SalesOrder::findOrFail($salesorderreturn['sales_order_id']);

                $journalvoucher = AcctJournalVoucher::where('created_id', Auth::id())
                    ->orderBy('journal_voucher_id', 'DESC')
                    ->first();


                $journal_voucher_id     = $journalvoucher['journal_voucher_id'];


                //------account_id Return Penjualan BKP-----//
                $preference_company = PreferenceCompany::first();

                $account = AcctAccount::where('account_id', 366)
                    ->where('data_state', 0)
                    ->first();

                $total_amount = $item['item_unit_price'] * $item['quantity_return'];

                $account_id_default_status         = $account['account_default_status'];

                $data_debit1 = array(
                    'journal_voucher_id'            => $journal_voucher_id,
                    'account_id'                    => 366,
                    'journal_voucher_description'    => $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'        => ABS($total_amount),
                    'journal_voucher_debit_amount'    => ABS($total_amount),
                    'account_id_default_status'        => $account_id_default_status,
                    'account_id_status'                => 1,
                );

                // dd($data_debit1);

                AcctJournalVoucherItem::create($data_debit1);

                //------account_id PPN Keluar------//
                $account         = AcctAccount::where('account_id',238)
                    ->where('data_state', 0)
                    ->first();

                // $total_amount = $item['item_unit_price'] * $item['quantity_return'];

                $ppn_out_amount = $salesorder['ppn_out_amount'];
                // dd($account_id_default_status);

                $data_debit2 = array(
                    'journal_voucher_id'            => $journal_voucher_id,
                    'account_id'                    => 238,
                    'journal_voucher_description'    => $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'        => ABS($ppn_out_amount),
                    'journal_voucher_debit_amount'    => ABS($ppn_out_amount),
                    'account_id_default_status'        => $account_id_default_status,
                    'account_id_status'                => 1,
                );

                // dd($data_debit2);

                AcctJournalVoucherItem::create($data_debit2);

                //------account_id Piutang Retur------//
                $account = AcctAccount::where('account_id', 48)
                    ->where('data_state', 0)
                    ->first();

                $ppn_out_amount = $salesorder['ppn_out_amount'];

                $receivable = $ppn_out_amount + $total_amount;


                $account_id_default_status         = $account['account_default_status'];

                $data_credit1 = array(
                    'journal_voucher_id'            => $journal_voucher_id,
                    'account_id'                    => 48,
                    'journal_voucher_description'    => $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'        => ABS($receivable),
                    'journal_voucher_credit_amount'    => ABS($receivable),
                    'account_id_default_status'        => $account_id_default_status,
                    'account_id_status'                => 0,
                );

                // dd($data_credit1);

                AcctJournalVoucherItem::create($data_credit1);
                //--------------------------------------------------------End Journal Voucher-----------------------------------------------------------------//


                //----------------------------------------------------------Journal Voucher Item2-------------------------------------------------------------------//


                // ------account_id Persediaan Barang Retur------//
                $account         = AcctAccount::where('account_id', 83)
                    ->where('data_state', 0)
                    ->first();

                $item_type_id = SalesOrderItem::select('item_type_id')
                    ->where('sales_order_item_id', $item['sales_order_item_id'])
                    ->first();

                $harga_beli = PurchaseOrderItem::select('purchase_order.total_amount')
                    ->join('purchase_order', 'purchase_order.purchase_order_id', '=', 'purchase_order_item.purchase_order_id')
                    ->where('purchase_order_item.item_type_id', $item_type_id['item_type_id'])
                    ->first();

                // $ppn_out_amount = $salesorder['ppn_out_amount'];
                // dd($account_id_default_status);

                $data_debit3 = array(
                    'journal_voucher_id'            => $journal_voucher_id,
                    'account_id'                    => 83,
                    'journal_voucher_description'    => $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'        => ABS($harga_beli['total_amount']),
                    'journal_voucher_debit_amount'    => ABS($harga_beli['total_amount']),
                    'account_id_default_status'        => $account_id_default_status,
                    'account_id_status'                => 1,
                );

                // dd($data_debit3);

                AcctJournalVoucherItem::create($data_debit3);

                //------account_id Beban Pokok Penjualan Barang ------//
                $account = AcctAccount::where('account_id', 390)
                    ->where('data_state', 0)
                    ->first();

                $harga_beli = PurchaseOrderItem::select('purchase_order.total_amount')
                    ->join('purchase_order', 'purchase_order.purchase_order_id', '=', 'purchase_order_item.purchase_order_id')
                    ->where('purchase_order_item.item_type_id', $item_type_id['item_type_id'])
                    ->first();


                $account_id_default_status         = $account['account_default_status'];

                $data_credit2 = array(
                    'journal_voucher_id'            => $journal_voucher_id,
                    'account_id'                    => 390,
                    'journal_voucher_description'    => $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'        => ABS($harga_beli['total_amount']),
                    'journal_voucher_credit_amount'    => ABS($harga_beli['total_amount']),
                    'account_id_default_status'        => $account_id_default_status,
                    'account_id_status'                => 0,
                );

                // dd($data_credit2);

                AcctJournalVoucherItem::create($data_credit2);
                //--------------------------------------------------------End Journal Voucher-----------------------------------------------------------------//



                $no++;
            }


            $msg = 'Tambah Return Penjualan Berhasil';
            return redirect('/sales-order-return')->with('msg', $msg);
        } else {
            $msg = 'Tambah Return Penjualan Gagal';
            return redirect('/sales-order-return')->with('msg', $msg);
        }
    }

    public function detailSalesOrderReturn($sales_order_return_id)
    {
        $salesorderreturnitem = SalesOrderReturnItem::where('sales_order_return_item.data_state', 0)
            ->where('sales_order_return_id', $sales_order_return_id)
            ->join('sales_order', 'sales_order.sales_order_id', '=', 'sales_order_return_item.sales_order_id')
            ->get();



        $salesorderreturn = SalesOrderReturn::findOrFail($sales_order_return_id);

        // dd( $salesorderreturn);

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
            ->where('data_state', 0)
            ->pluck('warehouse_name', 'warehouse_id');

        return view('content/SalesOrder/FormDetailSalesOrderReturn', compact('warehouse', 'salesorderreturn', 'salesorderreturnitem', 'sales_order_return_id'));
    }

    public function editSalesOrderReturn($sales_order_return_id)
    {
        $salesorderreturnitem = SalesOrderReturnItem::where('sales_order_return_item.data_state', 0)
            ->where('sales_order_return_id', $sales_order_return_id)
            ->join('sales_order', 'sales_order.sales_order_id', '=', 'sales_order_return_item.sales_order_id')
            ->get();



        $salesorderreturn = SalesOrderReturn::findOrFail($sales_order_return_id);

        // dd( $salesorderreturnitem);

        $warehouse = InvWarehouse::select('warehouse_id', 'warehouse_name')
            ->where('data_state', 0)
            ->pluck('warehouse_name', 'warehouse_id');

        return view('content/SalesOrder/FormEditSalesOrderReturn', compact('warehouse', 'salesorderreturn', 'salesorderreturnitem', 'sales_order_return_id'));
    }





    public function getIntemUnitCost($item_type_id)
    {
        $item_unit_cost = PurchaseOrderItem::select('purchase_order.total_amount')
            ->join('purchase_order', 'purchase_order.purchase_order_id', '=', 'purchase_order_item.purchase_order_id')
            ->where('purchase_order_item.item_type_id', $item_type_id)
            ->first();
        return $item_unit_cost['total_amount'];
    }





    public function processEditSalesOrderReturn(Request $request)
    {
        $salesorderreturn = SalesOrderReturn::findOrFail($request->sales_order_return_id);
        $salesorderreturn->barang_kembali = $request->barang_kembali;
        $salesorderreturn->warehouse_id = $request->warehouse_id;
        $salesorderreturn->save();
        //  dd($request->all());
        $dataitem = $request->all();

        $sales_order_return_id = SalesOrderReturn::select('sales_order_return_id', 'sales_order_return_no')
            ->where('sales_order_return_id', $request->sales_order_return_id)
            ->orderBy('created_at', 'DESC')
            ->first();



                        // //----------------------------------------------------------Journal Voucher-------------------------------------------------------------------//

                        $preferencecompany             = PreferenceCompany::first();

                        $transaction_module_code     = "SOR";

                        $transactionmodule             = PreferenceTransactionModule::where('transaction_module_code', $transaction_module_code)
                            ->first();


                        $transaction_module_id         = $transactionmodule['transaction_module_id'];

                        $journal_voucher_period     = date("Ym", strtotime($salesorderreturn['sales_order_return_date']));

                        $data_journal = array(
                            'branch_id'                        => 1,
                            'journal_voucher_period'         => $journal_voucher_period,
                            'journal_voucher_date'            => $salesorderreturn['sales_order_return_date'],
                            'journal_voucher_title'          => 'Return Penjualan Barang ' . $sales_order_return_id['sales_order_return_no'],
                            'journal_voucher_no'             => $sales_order_return_id['sales_order_return_no'],
                            'journal_voucher_description'    => $salesorderreturn['sales_order_return_remark'],
                            'transaction_module_id'          => $transaction_module_id,
                            'transaction_module_code'        => $transaction_module_code,
                            'transaction_journal_id'         => $sales_order_return_id['sales_order_return_id'],
                            'transaction_journal_no'         => $sales_order_return_id['sales_order_return_no'],
                            'created_id'                     => Auth::id(),
                        );

                        // dd($data_journal);
                        AcctJournalVoucher::create($data_journal);


                        //---------------------------------------------------------End Journal Voucher----------------------------------------------------------------//






                        $total_no = $request->total_no;
                        for ($i = 1; $i <= $total_no; $i++) {

                //----------------------------------------------------------Journal Voucher Item-------------------------------------------------------------------//


                $salesorderitem          = SalesOrderItem::where('sales_order_item_id', $dataitem['sales_order_item_id_'.$i])
                ->first();

                $salesorder              = SalesOrder::findOrFail($salesorderreturn['sales_order_id']);

                $journalvoucher = AcctJournalVoucher::where('created_id', Auth::id())
                ->orderBy('journal_voucher_id', 'DESC')
                ->first();


                $journal_voucher_id     = $journalvoucher['journal_voucher_id'];


                //------account_id Return Penjualan BKP-----//
                $preference_company = PreferenceCompany::first();

                $account = AcctAccount::where('account_id', $preference_company['account_sales_return_id'])
                ->where('data_state', 0)
                ->first();

                $total_amount = 0;

                $account_id_default_status         = $account['account_default_status'];


                //--------------------------------------------------------End Journal Voucher-----------------------------------------------------------------//


                //----------------------------------------------------------Journal Voucher Item2-------------------------------------------------------------------//


                // ------account_id Persediaan Barang dagang------//
                $account         = AcctAccount::where('account_id', $preferencecompany['account_inventory_trade_id'])
                ->where('data_state', 0)
                ->first();

                //  $item_type_id = SalesOrderItem::select('item_type_id')
                //  ->where('sales_order_item_id', $dataitem['sales_order_item_id'])
                //  ->first(); 

                //  $harga_beli = PurchaseOrderItem::select('purchase_order.total_amount')
                //  ->join('purchase_order', 'purchase_order.purchase_order_id', '=', 'purchase_order_item.purchase_order_id')
                //  ->where('purchase_order_item.item_type_id', $item_type_id['item_type_id'])
                //  ->first();

                // $ppn_out_amount = $salesorder['ppn_out_amount'];
                // dd($account_id_default_status);

                $data_debit3 = array(
                'journal_voucher_id'            => $journal_voucher_id,
                'account_id'                    => $preferencecompany['account_inventory_after_trade_id'],
                'journal_voucher_description'    => $data_journal['journal_voucher_description'],
                'journal_voucher_amount'        => ABS($dataitem['harga_beli_'.$i]),
                'journal_voucher_debit_amount'    => ABS($dataitem['harga_beli_'.$i]),
                'account_id_default_status'        => $account_id_default_status,
                'account_id_status'                => 1,
                );

                // dd($data_debit3);

                AcctJournalVoucherItem::create($data_debit3);

                //------account_id Beban Pokok Penjualan Barang Anggota BKP------//
                $account = AcctAccount::where('account_id', $preference_company['account_hpp_id'])
                ->where('data_state', 0)
                ->first();

                //  $harga_beli = PurchaseOrderItem::select('purchase_order.total_amount')
                //  ->join('purchase_order', 'purchase_order.purchase_order_id', '=', 'purchase_order_item.purchase_order_id')
                //  ->where('purchase_order_item.item_type_id', $item_type_id['item_type_id'])
                //  ->first();


                $account_id_default_status         = $account['account_default_status'];

                $data_credit2 = array(
                'journal_voucher_id'            => $journal_voucher_id,
                'account_id'                    => $preferencecompany['account_inventory_trade_id'],
                'journal_voucher_description'    => $data_journal['journal_voucher_description'],
                'journal_voucher_amount'        => ABS($dataitem['harga_beli_'.$i]),
                'journal_voucher_credit_amount'    => ABS($dataitem['harga_beli_'.$i]),
                'account_id_default_status'        => $account_id_default_status,
                'account_id_status'                => 0,
                );

                // dd($data_credit2);

                AcctJournalVoucherItem::create($data_credit2);
                //--------------------------------------------------------End Journal Voucher-----------------------------------------------------------------//




            $itemstock               = InvItemStock::findOrFail($dataitem['item_stock_id_'.$i]);
            $itemstock->warehouse_id = $dataitem['warehouse_id'];
            $itemstock->save();


           

        }

        if ($salesorderreturn) {
            $msg = 'Edit Return Penjualan Berhasil';
            return redirect('/sales-order-return')->with('msg', $msg);
        } else {
            $msg = 'Edit Return Penjualan Gagal';
            return redirect('/sales-order-return')->with('msg', $msg);
        }
    }

    public function export()
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
        $preference_company         = PreferenceCompany::first();
        $salesorderreturn = SalesOrderReturn::select('sales_order_return.*', 'sales_order_return_item.*')
            ->join('sales_order_return_item', 'sales_order_return_item.sales_order_return_id', 'sales_order_return_item.sales_order_return_id')
            ->where('sales_order_return.data_state', '=', 0)
            ->where('sales_order_return.sales_order_return_date', '>=', $start_date)
            ->where('sales_order_return.sales_order_return_date', '<=', $end_date)
            ->get();
        // Session::forget('salesdeliveryordernoteelements');
        //dd($salesorderreturn);


        $spreadsheet = new Spreadsheet();

        if (count($salesorderreturn) >= 0) {
            $spreadsheet->getProperties()->setCreator("TRADING SYSTEM")
                ->setLastModifiedBy("TRADING SYSTEM")
                ->setTitle("Sales Order Return")
                ->setSubject("")
                ->setDescription("Sales Order Return")
                ->setKeywords("Sales Order Return")
                ->setCategory("Sales Order Return");

            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Sales Order Return");
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(25);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(20);



            $spreadsheet->getActiveSheet()->mergeCells("B5:S5");
            $spreadsheet->getActiveSheet()->mergeCells("B6:S6");
            $spreadsheet->getActiveSheet()->mergeCells("B7:S7");
            $spreadsheet->getActiveSheet()->mergeCells("B8:S8");
            $spreadsheet->getActiveSheet()->mergeCells("B9:S9");
            $spreadsheet->getActiveSheet()->mergeCells("B10:S10");
            $spreadsheet->getActiveSheet()->mergeCells("B11:S11");
            $spreadsheet->getActiveSheet()->getStyle('B5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B11')->getFont()->setBold(true)->setSize(16);

            $spreadsheet->getActiveSheet()->getStyle('B12:S12')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B12:S12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);




            $sheet->setCellValue('B5', "PBF MENJANGAN ENAM ");
            $sheet->setCellValue('B6', "Jl.Puspowarno Raya No 55D RT 06 RW 09");
            $sheet->setCellValue('B7', "APA : " . Auth::user()->name . "");
            $sheet->setCellValue('B8', " SIKA: 449.2/16/DPM-PTSP/SIKA.16/III/2019 ");
            $sheet->setCellValue('B9', "");
            $sheet->setCellValue('B10', "");
            $sheet->setCellValue('B11', "Retur Penjualan Periode " . $start_date . " - " . $end_date);
            $sheet->setCellValue('B12', "No");
            $sheet->setCellValue('C12', "No. Retur");
            $sheet->setCellValue('D12', "TGL Retur");
            $sheet->setCellValue('E12', "No. Invoice");
            $sheet->setCellValue('F12', "TGL Invoice");
            $sheet->setCellValue('G12', "Customer");
            $sheet->setCellValue('H12', "No. Retur Barang");
            $sheet->setCellValue('I12', "Nota Retur Pajak");
            $sheet->setCellValue('J12', "Status Barang");
            $sheet->setCellValue('K12', "Gudang");
            $sheet->setCellValue('L12', "No. Sales Order");
            $sheet->setCellValue('M12', "TGL Sales Order");
            $sheet->setCellValue('N12', "Barang");
            $sheet->setCellValue('O12', "Batch Number");
            $sheet->setCellValue('P12', "Qty Kirim");
            $sheet->setCellValue('Q12', "Qty Retur");
            $sheet->setCellValue('R12', "Harga Jual");
            $sheet->setCellValue('S12', "Subtotal");

            $j  = 13;
            $no = 1;

            if (count($salesorderreturn) == 0) {
                $lastno = 2;
                $lastj = 13;
            } else {

                foreach ($salesorderreturn as $key => $val) {
                    $sheet = $spreadsheet->getActiveSheet(0);
                    $spreadsheet->getActiveSheet()->setTitle("Retur Penjualan");
                    $spreadsheet->getActiveSheet()->getStyle('B' . $j . ':S' . $j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $sheet->setCellValue('B' . $j, $no);
                    $sheet->setCellValue('C' . $j, $val['sales_order_return_no']);
                    $sheet->setCellValue('D' . $j, $val['sales_order_return_date']);
                    $sheet->setCellValue('E' . $j, $this->getSalesInvoiceNo($val['sales_invoice_id']));
                    $sheet->setCellValue('F' . $j, $this->getSalesInvoiceDate($val['sales_invoice_id']));
                    $sheet->setCellValue('G' . $j, $this->getName($val['customer_id']));
                    $sheet->setCellValue('H' . $j, $val['no_retur_barang']);
                    $sheet->setCellValue('I' . $j, $val['nota_retur_pajak']);
                    if ($val['barang_kembali'] == 1) {
                        $sheet->setCellValue('J' . $j, 'barang sudah kembali');
                    } else {
                        $sheet->setCellValue('J' . $j, 'barang belum kembali');
                    }
                    $sheet->setCellValue('K' . $j, $this->getWarehouseName($this->getWarehouseId($val['sales_order_return_id'])));
                    $sheet->setCellValue('L' . $j, $this->getSalesOrderNo($val['sales_order_id']));
                    $sheet->setCellValue('M' . $j, $this->getSalesOrderDate($val['sales_order_id']));
                    $sheet->setCellValue('N' . $j, $this->getInvItemTypeName($val['item_type_id']));
                    $sheet->setCellValue('O' . $j, $this->getItemBatchNumber($val['item_stock_id']));
                    $sheet->setCellValue('P' . $j, $val['quantity']);
                    $sheet->setCellValue('Q' . $j, $val['quantity_return']);
                    $sheet->setCellValue('R' . $j, $val['item_unit_price']);
                    $sheet->setCellValue('S' . $j, $val['subtotal_price']);

                    $no++;
                    $j++;
                    $lastno = $no;
                    $lastj = $j;
                }



                $sheet = $spreadsheet->getActiveSheet(0);
                $spreadsheet->getActiveSheet()->getStyle('B' . $lastj . ':S' . $lastj)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->setCellValue('H' . $lastj, 'Jumlah Total:');

                $sumrangedpp = 'Q' . $lastno - 1 . ':Q' . $j;
                $sheet->setCellValue('Q' . $lastj, '=SUM(' . $sumrangedpp . ')');

                $sumrangeppn = 'R' . $lastno - 1 . ':R' . $j;
                $sheet->setCellValue('R' . $lastj, '=SUM(' . $sumrangeppn . ')');

                $sumrangetotal = 'S' . $lastno - 1 . ':S' . $j;
                $sheet->setCellValue('S' . $lastj, '=SUM(' . $sumrangetotal . ')');


                $sheet->setCellValue('F' . $lastj + 1, 'Mengetahui');
                $sheet->setCellValue('K' . $lastj + 1, 'Dibuat Oleh');


                $spreadsheet->getActiveSheet()->getStyle('E' . $lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('G' . $lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('K' . $lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


                $sheet->setCellValue('E' . $lastj + 5, 'Apoteker');
                $sheet->setCellValue('G' . $lastj + 5, 'Administrasi Pajak');
                $sheet->setCellValue('K' . $lastj + 5, 'Dibuat Oleh');
            }




            ob_clean();
            $filename = 'Rekap Sales Order Return ' . date('d M Y') . '.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        } else {
            echo "Maaf data yang di eksport tidak ada !";
        }
    }
}
