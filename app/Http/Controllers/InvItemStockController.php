<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\InvItem;
use App\Models\CoreGrade;
use App\Models\InvItemType;
use App\Models\InvItemUnit;
use App\Models\InvItemStock;
use App\Models\InvItemCategory;
use App\Models\InvWarehouse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvItemStockController extends Controller
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
        $invitemcategory    = InvItemCategory::where('data_state', 0)->pluck('item_category_name', 'item_category_id');

        $invitemtype        = InvItemType::where('data_state', 0)->pluck('item_type_name', 'item_type_id');

        $coregrade          = CoreGrade::where('data_state', 0)->pluck('grade_name', 'grade_id');

        $invwarehouse       = InvWarehouse::where('data_state', 0)->pluck('warehouse_name', 'warehouse_id');

        $item_category_id   = Session::get('filteritemcategoryid');

        $item_type_id       = Session::get('filteritemtypeid');

        $grade_id           = Session::get('filtergradeid');
        
        $warehouse_id       = Session::get('filterwarehouseid');
        
        $invitemstock       = InvItemStock::select('inv_item_stock.*')
        ->where('inv_item_stock.data_state','=',0);
        if($item_category_id||$item_category_id!=null||$item_category_id!=''){
            $invitemstock   = $invitemstock->where('inv_item_stock.item_category_id', $item_category_id);
        }
        if($item_type_id||$item_type_id!=null||$item_type_id!=''){
            $invitemstock   = $invitemstock->where('inv_item_stock.item_type_id', $item_type_id);
        }
        if($warehouse_id||$warehouse_id!=null||$warehouse_id!=''){
            $invitemstock   = $invitemstock->where('inv_item_stock.warehouse_id', $warehouse_id);
        }
        $invitemstock       = $invitemstock->get();
    //    dd($invitemstock);

        return view('content/InvItemStock/ListInvItemStock',compact('invitemstock', 'invitemcategory', 'invitemtype', 'coregrade', 'invwarehouse', 'item_category_id', 'item_type_id', 'grade_id', 'warehouse_id'));
    }

    public function  getCoreGradeName($item_id){
        $grade = InvItem::select('core_grade.grade_name')
        ->where('item_id', $item_id)
        ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
        ->first();
        
        if($grade == null){
            return "-";
        }
        return $grade['grade_name'];
    }

    public function filterInvItemStock(Request $request){
        $item_category_id   = $request->item_category_id;
        $item_type_id       = $request->item_type_id;
        $grade_id           = $request->grade_id;
        $warehouse_id       = $request->warehouse_id;
        print_r('|||item_category_id'.$item_category_id);
        print_r('|||item_type_id'.$item_type_id);
        print_r('|||grade_id'.$grade_id);
        print_r('|||warehouse_id'.$warehouse_id);
        // exit;

        Session::put('filteritemcategoryid', $item_category_id);
        Session::put('filteritemtypeid', $item_type_id);
        Session::put('filtergradeid', $grade_id);
        Session::put('filterwarehouseid', $warehouse_id);

        return redirect('/item-stock');
    }

    public function getInvItemType(Request $request){
        $item_category_id = $request->item_category_id;
        $data='';
        $type = InvItemType::where('item_category_id', $item_category_id)
        ->where('data_state','=',0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($type as $mp){
            $data .= "<option value='$mp[item_type_id]'>$mp[item_type_name]</option>\n";	
        }

        return $data;
    }

    public function getCoreGrade(Request $request){
        $item_category_id   = $request->item_category_id;
        $item_type_id       = $request->item_type_id;
        $data='';

        $type = InvItem::select('core_grade.grade_name', 'core_grade.grade_id')
        ->join('core_grade', 'core_grade.grade_id', 'inv_item.grade_id')
        ->where('item_category_id', $item_category_id)
        ->where('item_type_id', $item_type_id)
        ->where('data_state','=',0)
        ->get();

        $data .= "<option value=''>--Choose One--</option>";
        foreach ($type as $mp){
            $data .= "<option value='$mp[grade_id]'>$mp[grade_name]</option>\n";	
        }

        return $data;
    }

    public function getInvItemCategoryName($item_category_id){
        $item = InvItemCategory::select('item_category_name')
        ->where('item_category_id', $item_category_id)
        ->where('data_state', 0)
        ->first();

        return $item['item_category_name'];
    }

    public function getInvItemTypeName($item_type_id){
        $item = InvItemType::select('item_type_name')
        ->where('item_type_id', $item_type_id)
        ->where('data_state', 0)
        ->first();

        return $item['item_type_name'];
    }

    public function getInvItemUnitName($item_unit_id){
        $unit = InvItemUnit::select('item_unit_name')
        ->where('item_unit_id', $item_unit_id)
        ->where('data_state', 0)
        ->first();

        return $unit['item_unit_name'];
    }

    public function getInvWarehouseName($warehouse_id){
        $warehouse = InvWarehouse::select('warehouse_name')
        ->where('warehouse_id', $warehouse_id)
        ->where('data_state', 0)
        ->first();

        return $warehouse['warehouse_name'];
    }

    public function export(){

        $item_category_id   = Session::get('filteritemcategoryid');

        $item_type_id       = Session::get('filteritemtypeid');

        $grade_id           = Session::get('filtergradeid');
        
        $warehouse_id       = Session::get('filterwarehouseid');
        
        $invitemstock       = InvItemStock::where('inv_item_stock.data_state', 0);
        if($item_category_id||$item_category_id!=null||$item_category_id!=''){
            $invitemstock   = $invitemstock->where('inv_item_stock.item_category_id', $item_category_id);
        }
        if($item_type_id||$item_type_id!=null||$item_type_id!=''){
            $invitemstock   = $invitemstock->where('inv_item_stock.item_type_id', $item_type_id);
        }
        if($grade_id||$grade_id!=null||$grade_id!=''){
            $invitemstock   = $invitemstock->join('inv_item', 'inv_item.item_id', 'inv_item_stock.item_id')
            ->where('inv_item.grade_id', $grade_id);
        }
        if($warehouse_id||$warehouse_id!=null||$warehouse_id!=''){
            $invitemstock   = $invitemstock->where('inv_item_stock.warehouse_id', $warehouse_id);
        }
        $invitemstock       = $invitemstock->get();

        $spreadsheet = new Spreadsheet();

        if(count($invitemstock)>=0){
            $spreadsheet->getProperties()->setCreator("TRADING SYSTEM")
                ->setLastModifiedBy("TRADING SYSTEM")
                ->setTitle("Stock Barang")
                ->setSubject("")
                ->setDescription("Stock Barang")
                ->setKeywords("Stock Barang")
                ->setCategory("Stock Barang");

            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Stock Barang");
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(10);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(25);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);


    
            $spreadsheet->getActiveSheet()->mergeCells("B1:M1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);

            $spreadsheet->getActiveSheet()->getStyle('B3:M3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:M3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('B1', "Stock Barang Periode ".date('d M Y'));	
            $sheet->setCellValue('B3', "No");
            $sheet->setCellValue('C3', "Kategori");
            $sheet->setCellValue('D3', "Barang");
            $sheet->setCellValue('E3', "Batch Number");
            $sheet->setCellValue('F3', "Qty");
            $sheet->setCellValue('G3', "Unit");
            $sheet->setCellValue('H3', "Gudang");
            $sheet->setCellValue('I3', "No. PO Customer");
            $sheet->setCellValue('J3', "No Retur Barang");
            $sheet->setCellValue('K3', "Nota Retur Pajak");
            $sheet->setCellValue('L3', "Tanggal Datang");
            $sheet->setCellValue('M3', "Tanggal Kadaluarsa");
            
            $j  = 4;
            $no = 1;
            if(count($invitemstock)==0){
                $lastno = 2;
                $lastj = 4;
               }else{
            foreach($invitemstock as $key => $val){
                $sheet = $spreadsheet->getActiveSheet(0);
                $spreadsheet->getActiveSheet()->setTitle("Stock Barang");
                $spreadsheet->getActiveSheet()->getStyle('B'.$j.':M'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->setCellValue('B'.$j, $no);
                $sheet->setCellValue('C'.$j, $this->getInvItemCategoryName($val['item_category_id']));
                $sheet->setCellValue('D'.$j, $this->getInvItemTypeName($val['item_type_id']));
                $sheet->setCellValue('E'.$j, $val['item_batch_number']);
                $sheet->setCellValue('F'.$j, $val['item_total']);
                $sheet->setCellValue('G'.$j, $this->getInvItemUnitName($val['item_unit_id']));
                $sheet->setCellValue('H'.$j, $this->getInvWarehouseName($val['warehouse_id']));
                $sheet->setCellValue('I'.$j, $val['purchase_order_no']);
                $sheet->setCellValue('J'.$j, $val['no_retur_barang']);
                $sheet->setCellValue('K'.$j, $val['nota_retur_pajak']);
                $sheet->setCellValue('L'.$j, date('Y-m-d', strtotime($val['item_stock_date'])));
                if($val['item_stock_expired_date'] == '0000-00-00'){
                    $sheet->setCellValue('M'.$j, '-');
                }else{
                    $sheet->setCellValue('M'.$j, date('Y-m-d', strtotime($val['item_stock_expired_date'])));
                }
                

                    
                $j++;
                $lastno = $no;
                $lastj = $j;
            }

           
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->getStyle('B'.$lastj.':M'.$lastj)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->setCellValue('B' . $lastj , 'Jumlah Total:');
            $sumrangeQty = 'F'. $lastno - 1 .':F'.$j;
            $sheet->setCellValue('H' . $lastj , '=SUM('.$sumrangeQty.')');

            $sheet->setCellValue('F' . $lastj + 1, 'Mengetahui');
            $sheet->setCellValue('K' . $lastj + 1, 'Dibuat Oleh');


            $spreadsheet->getActiveSheet()->getStyle('E'.$lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('H'.$lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('K'.$lastj + 5)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
           
           
            $sheet->setCellValue('E' . $lastj + 5, 'Apoteker');
            $sheet->setCellValue('H' . $lastj + 5, 'Administrasi Pajak');
            $sheet->setCellValue('K' . $lastj + 5, 'Dibuat Oleh');

        }
        
            ob_clean();
            $filename='Stock Barang '.date('d M Y').'.xls';
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
