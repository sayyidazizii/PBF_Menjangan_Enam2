<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\InvWarehouse;
use App\Models\InvWarehouseLocation;
use App\Models\CoreSupplier;
use App\Models\CoreProvince;
use App\Models\CoreCity;
use App\Models\InvItemCategory;
use App\Models\InvItemStock;
use App\Models\InvItemUnit;
use App\Models\InvItemType;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Models\PreferenceCompany;
use App\Models\PurchaseOrderType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PurchaseOrderController extends Controller
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

        Session::put('editarraystate', 0);
        Session::forget('purchaseorderitem');
        Session::forget('purchaseorderelements');


        $purchaseorder = PurchaseOrder::where('data_state', '=', 0)
            ->where('purchase_order_date', '>=', $start_date)
            ->where('purchase_order_date', '<=', $end_date)
            ->get();

        return view('content/PurchaseOrder/ListPurchaseOrder', compact('purchaseorder', 'start_date', 'end_date'));
    }

    public function filterPurchaseOrder(Request $request)
    {

        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/purchase-order');
    }

    public function resetFilterPurchaseOrder()
    {
        Session::forget('start_date');
        Session::forget('end_date');

        return redirect('/purchase-order');
    }

    public function addPurchaseOrder()
    {
        $purchaseorderelements  = Session::get('purchaseorderelements');
        $purchaseorderitem      = Session::get('purchaseorderitem');

        // dd($purchaseorderelements);
        //dd($purchaseorderitem);

        $warehouse              = InvWarehouse::where('data_state', '=', 0)->pluck('warehouse_name', 'warehouse_id');
        $supplier               = CoreSupplier::where('data_state', '=', 0)->pluck('supplier_name', 'supplier_id');
        $itemcategory           = InvItemCategory::where('data_state', '=', 0)->pluck('item_category_name', 'item_category_id');
        $itemunit               = InvItemUnit::where('data_state', '=', 0)->pluck('item_unit_name', 'item_unit_id');
        $coreprovince           = CoreProvince::where('data_state', '=', 0)->pluck('province_name', 'province_id');
        $corecity               = CoreCity::where('data_state', '=', 0)->pluck('city_name', 'city_id');
        $location               = InvWarehouseLocation::where('inv_warehouse_location.data_state', 0)
            ->join('core_city', 'inv_warehouse_location.city_id', 'core_city.city_id')
            ->pluck('city_name', 'warehouse_location_id');
        $itemtype               = [];

        $ppnIn                    = PreferenceCompany::select('ppn_amount_in')->first();

        $null_warehouse_id = Session::get('warehouse_id');
        $null_supplier_id = Session::get('supplier_id');
        $null_item_category_id = Session::get('item_category_id');
        $null_item_type_id = Session::get('item_type_id');
        $null_item_unit_id = Session::get('item_unit_id');

        return view('content/PurchaseOrder/FormAddPurchaseOrder', compact('ppnIn', 'warehouse', 'supplier', 'itemcategory', 'itemtype', 'purchaseorderitem', 'itemunit', 'purchaseorderelements', 'coreprovince', 'corecity', 'location', 'null_warehouse_id', 'null_supplier_id', 'null_item_type_id', 'null_item_category_id', 'null_item_unit_id'));
    }

    public function editPurchaseOrder($purchase_order_id)
    {
        $editarraystate = Session::get('editarraystate');

        $purchaseorder = PurchaseOrder::where('data_state', 0)
            ->where('purchase_order_id', $purchase_order_id)
            ->first();


        if ($editarraystate == 0) {
            Session::forget('purchaseorderitem');
            $purchaseorderitem = PurchaseOrderItem::where('data_state', 0)
                ->where('purchase_order_id', $purchase_order_id)
                ->get();

            foreach ($purchaseorderitem as $key => $val) {

                $purchaseorderitem = array(
                    'purchase_order_item_id'    => $val['purchase_order_item_id'],
                    'item_category_id'            => $val['item_category_id'],
                    'item_type_id'                => $val['item_type_id'],
                    'item_unit_id'                => $val['item_unit_id'],
                    'quantity'                    => $val['quantity'],
                    'price'                        => $val['item_unit_cost'],
                    'total_price'                => $val['subtotal_amount'],
                );

                $lastpurchaseorderitem = Session::get('purchaseorderitem');
                if ($lastpurchaseorderitem !== null) {
                    array_push($lastpurchaseorderitem, $purchaseorderitem);
                    Session::put('purchaseorderitem', $lastpurchaseorderitem);
                } else {
                    $lastpurchaseorderitem = [];
                    array_push($lastpurchaseorderitem, $purchaseorderitem);
                    Session::push('purchaseorderitem', $purchaseorderitem);
                }
            }
        }

        $purchaseorderitem = Session::get('purchaseorderitem');

        $warehouse = InvWarehouse::where('data_state', '=', 0)->pluck('warehouse_name', 'warehouse_id');
        $supplier = CoreSupplier::where('data_state', '=', 0)->pluck('supplier_name', 'supplier_id');
        $itemcategory = InvItemCategory::where('data_state', '=', 0)->pluck('item_category_name', 'item_category_id');
        $itemunit = InvItemUnit::where('data_state', '=', 0)->pluck('item_unit_name', 'item_unit_id');
        $itemtype = InvItemType::where('data_state', 0)->where('item_category_id', $purchaseorder['item_category_id'])->pluck('item_type_name', 'item_type_id');

        return view('content/PurchaseOrder/FormEditPurchaseOrder', compact('warehouse', 'supplier', 'itemcategory', 'itemtype', 'purchaseorderitem', 'itemunit', 'purchaseorder', 'editarraystate'));
    }

    public function detailPurchaseOrder($purchase_order_id)
    {
        $purchaseorder = PurchaseOrder::where('data_state', 0)
            ->where('purchase_order_id', $purchase_order_id)
            ->first();

        $purchaseorderitem = PurchaseOrderItem::where('data_state', 0)
            ->where('purchase_order_id', $purchase_order_id)
            ->get();

        return view('content/PurchaseOrder/FormDetailPurchaseOrder', compact('purchaseorderitem', 'purchaseorder'));
    }


    public function cetakPurchaseOrder($purchase_order_id)
    {
        $purchaseorder = PurchaseOrder::select('*')
            ->join('core_supplier', 'core_supplier.supplier_id', 'purchase_order.supplier_id')
            ->where('purchase_order.data_state', 0)
            ->where('purchase_order.purchase_order_id', $purchase_order_id)
            ->first();

        $purchaseorderitem = PurchaseOrderItem::where('data_state', 0)
            ->where('purchase_order_id', $purchase_order_id)
            ->get();
        
        $company = PreferenceCompany::select('*')
            ->first();

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
                <table cellspacing=\"0\" cellpadding=\"2\" border=\"1\">
                    <tr>
                        <td><div style=\"text-align: center; font-size:12px; font-weight: bold\">Purchasing Order</div></td>
                   
                    </tr>
                </table>
            </td>

            </tr>

        </table>
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"1\">
            <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td>
                    <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                        <tr style=\"text-align: left;font-weight: bold\">
                                <td>No.  PO</td>   
                                <td>: " . $purchaseorder['purchase_order_no'] . "</td>
                        </tr>
                        <tr style=\"text-align: left;font-weight: bold\">
                                <td>Tgl. PO </td>
                                <td>: " . $purchaseorder['purchase_order_date'] . "</td>
                        </tr>
                        <tr style=\"text-align: left;font-weight: bold\">
                                <td>Pembayaran</td>
                                <td>: Hutang</td>
                        </tr>
                        <tr style=\"text-align: left;font-weight: bold\">
                                <td>Termin</td>
                                <td>: -</td>
                        </tr>
                    </table>
                </td>
                <td>
                <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                    <tr style=\"text-align: left;font-weight: bold\">
                            <td>Kode Supplier</td>   
                            <td>: " . $purchaseorder['supplier_code'] . "</td>
                    </tr>
                    <tr style=\"text-align: left;font-weight: bold\">
                            <td>Nama Supplier</td>
                            <td>: " . $purchaseorder['supplier_name'] . "</td>
                    </tr>
                    <tr style=\"text-align: left;font-weight: bold\">
                            <td>Telp.</td>
                            <td>: " . $purchaseorder['supplier_home_phone'] . "</td>
                    </tr>
                    <tr style=\"text-align: left;font-weight: bold\">
                            <td>Alamat</td>
                            <td>: " . $purchaseorder['supplier_address'] . "</td>
                    </tr>
                </table>
            </td>
            </tr>
        </table>
    </table>
        ";
        $pdf::writeHTML($tbl, true, false, false, false, '');

        $html2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
                        <tr style=\"text-align: center;\">
                            <td width=\"4%\" ><div style=\"text-align: center;\">No</div></td>
                            <td width=\"20%\" ><div style=\"text-align: center;\">Nama Barang</div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\">Qty</div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\">Harga </div></td>
                            <td width=\"9%\" ><div style=\"text-align: center;\">Diskon </div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\">Jumlah(DPP) </div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\">Total </div></td>
                            <td width=\"10%\" ><div style=\"text-align: center;\">Ket </div></td>
                        </tr>";
        $no = 1;
        $total = 0;
        $totalJumlah = 0;
        foreach ($purchaseorderitem as $key => $val) {
            $Jumlah = $val['quantity'] * $val['item_unit_cost'] - $val['discount_amount'] ;
            $totalJumlah += $Jumlah;
            $total = $totalJumlah  + $purchaseorder['ppn_in_amount'];
            $html2 .= "<tr>
                            <td>" . $no . "</td>
                            <td>" . $this->getItemCategoryName($val['item_category_id']) . "-" . $this->getItemTypeName($val['item_type_id']) . "</td>
                            <td style=\"text-align: right;\">" . $val['quantity'] . "</td>
                            <td style=\"text-align: right;\">" . number_format($val['item_unit_cost'], 2) . "</td>
                            <td style=\"text-align: right;\">" . $val['discount_percentage']  . "%</td>
                            <td style=\"text-align: right;\">" . number_format($val['subtotal_amount'], 2) . "</td>
                            <td style=\"text-align: right;\">" .  number_format($Jumlah , 2)  . "</td>
                            <td style=\"text-align: right;\">" . $purchaseorder['purchase_order_remark'] . "</td>
                        </tr> 
                        ";
            $no++;
        }

        $html2  .= " <tr>
                        <td colspan=\"6\" style=\"text-align: center;font-weight: bold\";>PPN</td>
                        <td style=\"text-align: right;\">" . number_format($purchaseorder['ppn_in_amount'], 2) . "</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan=\"6\" style=\"text-align: center;font-weight: bold\";>Total</td>
                        <td style=\"text-align: right;\">" . number_format($total, 2) . "</td>
                        <td></td>
                    </tr>
                    ";
        $html2 .= "</table>";
        $path = '<img width="60"; height="60" src="resources/assets/img/ttd.png">';
        //dd($path);        
        $html2 .= "
                    <table style=\"text-align: center;font-weight: bold\" cellspacing=\"20\";>
                        <tr>
                            <th>Disiapkan Oleh:</th>
                            <th>Disetujui Oleh:</th>
                            <th>Diketahui Oleh:</th>
                        </tr>
                    </table>
                    <table style=\"text-align: center;\" cellspacing=\"0\";>
                        <tr>
                            <th></th>
                            <th>".$path."</th>
                            <th></th>
                        </tr>
                    </table>
                    <table style=\"text-align: center;font-weight: bold\" cellspacing=\"0\";>
                    <tr>
                        <th>Martia Selawati</th>
                        <th>Isti Ramadhani S.Farm.,Apt</th>
                        <th>ADI SUTEJO</th>
                    </tr>
                </table>
                    ";
        $pdf::writeHTML($html2, true, false, true, false, '');
       // $pdf::Image($path, 98, 98, 15, 15, 'PNG', '', 'LT', false, 300, '', false, false, 1, false, false, false);



        // ob_clean();

        $filename = 'PO_'.$purchaseorder['purchase_order_no'].'.pdf';
        $pdf::Output($filename, 'I');
    }




    public function elements_add(Request $request)
    {
        $purchaseorderelements = Session::get('purchaseorderelements');
        if (!$purchaseorderelements || $purchaseorderelements == '') {
            $purchaseorderelements['purchase_order_date'] = '';
            $purchaseorderelements['purchase_order_shipment_date'] = '';
            $purchaseorderelements['warehouse_id'] = '';
            $purchaseorderelements['supplier_id'] = '';
            $purchaseorderelements['purchase_order_remark'] = '';
        }
        $purchaseorderelements[$request->name] = $request->value;
        Session::put('purchaseorderelements', $purchaseorderelements);
    }

    public function processAddArrayPurchaseOrderItem(Request $request)
    {
        $fields = $request->validate([
            'item_category_id'          => 'required',
            'item_type_id'              => 'required',
            'item_unit_id'              => 'required',
            'quantity'                  => 'required',
            'price'                     => 'required',
            'total_price'               => 'required',
            // 'discount_percentage'       => 'required',
        ]);

        $purchaseorderitem = array(
            'item_category_id'        => $request->item_category_id,
            'item_type_id'            => $request->item_type_id,
            'item_unit_id'            => $request->item_unit_id,
            'quantity'                => $request->quantity,
            'price'                    => $request->price,
            'total_price'            => $request->total_price,
            'discount_percentage'    => $request['discount_percentage_item'],
            'discount_amount'        => $request['discount_amount_item'],
        );
        //dd($purchaseorderitem);

        $lastpurchaseorderitem = Session::get('purchaseorderitem');
        if ($lastpurchaseorderitem !== null) {
            array_push($lastpurchaseorderitem, $purchaseorderitem);
            Session::put('purchaseorderitem', $lastpurchaseorderitem);
        } else {
            $lastpurchaseorderitem = [];
            array_push($lastpurchaseorderitem, $purchaseorderitem);
            Session::push('purchaseorderitem', $purchaseorderitem);
        }

        Session::put('editarraystate', 1);

        return redirect('/purchase-order/add');
    }

    public function deleteArrayPurchaseOrderItem($record_id)
    {
        $arrayBaru            = array();
        $dataArrayHeader    = Session::get('purchaseorderitem');

        foreach ($dataArrayHeader as $key => $val) {
            if ($key != $record_id) {
                $arrayBaru[$key] = $val;
            }
        }
        Session::forget('purchaseorderitem');
        Session::put('purchaseorderitem', $arrayBaru);

        return redirect('/purchase-order/add');
    }

    public function deletePurchaseOrder($purchase_order_id)
    {
        $purchaseorder = PurchaseOrder::findOrFail($purchase_order_id);
        $purchaseorder->data_state = 1;
        if ($purchaseorder->save()) {
            $msg = 'Hapus Purchase Order Berhasil';
            return redirect('/purchase-order')->with('msg', $msg);
        } else {
            $msg = 'Hapus Purchase Order Gagal';
            return redirect('/purchase-order')->with('msg', $msg);
        }
    }


    // public function getPPNIn(Request $request){

    // }





    public function getItemCategoryName($item_category_id)
    {
        $itemcategory = InvItemCategory::where('data_state', 0)
            ->where('item_category_id', $item_category_id)
            ->first();

        if ($itemcategory == null) {
            return "-";
        }


        return $itemcategory['item_category_name'];
    }

    public function getItemTypeName($item_type_id)
    {
        $itemtype = InvItemType::where('data_state', 0)
            ->where('item_type_id', $item_type_id)
            ->first();

        if ($itemtype == null) {
            return "-";
        }


        return $itemtype['item_type_name'];
    }

    public function getPurchaseOrderTypeName($purchase_order_type_id)
    {
        $data = PurchaseOrderType::where('data_state', 0)
            ->where('purchase_order_type_id', $purchase_order_type_id)
            ->first();

        if ($data == null) {
            return "-";
        }

        return $data['purchase_order_type_name'];
    }

    public function getItemUnitName($item_unit_id)
    {
        $itemunit = InvItemUnit::where('data_state', 0)
            ->where('item_unit_id', $item_unit_id)
            ->first();

        if ($itemunit == null) {
            return "-";
        }

        return $itemunit['item_unit_name'];
    }

    public function getCoreSupplierName($supplier_id)
    {
        $supplier = CoreSupplier::where('data_state', 0)
            ->where('supplier_id', $supplier_id)
            ->first();

        if ($supplier == null) {
            return "-";
        }

        return $supplier['supplier_name'];
    }

    public function getInvWarehouseName($warehouse_id)
    {
        $warehouse = InvWarehouse::where('data_state', 0)
            ->where('warehouse_id', $warehouse_id)
            ->first();

        if ($warehouse == null) {
            return "-";
        }

        return $warehouse['warehouse_name'];
    }

    public function getInvItemType(Request $request)
    {
        $item_category_id = $request->item_category_id;
        $data = '';

        $type = InvItemType::where('item_category_id', $item_category_id)
            ->where('data_state', '=', 0)
            ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($type as $mp) {
            $data .= "<option value='$mp[item_type_id]'>$mp[item_type_name]</option>\n";
        }

        return $data;
    }

    public function getCorecity(Request $request)
    {
        $province_id = $request->province_id;
        $data = '';

        $city = Corecity::where('province_id', $province_id)
            ->where('data_state', '=', 0)
            ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($city as $mp) {
            $data .= "<option value='$mp[city_id]'>$mp[city_name]</option>\n";
        }

        return $data;
    }

    public function addCoreSupplier(Request $request)
    {
        $supplier_name              = $request->supplier_name;
        $province_id                = $request->province_id;
        $city_id                    = $request->city_id;
        $supplier_address           = $request->supplier_address;
        $supplier_home_phone        = $request->supplier_home_phone;
        $supplier_mobile_phone1     = $request->supplier_mobile_phone1;
        $supplier_mobile_phone2     = $request->supplier_mobile_phone2;
        $supplier_fax_number        = $request->supplier_fax_number;
        $supplier_email             = $request->supplier_email;
        $supplier_contact_person    = $request->supplier_contact_person;
        $supplier_id_number         = $request->supplier_id_number;
        $supplier_tax_no            = $request->supplier_tax_no;
        $supplier_payment_terms     = $request->supplier_payment_terms;
        $supplier_remark            = $request->supplier_remark;
        $data = '';

        $coresupplier = CoreSupplier::create([
            'supplier_name'             => $supplier_name,
            'province_id'               => $province_id,
            'city_id'                   => $city_id,
            'supplier_address'          => $supplier_address,
            'supplier_home_phone'       => $supplier_home_phone,
            'supplier_mobile_phone1'    => $supplier_mobile_phone1,
            'supplier_mobile_phone2'    => $supplier_mobile_phone2,
            'supplier_fax_number'       => $supplier_fax_number,
            'supplier_email'            => $supplier_email,
            'supplier_contact_person'   => $supplier_contact_person,
            'supplier_id_number'        => $supplier_id_number,
            'supplier_tax_no'           => $supplier_tax_no,
            'supplier_payment_terms'    => $supplier_payment_terms,
            'supplier_remark'           => $supplier_remark,
            'created_id'                => Auth::id()
        ]);

        $supplier = CoreSupplier::where('data_state', '=', 0)
            ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($supplier as $mp) {
            $data .= "<option value='$mp[supplier_id]'>$mp[supplier_name]</option>\n";
        }

        return $data;
    }

    public function addInvWarehouse(Request $request)
    {
        $warehouse_code         = $request->warehouse_code;
        $warehouse_name         = $request->warehouse_name;
        $warehouse_address      = $request->warehouse_address;
        $warehouse_location_id  = $request->warehouse_location_id;
        $warehouse_phone        = $request->warehouse_phone;
        $warehouse_remark       = $request->warehouse_remark;
        $data = '';

        $coresupplier = InvWarehouse::create([
            'warehouse_code'        => $warehouse_code,
            'warehouse_name'        => $warehouse_name,
            'warehouse_address'     => $warehouse_address,
            'warehouse_location_id' => $warehouse_location_id,
            'warehouse_phone'       => $warehouse_phone,
            'warehouse_remark'      => $warehouse_remark,
            'created_id'            => Auth::id()
        ]);

        $warehouse = InvWarehouse::where('data_state', '=', 0)
            ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($warehouse as $mp) {
            $data .= "<option value='$mp[warehouse_id]'>$mp[warehouse_name]</option>\n";
        }

        return $data;
    }

    public function processAddPurchaseOrder(Request $request)
    {
        $fields = $request->validate([
            'purchase_order_date'               => 'required',
            'purchase_order_shipment_date'      => 'required',
            'warehouse_id'                      => 'required',
            'supplier_id'                       => 'required',
            'total_item_all'                    => 'required',
            'total_amount'                      => 'required',
        ]);

        $purchaseorder = array(
            'purchase_order_date'           => $fields['purchase_order_date'],
            'purchase_order_shipment_date'  => $fields['purchase_order_shipment_date'],
            'warehouse_id'                  => $fields['warehouse_id'],
            'supplier_id'                   => $fields['supplier_id'],
            'total_item'                    => $fields['total_item_all'],
            'total_amount'                  => $fields['total_amount'],
            'ppn_in_percentage'                => $request['ppn_in_percentage'],
            'ppn_in_amount'                    => $request['ppn_in_amount'],
            'subtotal_after_ppn_in'            => $request['subtotal_after_ppn_in'],
            'purchase_order_remark'         => $request->purchase_order_remark,
            'branch_id'                     => Auth::user()->branch_id,
        );
        //dd($purchaseorder);

        if (PurchaseOrder::create($purchaseorder)) {
            $purchase_order_id = PurchaseOrder::orderBy('created_at', 'DESC')->first();
            $purchaseorderitem = Session::get('purchaseorderitem');
            foreach ($purchaseorderitem as $key => $val) {
                $datapurchaseorderitem = array(
                    'purchase_order_id'             => $purchase_order_id['purchase_order_id'],
                    'item_category_id'              => $val['item_category_id'],
                    'item_type_id'                  => $val['item_type_id'],
                    'item_unit_id'                  => $val['item_unit_id'],
                    'quantity'                      => $val['quantity'],
                    'quantity_outstanding'          => $val['quantity'],
                    'item_unit_cost'                => $val['price'],
                    'subtotal_amount'               => $val['total_price'],
                    'discount_percentage'            => $val['discount_percentage'],
                    'discount_amount'                => $val['discount_amount'],
                );
                //dd($datapurchaseorderitem);
                PurchaseOrderItem::create($datapurchaseorderitem);
            }
            $msg = 'Tambah Purchase Order Berhasil';
            return redirect('/purchase-order')->with('msg', $msg);
        } else {
            $msg = 'Tambah Purchase Order Gagal';
            return redirect('/purchase-order/add')->with('msg', $msg);
        }
    }

    public function getSelectDataUnit(Request $request){

        // dd($request->all());
        // $item_stock_id  = $request->item_stock_id;
        // $item_type_id   = InvItemStock::select('*')
        // ->where('inv_item_stock.data_state','=',0)
        // ->where('inv_item_stock.item_stock_id', $item_stock_id)
        // ->where('inv_item_stock.warehouse_id', 6)
        // ->first();

        $inv_item_type= InvItemType::where('item_type_id', $request->item_type_id)
        ->first();
        
        $data= '';

        if($inv_item_type != null){
            $unit1 = InvItemType::select('inv_item_type.item_unit_1','inv_item_unit.*')
            ->join('inv_item_unit', 'inv_item_unit.item_unit_id', '=', 'inv_item_type.item_unit_1')
            ->where('inv_item_type.item_unit_1', $inv_item_type['item_unit_1'])
            // ->where('inv_item_type.item_unit_2', $inv_item_type['item_unit_2'])
            // ->where('inv_item_type.item_unit_3', $inv_item_type['item_unit_3'])
            ->first();
            
            // return $unit1;
            $unit2 = InvItemType::select('inv_item_type.item_unit_2','inv_item_unit.*')
            ->join('inv_item_unit', 'inv_item_unit.item_unit_id', '=', 'inv_item_type.item_unit_2')
            ->where('inv_item_type.item_unit_2', $inv_item_type['item_unit_2'])
            ->first();

            $unit3 = InvItemType::select('inv_item_type.item_unit_3','inv_item_unit.*')
            ->join('inv_item_unit', 'inv_item_unit.item_unit_id', '=', 'inv_item_type.item_unit_3')
            ->where('inv_item_type.item_unit_3', $inv_item_type['item_unit_3'])
            ->first();
        

        $array = [];
        if($unit1){
            array_push($array, $unit1);
        }
        if($unit2){
            array_push($array, $unit2);
        }
        if($unit3){
            array_push($array, $unit3);
        }
        // $unit = array_merge($unit1, $unit2);
        // $unit4 = array_merge($unit, $unit3);
        
        
        $data .= "<option value=''>--Choose One--</option>";
        foreach ($array as $val){
            print_r($val['item_unit_id']);
            
            $data .= "<option value='$val[item_unit_id]'>$val[item_unit_name]</option>\n";	
        }
        return $data;
        }
    }
}
