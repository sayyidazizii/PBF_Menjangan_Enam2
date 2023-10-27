<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\AcctAccount;
use App\Models\AcctAccountBalance;
use App\Models\AcctAccountBalanceDetail;
use App\Models\AcctDisbursement;
use App\Models\AcctDisbursementDisbursement;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctReceipt;
use App\Models\AcctReceiptItem;
use App\Models\CoreCustomer;
use App\Models\CoreSupplier;
use App\Models\InvItem;
use App\Models\InvItemType;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchasePayment;
use App\Models\SalesCollection;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;

class APIController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
    public function login(Request $request){
        $fields = $request->validate([
            'username'   => 'required|string',
            'password'   => 'required|string',
        ]);

        // Check username
        $user = User::select('system_user.*', 'system_user_group.user_group_name')
                ->join('system_user_group', 'system_user_group.user_group_id', 'system_user.user_group_id')
                ->where('name', $fields['username'])
                ->first();

        //Check password
        if(!Hash::check($fields['password'], $user->password)){
            return response([
                'message' => 'Username / Password Tidak Sesuai'
            ],401);
        }

        $token = $user->createToken('token-name')->plainTextToken;
        $response = [
            'data'  => $user,
            'token' => $token,
        ];

        return response($response, 201);
    }

    public function logout(Request $request){
        $user = auth()->user();
        $user_state = User::findOrFail($user['user_id']);
        $user_state->save();

        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged Out'
        ];
    }

    public function userProfile(Request $request){
        $fields = $request->validate([
            'user_id'   => 'required|string',
        ]);

        // Check username
        $user = User::select('system_user.name, system_user.created_at, system_user_group.user_group_name')
        ->join('system_user_group', 'system_user_group.user_group_id', 'system_user.user_gorup_id')
        ->where('system_user.user_id', $fields['user_id'])
        ->first();

        $response = [
            'data'  => $user,
        ];

        return response($response, 201);
    }

    public function changePassword(Request $request){
        $fields = $request->validate([
            'old_password'  => 'required|string',
            'new_password'  => 'required|string',
            'user_id'       => 'required|string',
        ]);

        // Check username
        $user = User::findOrFail($fields['user_id']);
        
        if(!Hash::check($fields['old_password'], $user->password)){
            return response([
                'message' => 'Password Lama Tidak Sesuai'
            ],401);
        }

        $user->password = Hash::make($fields['new_password']);
        if($user->save()){
            return response([
                'message' => 'Ganti Password Berhasil'
            ],201);
        }else{
            return response([
                'message' => 'Ganti Password Tidak Berhasil'
            ],401);
        }


    }

    public function purchaseOrder(Request $request){
        $fields = $request->validate([
            'start_date'    => 'required|string',
            'end_date'      => 'required|string',
        ]);

        $itemtype = InvItemType::where('data_state', 0)
                    ->get();

        $coresupplier = CoreSupplier::where('data_state', 0)
                        ->get();

        $purchaseorder = PurchaseOrder::whereDate('purchase_order_date', '>=', date('Y-m-d', strtotime($fields['start_date'])))
                        ->whereDate('purchase_order_date', '<=', date('Y-m-d', strtotime($fields['end_date'])))
                        ->where('data_state', 0)
                        ->get();

        $total_amount = 0;
        foreach($purchaseorder as $key => $val){
            $total_amount += $val['total_amount'];
        }

        foreach($itemtype as $key => $val){
            $purchaseorderitem = PurchaseOrderItem::select('purchase_order_item.*', 'inv_item_unit.item_unit_default_quantity')
            ->join('purchase_order', 'purchase_order.purchase_order_id', 'purchase_order_item.purchase_order_id')
            ->join('inv_item_unit', 'inv_item_unit.item_unit_id', 'purchase_order_item.item_unit_id')
            ->whereDate('purchase_order.purchase_order_date', '>=', date('Y-m-d', strtotime($fields['start_date'])))
            ->whereDate('purchase_order.purchase_order_date', '<=', date('Y-m-d', strtotime($fields['end_date'])))
            ->where('purchase_order_item.item_type_id', $val['item_type_id'])
            ->where('purchase_order_item.data_state', 0)
            ->where('purchase_order.data_state', 0)
            ->get();

            $quantity = 0;
            $amount = 0;
            foreach($purchaseorderitem as $key1 => $val1){
                $current_quantity = $val1['quantity']*$val1['item_unit_default_quantity'];
                $quantity += $current_quantity;
                
                $amount += $val1['subtotal_amount'];
            }
            $dataitemtype['name']       = $val['item_type_name'];
            $dataitemtype['quantity']   = $quantity;
            $dataitemtype['amount']     = $amount;
            $itemtypequantity[$key]     = $dataitemtype;
        }
        
        foreach($coresupplier as $key => $val){
            $purchaseorder = PurchaseOrder::select('purchase_order.*')
            ->whereDate('purchase_order.purchase_order_date', '>=', date('Y-m-d', strtotime($fields['start_date'])))
            ->whereDate('purchase_order.purchase_order_date', '<=', date('Y-m-d', strtotime($fields['end_date'])))
            ->where('purchase_order.supplier_id', $val['supplier_id'])
            ->where('purchase_order.data_state', 0)
            ->get();

            $amount = 0;
            foreach($purchaseorder as $key1 => $val1){
                $current_amount = $val1['total_amount'];
                $amount += $current_amount;
            }
            $datasupplier['name']           = $val['supplier_name'];
            $datasupplier['amount']         = $amount;
            $supplieramount[$key]           = $datasupplier;
        }

        $response = [
            'total_amount'      => $total_amount,
            'typequantity'      => $itemtypequantity,
            'supplieramount'    => $supplieramount,
        ];

        return response($response, 201);

    }

    public function salesOrder(Request $request){
        $fields = $request->validate([
            'start_date'    => 'required|string',
            'end_date'      => 'required|string',
        ]);

        $invitem        = InvItem::select('inv_item.*', 'inv_item_type.item_type_name', 'core_grade.grade_name')
                        ->join('inv_item_type', 'inv_item_type.item_type_id', 'inv_item.item_type_id')
                        ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
                        ->where('inv_item.data_state', 0)
                        ->get();

        $corecustomer   = CoreCustomer::where('data_state', 0)
                        ->get();

        $salesorder = SalesOrder::whereDate('sales_order_date', '>=', date('Y-m-d', strtotime($fields['start_date'])))
                        ->whereDate('sales_order_date', '<=', date('Y-m-d', strtotime($fields['end_date'])))
                        ->where('data_state', 0)
                        ->get();

        $total_amount = 0;
        foreach($salesorder as $key => $val){
            $total_amount += $val['total_amount'];
        }

        foreach($invitem as $key => $val){
            $salesorderitem = SalesOrderItem::select('sales_order_item.*', 'inv_item_unit.item_unit_default_quantity')
            ->join('sales_order', 'sales_order.sales_order_id', 'sales_order_item.sales_order_id')
            ->join('inv_item_unit', 'inv_item_unit.item_unit_id', 'sales_order_item.item_unit_id')
            ->join('inv_item', 'inv_item.item_id', 'sales_order_item.item_id')
            ->whereDate('sales_order.sales_order_date', '>=', date('Y-m-d', strtotime($fields['start_date'])))
            ->whereDate('sales_order.sales_order_date', '<=', date('Y-m-d', strtotime($fields['end_date'])))
            ->where('sales_order_item.item_id', $val['item_id'])
            ->where('sales_order_item.data_state', 0)
            ->where('sales_order.data_state', 0)
            ->get();

            $quantity = 0;
            $amount = 0;
            foreach($salesorderitem as $key1 => $val1){
                $current_quantity = $val1['quantity']*$val1['item_unit_default_quantity'];
                $quantity += $current_quantity;
                
                $amount += $val1['subtotal_amount'];
            }
            $dataitemtype['name']       = $val['item_type_name'].' '.$val['grade_name'];
            $dataitemtype['quantity']   = $quantity;
            $dataitemtype['amount']     = $amount;
            $itemtypequantity[$key]     = $dataitemtype;
        }
        
        foreach($corecustomer as $key => $val){
            $salesorder = SalesOrder::select('sales_order.*')
            ->whereDate('sales_order.sales_order_date', '>=', date('Y-m-d', strtotime($fields['start_date'])))
            ->whereDate('sales_order.sales_order_date', '<=', date('Y-m-d', strtotime($fields['end_date'])))
            ->where('sales_order.customer_id', $val['customer_id'])
            ->where('sales_order.data_state', 0)
            ->get();

            $amount = 0;
            foreach($salesorder as $key1 => $val1){
                $current_amount = $val1['total_amount'];
                $amount += $current_amount;
            }
            $datacustomer['name']           = $val['customer_name'];
            $datacustomer['amount']         = $amount;
            $customeramount[$key]           = $datacustomer;
        }

        $response = [
            'total_amount'      => $total_amount,
            'typequantity'      => $itemtypequantity,
            'customeramount'    => $customeramount,
        ];

        return response($response, 201);

    }

    public function cashBank(Request $request){
        $fields = $request->validate([
            'start_date'    => 'required|string',
            'end_date'      => 'required|string',
        ]);

        $acctreceipt = AcctReceipt::where('data_state', 0)
        ->whereDate('receipt_date', '>=', date('Y-m-d', strtotime($fields['start_date'])))
        ->whereDate('receipt_date', '<=', date('Y-m-d', strtotime($fields['end_date'])))
        ->get();

        $receipt_amount = 0;
        foreach($acctreceipt as $key => $val){
            $receipt_amount += $val['receipt_amount_total'];
        }
        
        $acctdisbursement = AcctDisbursement::where('data_state', 0)
        ->whereDate('disbursement_date', '>=', date('Y-m-d', strtotime($fields['start_date'])))
        ->whereDate('disbursement_date', '<=', date('Y-m-d', strtotime($fields['end_date'])))
        ->get();

        $disbursement_amount = 0;
        foreach($acctdisbursement as $key => $val){
            $disbursement_amount += $val['disbursement_amount_total'];
        }
        
        $acctaccount = AcctAccount::select('acct_account.account_code', 'acct_account.account_name', 'acct_account_balance.last_balance')
        ->join('acct_account_balance', 'acct_account_balance.account_id', 'acct_account.account_id')
        ->where('acct_account.data_state', 0)
        ->get();

        $purchasepayment = PurchasePayment::select('purchase_payment.payment_amount', 'purchase_payment.payment_date', 'core_supplier.supplier_name')
        ->join('core_supplier', 'core_supplier.supplier_id', 'purchase_payment.supplier_id')
        ->whereDate('purchase_payment.payment_date', '>=', date('Y-m-d', strtotime($fields['start_date'])))
        ->whereDate('purchase_payment.payment_date', '<=', date('Y-m-d', strtotime($fields['end_date'])))
        ->where('purchase_payment.data_state', 0)
        ->get();

        $salescollection = SalesCollection::select('sales_collection.collection_amount', 'sales_collection.collection_date', 'core_customer.customer_name')
        ->join('core_customer', 'core_customer.customer_id', 'sales_collection.customer_id')
        ->whereDate('sales_collection.collection_date', '>=', date('Y-m-d', strtotime($fields['start_date'])))
        ->whereDate('sales_collection.collection_date', '<=', date('Y-m-d', strtotime($fields['end_date'])))
        ->where('sales_collection.data_state', 0)
        ->get();

        $response = [
            'receipt_amount'        => $receipt_amount,
            'disbursement_amount'   => $disbursement_amount,
            'acctaccount'           => $acctaccount,
            'purchasepayment'       => $purchasepayment,
            'salescollection'       => $salescollection,
        ];

        return response($response, 201);

    }
}
