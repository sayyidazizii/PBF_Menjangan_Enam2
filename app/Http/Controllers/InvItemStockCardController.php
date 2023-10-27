<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\InvItemStockCard;
use App\Models\InvItemUnit;
use App\Models\InvItemCategory;
use App\Models\InvItemStock;
use App\Models\InvItemType;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;


class InvItemStockCardController extends Controller
{
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

        // dd($itemstock);
        
        $itemstock = InvItemStock::where('inv_item_stock.data_state', 0)
        ->where('inv_item_stock.updated_at', '>=', $start_date)
        ->where('inv_item_stock.updated_at', '<=', $end_date)
        // ->join('inv_item_stock_card', 'inv_item_stock_card.item_stock_id', '=', 'inv_item_stock.item_stock_id' )
        ->get();

        // $data_stock = InvItemStock::findOrFail('item_stock_id', $itemstock['item_stock_id']);
        
        // dd($itemstockcard);
        return view('content/InvItemStockCard/ListInvItemStockCard',compact('itemstock', 'start_date', 'end_date'));
    }

    public function filterInvItemStockCard(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/item-stock-card');
    }

    public function resetFilterInvItemStockCard()
    {
        Session::forget('start_date');
        Session::forget('end_date');

        return redirect('/item-stock-card');
    }

    public function getItemCategoryName($item_category_id){
        $itemcategory = InvItemCategory::where('data_state', 0)
        ->where('item_category_id', $item_category_id)
        ->first();

        if($itemcategory == null){
            return "-";
        }

        return $itemcategory['item_category_name'];
    }

    public function getItemTypeName($item_type_id){
        $itemtype = InvItemType::where('data_state', 0)
        ->where('item_type_id', $item_type_id)
        ->first();

        if($itemtype == null){
            return "-";
        }

        return $itemtype['item_type_name'];
    }

    public function getItemUnitName($item_type_id){

        $itemtype = InvItemType::where('data_state', 0)
        ->where('item_type_id', $item_type_id)
        ->first();
        
        $itemunit = InvItemUnit::where('data_state', 0)
        ->where('item_unit_id', $itemtype['item_unit_1'])
        ->first();

        if($itemunit == null){
            return "-";
        }

        return $itemunit['item_unit_name'];
    }

    public function getOpeningBalance($item_stock_id){
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

        $itemstockcard = InvItemStockCard::where('data_state', 0)
        ->where('updated_at', '>=', $start_date)
        ->where('updated_at', '<=', $end_date)
        ->where('item_stock_id', $item_stock_id)
        ->first();

        if($itemstockcard == null){
            return "0";
        }

        return $itemstockcard['opening_balance'];
    }

    public function getLastBalance($item_stock_id){
        $itemstockcard = InvItemStockCard::where('data_state', 0)
        ->where('item_stock_id', $item_stock_id)
        ->orderBy('item_stock_card_id','desc')
        ->limit(1)
        ->first();

        if($itemstockcard == null){
            return "0";
        }

        return $itemstockcard['last_balance'];
    }

    public function getStockIn($item_stock_id){
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

        $itemstockcard = InvItemStockCard::where('data_state', 0)
        ->where('updated_at', '>=', $start_date)
        ->where('updated_at', '<=', $end_date)
        ->where('item_stock_id', $item_stock_id)
        ->sum('item_stock_card_in');

        if($itemstockcard == null){
            return "0";
        }

        return $itemstockcard;
    }

    public function getStockOut($item_stock_id){
        $itemstockcard = InvItemStockCard::where('data_state', 0)
        ->where('item_stock_id', $item_stock_id)
        ->sum('item_stock_card_out');

        if($itemstockcard == null){
            return "0";
        }

        return $itemstockcard;
    }

    public function detailInvItemStockCard($item_stock_id){
        $itemstockcard = InvItemStockCard::where('inv_item_stock_card.data_state', 0)
        ->join('inv_item_stock', 'inv_item_stock.item_stock_id', '=', 'inv_item_stock_card.item_stock_id' )
        ->where('inv_item_stock.item_stock_id', $item_stock_id)
        ->get();

        return view('content/InvItemStockCard/DetailInvItemStockCard',compact('itemstockcard'));
    }

    public function printStockCardReport($item_stock_id)
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

        // dd($itemstock);
        
        $itemstock = InvItemStock::where('inv_item_stock.data_state', 0)
        ->where('inv_item_stock.updated_at', '>=', $start_date)
        ->where('inv_item_stock.updated_at', '<=', $end_date)
        ->join('inv_item_stock_card', 'inv_item_stock_card.item_stock_id', '=', 'inv_item_stock.item_stock_id' )
        ->get();

        $itemstockcard = InvItemStockCard::where('inv_item_stock_card.data_state', 0)
        ->join('inv_item_stock', 'inv_item_stock.item_stock_id', '=', 'inv_item_stock_card.item_stock_id')
        ->where('inv_item_stock_card.created_at', '>=', $start_date)
        ->where('inv_item_stock_card.created_at', '<=', $end_date)
        ->where('inv_item_stock.item_stock_id', $item_stock_id)
        ->get();

        $data_stock = InvItemStockCard::where('data_state', 0)
        ->where('item_stock_id', $item_stock_id)
        ->first();

        //dd($data_stock);

        // $opening_balance = InvItemStockCard::where('data_state', 0)
        // ->where('item_stock_id', $item_stock_id)
        // ->where('updated_at', '>=', $start_date)
        // ->where('updated_at', '<=', $end_date)
        // ->first();

        //-----------TCPF-----------
        $pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(10, 10, 10, 10); // put space of 10 on top

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 8);

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td><div style=\"text-align: center; font-size:14px; font-weight: bold\">Kartu Stock</div></td>
            </tr>
        </table>
        ";

        $pdf::writeHTML($tbl, true, false, false, false, '');

        $tbl = "
        <br>
        <br>
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td width=\"15%\"><div style=\"text-align: lef=ft; font-size:12px\">Periode</div></td>
                <td width=\"3%\"><div style=\"text-align: center;  font-size:12px\">:</div></td>
                <td width=\"60%\"><div style=\"text-align: left;  font-size:12px\">".date('d-m-Y', strtotime($start_date))." s/d ".date('d-m-Y', strtotime($end_date))."</div></td>
            </tr>
            <tr>
                <td width=\"15%\"><div style=\"text-align: lef=ft; font-size:12px\">Tipe Barang</div></td>
                <td width=\"3%\"><div style=\"text-align: center;  font-size:12px\">:</div></td>
                <td width=\"60%\"><div style=\"text-align: left;  font-size:12px\">".$this->getItemTypeName($data_stock['item_type_id'])."</div></td>
            </tr>
            <tr>
                <td width=\"15%\"><div style=\"text-align: lef=ft; font-size:12px\">Number Batch</div></td>
                <td width=\"3%\"><div style=\"text-align: center;  font-size:12px\">:</div></td>
                <td width=\"60%\"><div style=\"text-align: left;  font-size:12px\">".$data_stock['item_batch_number']."</div></td>
            </tr>
        </table>";
        $pdf::writeHTML($tbl, true, false, false, false, '');
        
        $no = 1;
        $tblStock1 = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
            <tr>
                <td width=\"5%\"><div style=\"text-align: center; font-weight: bold\">No</div></td>
                <td width=\"10%\"><div style=\"text-align: center; font-weight: bold\">Tanggal</div></td>
                <td width=\"25%\"><div style=\"text-align: center; font-weight: bold\">Keterangan</div></td>
                <td width=\"15%\"><div style=\"text-align: center; font-weight: bold\">Stock Awal</div></td>
                <td width=\"15%\"><div style=\"text-align: center; font-weight: bold\">Stock Masuk</div></td>
                <td width=\"15%\"><div style=\"text-align: center; font-weight: bold\">Stock Keluar</div></td>
                <td width=\"15%\"><div style=\"text-align: center; font-weight: bold\">Stock Akhir</div></td>
            </tr>
            ";

        $tblStock2 = " ";
        $no = 1;

        foreach ($itemstockcard as $key => $val) {

            $tblStock2 .="
                        <tr>			
                            <td style=\"text-align:center\">$no.</td>
                            <td style=\"text-align:center\">".date('d-m-Y', strtotime($val['transaction_date']))."</td>
                            <td> ".$val['transaction_code']."</td>
                            <td><div style=\"text-align: right;\">".$val['opening_balance']."</div></td>
                            <td><div style=\"text-align: right;\">".$val['item_stock_card_in']."</div></td>
                            <td><div style=\"text-align: right;\">".$val['item_stock_card_out']."</div></td>
                            <td><div style=\"text-align: right;\">".$val['last_balance']."</div></td>
                        </tr>
                        
                    ";
            $no++;
        }
        $tblStock4 = " 
        </table>
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td style=\"text-align:right; font-style: italic;\">".Auth::user()->name.", ".date('d-m-Y H:i')."</td>
            </tr>
        </table>";

        $pdf::writeHTML($tblStock1.$tblStock2.$tblStock4, true, false, false, false, '');

        $filename = 'Kartu_Stock_'.date('dFY', strtotime($start_date)).'.pdf';
        $pdf::Output($filename, 'I');

        return redirect('/ledger');
    }
}
