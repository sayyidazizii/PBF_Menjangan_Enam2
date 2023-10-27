<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\PurchaseOrder;
use App\Models\PreferenceCompany;
use App\Models\PurchaseOrderItem;
use App\Models\InvWarehouse;
use App\Models\CoreSupplier;
use App\Models\InvItemCategory;
use App\Models\InvItemStock;
use App\Models\InvItemUnit;
use App\Models\InvItemType;
use App\Models\PreferenceTransactionModule;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PurchaseOrderApprovalController extends Controller
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
        Session::put('editarraystate', 0);
        Session::forget('purchaseorderitem');

        $purchaseorder = PurchaseOrder::where('data_state','=',0)
        ->where('approved', 0)
        ->get();

        return view('content/PurchaseOrder/ListPurchaseOrderApproval',compact('purchaseorder'));
    }

    public function approvePurchaseOrder($purchase_order_id){
        $purchaseorder= PurchaseOrder::where('data_state',0)
        ->where('purchase_order_id', $purchase_order_id)
        ->first();

        $purchaseorderitem= PurchaseOrderItem::where('data_state',0)
        ->where('purchase_order_id', $purchase_order_id)
        ->get();

        return view('content/PurchaseOrder/FormApprovePurchaseOrder',compact('purchaseorderitem', 'purchaseorder', 'purchase_order_id'));
    }

    public function processApprovePurchaseOrder(Request $request){
        $purchase_order_id = $request->purchase_order_id;
        $purchaseorder = PurchaseOrder::findOrFail($purchase_order_id);
        $purchaseorder->approved = 1;
        $purchaseorder->save();

        $msg = 'Approve Purchase Order Berhasil';
        return redirect('/purchase-order-approval')->with('msg',$msg);
    }

    public function processDisapprovePurchaseOrder(Request $request){
        $purchase_order_id = $request->purchase_order_id;
        $purchaseorder = PurchaseOrder::findOrFail($purchase_order_id);
        $purchaseorder->approved = 2;
        $purchaseorder->save();

        $msg = 'Disapprove Purchase Order Berhasil';
        return redirect('/purchase-order-approval')->with('msg',$msg);
    }

    public function detailPurchaseOrder($purchase_order_id)
    {
        $purchaseorder= PurchaseOrder::where('data_state',0)
        ->where('purchase_order_id', $purchase_order_id)
        ->first();

        $purchaseorderitem= PurchaseOrderItem::where('data_state',0)
        ->where('purchase_order_id', $purchase_order_id)
        ->get();

        return view('content/PurchaseOrder/FormDetailPurchaseOrder',compact('purchaseorderitem', 'purchaseorder'));
    }

    public function getItemCategoryName($item_category_id){
        $itemcategory = InvItemCategory::where('data_state', 0)
        ->where('item_category_id', $item_category_id)
        ->first();

        return $itemcategory['item_category_name'];
    }

    public function getItemTypeName($item_type_id){
        $itemtype = InvItemType::where('data_state', 0)
        ->where('item_type_id', $item_type_id)
        ->first();

        return $itemtype['item_type_name'];
    }

    public function getItemUnitName($item_unit_id){
        $itemunit = InvItemUnit::where('data_state', 0)
        ->where('item_unit_id', $item_unit_id)
        ->first();

        return $itemunit['item_unit_name'];
    }

    public function getCoreSupplierName($supplier_id){
        $supplier = CoreSupplier::where('data_state', 0)
        ->where('supplier_id', $supplier_id)
        ->first();

        return $supplier['supplier_name'];
    }

    public function getInvWarehouseName($warehouse_id){
        $warehouse = InvWarehouse::where('data_state', 0)
        ->where('warehouse_id', $warehouse_id)
        ->first();

        return $warehouse['warehouse_name'];
    }

    public function processAddPurchaseOrder(Request $request){
        $fields = $request->validate([
            'purchase_order_date'               => 'required',
            'purchase_order_shipment_date'      => 'required',
            'warehouse_id'                      => 'required',
            'supplier_id'                       => 'required',
            'total_item_all'                    => 'required',
            'total_price_all'                   => 'required',
        ]);

        $branch_id = User::select('branch_id')->where('user_id','=',Auth::id())->first();

        $purchaseorder = array (
            'purchase_order_date'           => $fields['purchase_order_date'],
            'purchase_order_shipment_date'  => $fields['purchase_order_shipment_date'],
            'warehouse_id'                  => $fields['warehouse_id'],
            'supplier_id'                   => $fields['supplier_id'],
            'total_item'                    => $fields['total_item_all'],
            'total_amount'                  => $fields['total_price_all'],
            'purchase_order_remark'         => $request->purchase_order_remark,
            'branch_id'                     => $branch_id,
        );

        if(PurchaseOrder::create($purchaseorder)){
            $purchase_order_id = PurchaseOrder::orderBy('created_at','DESC')->first();
            $purchaseorderitem = Session::get('purchaseorderitem');
            foreach ($purchaseorderitem AS $key => $val){
                $datapurchaseorderitem = array (
                    'purchase_order_id'     => $purchase_order_id['purchase_order_id'],
                    'item_category_id'      => $val['item_category_id'],
                    'item_type_id'          => $val['item_type_id'],
                    'item_unit_id'          => $val['item_unit_id'],
                    'quantity'              => $val['quantity'],
                    'quantity_outstanding'  => $val['quantity'],
                    'item_unit_cost'        => $val['price'],
                    'subtotal_amount'       => $val['total_price'],
                );

                PurchaseOrderItem::create($datapurchaseorderitem);
            }
            $msg = 'Tambah Purchase Order Berhasil';
            return redirect('/purchase-order')->with('msg',$msg);
        }else{
            $msg = 'Tambah Purchase Order Gagal';
            return redirect('/purchase-order/add')->with('msg',$msg);
        }

    }
}
