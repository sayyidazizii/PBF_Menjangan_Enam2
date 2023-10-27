<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CoreCustomer;
use App\Providers\RouteServiceProvider;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\InvWarehouse;
use App\Models\CoreSupplier;
use App\Models\InvItemCategory;
use App\Models\InvItemUnit;
use App\Models\InvItemType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SalesOrderApprovalController extends Controller
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
        Session::forget('salesorderitem');

        $salesorder = SalesOrder::where('data_state','=',0)
        ->where('approved', 0)
        ->get();

        return view('content/SalesOrder/ListSalesOrderApproval',compact('salesorder')); 
    }

    public function approveSalesOrder($sales_order_id){
        $salesorder= SalesOrder::where('data_state',0)
        ->where('sales_order_id', $sales_order_id)
        ->first();

        $salesorderitem= SalesOrderItem::where('data_state',0)
        ->where('sales_order_id', $sales_order_id)
        ->get();

        return view('content/SalesOrder/FormApproveSalesOrder',compact('salesorderitem', 'salesorder', 'sales_order_id'));
    }

    public function processApproveSalesOrder(Request $request){
        $sales_order_id = $request->sales_order_id;
        $salesorder = SalesOrder::findOrFail($sales_order_id);
        $salesorder->approved = 1;
        $salesorder->save();

        $msg = 'Approve Sales Order Berhasil';
        return redirect('/sales-order-approval')->with('msg',$msg);
    }

    public function processDisapproveSalesOrder(Request $request){
        $sales_order_id = $request->sales_order_id;
        $salesorder     = SalesOrder::findOrFail($sales_order_id);
        $salesorder->approved = 2;
        $salesorder->save();

        $msg = 'Disapprove Sales Order Berhasil';
        return redirect('/sales-order-approval')->with('msg',$msg);
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

    public function getCoreCustomerName($customer_id){
        $customer = CoreCustomer::where('data_state', 0)
        ->where('customer_id', $customer_id)
        ->first();

        return $customer['customer_name'];
    }
}
