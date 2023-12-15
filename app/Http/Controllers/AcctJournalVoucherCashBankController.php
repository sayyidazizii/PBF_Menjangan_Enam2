<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\CoreBranch;
use App\Models\CoreProject;
use App\Models\User;
use App\Models\PreferenceTransactionModule;
use App\Models\AcctAccountType;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AcctJournalVoucherCashBankController extends Controller
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
        Session::forget('acctjournalvoucherelements');
        Session::forget('dataarrayjournalvoucher');
        Session::forget('dataprocessjournalvoucher');
        $branch         = CoreBranch::where('data_state','=',0)->get();

        if(!Session::get('start_date')){
            $start_date     = date('Y-m-d');
        }else{
            $start_date     = Session::get('start_date');
        }

        if(!Session::get('end_date')){
            $end_date       = date('Y-m-d');
        }else{
            $end_date       = Session::get('end_date');
        }

        if(!Session::get('branch_id')){
            $branch_id      = $branch[0]['branch_id'];
        }else{
            $branch_id      = Session::get('branch_id');
        }
        
        $corebranch         = CoreBranch::where('core_branch.data_state','=','0')->get()->pluck('branch_name','branch_id');

        $acctjournalvoucher = AcctJournalVoucherItem::select(DB::raw("acct_journal_voucher_item.journal_voucher_item_id, acct_journal_voucher.journal_voucher_description, acct_journal_voucher.journal_voucher_title, acct_journal_voucher.project_type_id, acct_journal_voucher.project_id, acct_journal_voucher_item.journal_voucher_debit_amount, acct_journal_voucher_item.journal_voucher_credit_amount, acct_journal_voucher_item.account_id, acct_account.account_code, acct_account.account_name, acct_journal_voucher_item.account_id_status, acct_journal_voucher.transaction_module_code, acct_journal_voucher.journal_voucher_date, acct_journal_voucher.journal_voucher_id, acct_journal_voucher_item.journal_voucher_description AS journal_voucher_description_item"))
        ->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id','=','acct_journal_voucher_item.journal_voucher_id')
        ->join('acct_account','acct_account.account_id','=','acct_journal_voucher_item.account_id')
        ->where('acct_journal_voucher.journal_voucher_date','>=',$start_date)
        ->where('acct_journal_voucher.journal_voucher_date','<=',$end_date)
        // ->where('acct_journal_voucher.branch_id','=',$branch_id)
        ->where('acct_journal_voucher.data_state','=',0)
        ->where('acct_journal_voucher.transaction_module_code','=','PP')
        ->where('acct_journal_voucher_item.data_state','=',0)
        // ->where('acct_journal_voucher_item.journal_voucher_amount','<>',0)
        ->orderBy('acct_journal_voucher_item.journal_voucher_item_id', 'ASC')
        ->get();
       // dd($acctjournalvoucher);
        return view('content/AcctJournalVoucher/ListAcctJournalVoucherCashBank', compact('acctjournalvoucher','start_date','end_date','corebranch','branch_id'));
    }

    public function filterAcctJournalVoucher(Request $request){
        $start_date     = $request->start_date;
        $end_date       = $request->end_date;
        $branch_id      = $request->branch_id;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('branch_id', $branch_id);

        return redirect('/journal-CashBank');
    }

    public function getMinID($journal_voucher_id){
        $id = AcctJournalVoucherItem::select('journal_voucher_item_id')
        ->where('journal_voucher_id', $journal_voucher_id)
        ->orderBy('journal_voucher_item_id', 'asc')
        ->first();
        
        return $id['journal_voucher_item_id'];
    }

    public function getProjectName($project_id){
        $project_name = CoreProject::select('project_name')
        ->where('project_id','=',$project_id)
        ->first();

        if($project_name == null){
            return "-";
        }
        return $project_name['project_name'];
    }

    public function getAccountCode($account_id){
        $account_code = AcctAccount::select('account_code')
        ->where('account_id','=',$account_id)
        ->first();
        return $account_code['account_code'];
    }

    public function getAccountName($account_id){
        $account_name = AcctAccount::select('account_name')
        ->where('account_id','=',$account_id)
        ->first();
        return $account_name['account_name'];
    }

    public function addJournalVoucher()
    {
        $acctjournalvoucherelements= Session::get('acctjournalvoucherelements');
        $acctjournalvoucheritem = Session::get('dataarrayjournalvoucher');
        $accountstatus = array (
            array(  'account_default_status'	=> '1',
                    'account_default_name'	    => 'Debit',
            ),
            array(  'account_default_status'	=> '0',
                    'account_default_name'	    => 'Kredit',
            ),
        );
        $acctaccount            = AcctAccount::where('acct_account.data_state','=','0')
        ->where('acct_account.parent_account_status','=','0')
        ->get();

        $coreproject            = CoreProject::where('core_project.data_state','=','0')
        ->get();
        // print_r($acctjournalvoucherelements);exit;
        return view('content/AcctJournalVoucher/FormAddAcctJournalVoucher', compact('accountstatus', 'acctaccount', 'coreproject', 'acctjournalvoucheritem', 'acctjournalvoucherelements'));
    }

    public function elements_add(Request $request){
        $acctjournalvoucherelements= Session::get('acctjournalvoucherelements');
        if(!$acctjournalvoucherelements || $acctjournalvoucherelements == ''){
            $acctjournalvoucherelements['journal_voucher_date']         = '';
            $acctjournalvoucherelements['journal_voucher_description']  = '';
        }
        $acctjournalvoucherelements[$request->name] = $request->value;
        Session::put('acctjournalvoucherelements', $acctjournalvoucherelements);
    }

    public function addArrayJournalVoucher(Request $request)
    {
        $date = date('YmdHis');
        $dataarrayjournalvoucher = array(
            'record_id'								=> $date.$request->account_id,
            'project_id'							=> $request->project_id,
            'account_id'							=> $request->account_id,
            'journal_voucher_date'				    => $request->journal_voucher_date,
            'journal_voucher_status'				=> $request->journal_voucher_status,
            'journal_voucher_amount'				=> $request->journal_voucher_amount,
            'journal_voucher_description'		    => $request->journal_voucher_description,
            'journal_voucher_description_item'		=> $request->journal_voucher_description_item,
        );

        $lastdataarrayjournalvoucher = Session::get('dataarrayjournalvoucher');
        if($lastdataarrayjournalvoucher !== null){
            array_push($lastdataarrayjournalvoucher, $dataarrayjournalvoucher);
            Session::put('dataarrayjournalvoucher', $lastdataarrayjournalvoucher);
        }else{
            $lastdataarrayjournalvoucher = [];
            array_push($lastdataarrayjournalvoucher, $dataarrayjournalvoucher);
            Session::push('dataarrayjournalvoucher', $dataarrayjournalvoucher);
        }
        
        Session::forget('dataprocessjournalvoucher');
        Session::put('dataprocessjournalvoucher', $dataarrayjournalvoucher);
        return redirect('/journal/add');
    }


    public function deleteArrayJournalVoucher($record_id)
    {
        $arrayBaru			= array();
        $dataArrayHeader	= Session::get('dataarrayjournalvoucher');
        
        foreach($dataArrayHeader as $key=>$val){
            if($key != $record_id){
                $arrayBaru[$key] = $val;
            }
        }
        Session::forget('dataarrayjournalvoucher');
        Session::put('dataarrayjournalvoucher', $arrayBaru);

        return redirect('/journal/add');
    }
		
    public function processAddAcctJournalVoucher(Request $request){
        $acctjournalvoucheritem = Session::get('dataarrayjournalvoucher');
        $acctjournalvoucherprocess = Session::get('dataprocessjournalvoucher');

        $journal_voucher_period = date("Ym", strtotime($acctjournalvoucherprocess['journal_voucher_date']));

        $transaction_module_code = "JU";

        $transaction_module_id 		= PreferenceTransactionModule::where('transaction_module_code','=',$transaction_module_code)->first();
        $branch_id = User::select('branch_id')->where('user_id','=',Auth::id())->first();

        $data = array(
            'branch_id'						=> $branch_id['branch_id'],
            'journal_voucher_period' 		=> $journal_voucher_period,
            'journal_voucher_date'			=> $request->journal_voucher_date,
            'journal_voucher_title'			=> $request->journal_voucher_description,
            'journal_voucher_description'	=> $request->journal_voucher_description,
            'journal_voucher_token'			=> md5(rand()),
            'transaction_module_id'			=> $transaction_module_id['transaction_module_id'],
            'transaction_module_code'		=> $transaction_module_code,
            'created_id'					=> Auth::id(),
            'created_on'					=> date('Y-m-d H:i:s'),
        );

        // print_r($data);exit;

        $journal_voucher_token = AcctJournalVoucher::where('journal_voucher_token','=', $data['journal_voucher_token'])->get();

        if(!empty($acctjournalvoucheritem)){
            if(count($journal_voucher_token) == 0){
                if(AcctJournalVoucher::create($data)){
                    $journal_voucher_id = AcctJournalVoucher::select('journal_voucher_id')
                    ->where('created_id','=', $data['created_id'])
                    ->orderBy('journal_voucher_id', 'DESC')
                    ->first();

                    foreach ($acctjournalvoucheritem as $key => $val) {
                        
                        if($val['journal_voucher_status'] == 1){
                            $data_debet =array(
                                'journal_voucher_id'			=> $journal_voucher_id['journal_voucher_id'],
                                'account_id'					=> $val['account_id'],
                                'journal_voucher_description'	=> $val['journal_voucher_description_item'],
                                'journal_voucher_amount'		=> $val['journal_voucher_amount'],
                                'journal_voucher_debit_amount'	=> $val['journal_voucher_amount'],
                                'account_id_status'				=> 1,
                                'journal_voucher_item_token'	=> $data['journal_voucher_token'].$val['record_id'],
                            );

                            AcctJournalVoucherItem::create($data_debet);
                        } else {
                            $data_credit =array(
                                'journal_voucher_id'			=> $journal_voucher_id['journal_voucher_id'],
                                'account_id'					=> $val['account_id'],
                                'journal_voucher_description'	=> $val['journal_voucher_description_item'],
                                'journal_voucher_amount'		=> $val['journal_voucher_amount'],
                                'journal_voucher_credit_amount'	=> $val['journal_voucher_amount'],
                                'account_id_status'				=> 0,
                                'journal_voucher_item_token'	=> $data['journal_voucher_token'].$val['record_id'],
                            );

                            AcctJournalVoucherItem::create($data_credit);
                        }
                    }


                    $msg = 'Tambah Data Jurnal Umum Berhasil';
                    return redirect('/journal')->with('msg',$msg);
                }else{
                    $msg = 'Tambah Data Jurnal Umum Gagal';
                    return redirect('/journal/add')->with('msg',$msg);
                }
            } else {
                $journal_voucher_id = AcctJournalVoucher::select('journal_voucher_id')
                ->where('created_id','=', $data['created_id'])
                ->orderBy('journal_voucher_id', 'DESC')
                ->first();

                foreach ($acctjournalvoucheritem as $key => $val) {
                    if($val['journal_voucher_status'] == 1){
                        $data_debet =array(
                            'journal_voucher_id'			=> $journal_voucher_id['journal_account_id'],
                            'account_id'					=> $val['account_id'],
                            'journal_voucher_description'	=> $val['journal_voucher_description_item'],
                            'journal_voucher_amount'		=> $val['journal_voucher_amount'],
                            'journal_voucher_debit_amount'	=> $val['journal_voucher_amount'],
                            'account_id_status'				=> 1,
                            'journal_voucher_item_token'	=> $data['journal_voucher_token'].$val['account_id'],
                        );

                        $journal_voucher_item_token = AcctJournalVoucherItem::where('journal_voucher_item_token','=', $data_debet['journal_voucher_item_token'])->get();

                        if(count($journal_voucher_item_token) == 0){
                            AcctJournalVoucherItem::create($data_debet);
                        }
                        
                    } else {
                        $data_credit =array(
                            'journal_voucher_id'			=> $journal_voucher_id['journal_account_id'],
                            'account_id'					=> $val['account_id'],
                            'journal_voucher_description'	=> $val['journal_voucher_description_item'],
                            'journal_voucher_amount'		=> $val['journal_voucher_amount'],
                            'journal_voucher_credit_amount'	=> $val['journal_voucher_amount'],
                            'account_id_status'				=> 0,
                            'journal_voucher_item_token'	=> $data['journal_voucher_token'].$val['account_id'],
                        );

                        $journal_voucher_item_token = AcctJournalVoucherItem::where('journal_voucher_item_token','=', $data_credit['journal_voucher_item_token'])->get();

                        if(count($journal_voucher_item_token) == 0){
                            AcctJournalVoucherItem::create($data_credit);
                        }
                    }
                }

                $msg = 'Tambah Data Jurnal Umum Berhasil';
                return redirect('/journal')->with('msg',$msg);
            }
            
        } else {
            $msg = 'Tambah Data Jurnal Umum Gagal';
            return redirect('/journal/add')->with('msg',$msg);
        }
    }
    
    public function processPrintingAcctJournalVoucher ()
    {
        $branch         = CoreBranch::where('data_state','=',0)->get();

        if(!Session::get('start_date')){
            $start_date     = date('Y-m-d');
        }else{
            $start_date     = Session::get('start_date');
        }

        if(!Session::get('end_date')){
            $end_date       = date('Y-m-d');
        }else{
            $end_date       = Session::get('end_date');
        }

        if(!Session::get('branch_id')){
            $branch_id      = $branch[0]['branch_id'];
        }else{
            $branch_id      = Session::get('branch_id');
        }

        $acctjournalvoucher = AcctJournalVoucherItem::select(DB::raw("acct_journal_voucher_item.journal_voucher_item_id, acct_journal_voucher.journal_voucher_description, acct_journal_voucher.journal_voucher_title, acct_journal_voucher.project_type_id, acct_journal_voucher.project_id, acct_journal_voucher_item.journal_voucher_debit_amount, acct_journal_voucher_item.journal_voucher_credit_amount, acct_journal_voucher_item.account_id, acct_account.account_code, acct_account.account_name, acct_journal_voucher_item.account_id_status, acct_journal_voucher.transaction_module_code, acct_journal_voucher.journal_voucher_date, acct_journal_voucher.journal_voucher_id, acct_journal_voucher_item.journal_voucher_description AS journal_voucher_description_item"))
        ->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id','=','acct_journal_voucher_item.journal_voucher_id')
        ->join('acct_account','acct_account.account_id','=','acct_journal_voucher_item.account_id')
        ->where('acct_journal_voucher.journal_voucher_date','>=',$start_date)
        ->where('acct_journal_voucher.journal_voucher_date','<=',$end_date)
        ->where('acct_journal_voucher.transaction_module_code','=','PP')
        // ->where('acct_journal_voucher.branch_id','=',$branch_id)
        // ->where('acct_journal_voucher.journal_voucher_type_id','=',4)
        ->where('acct_journal_voucher.data_state','=',0)
        ->where('acct_journal_voucher_item.journal_voucher_amount','<>',0)
        ->orderBy('acct_journal_voucher_item.journal_voucher_item_id', 'ASC')
        ->get();

        // print_r($acctjournalvoucher);exit;

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
                <td><div style=\"text-align: center; font-size:14px; font-weight: bold\">JURNAL Kas Dan Bank</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center; font-size:12px\">PERIODE : ".date('d M Y', strtotime($start_date))." s.d. ".date('d M Y', strtotime($end_date))."</div></td>
            </tr>
        </table>
        ";
        $pdf::writeHTML($tbl, true, false, false, false, '');
        
        $no = 1;
        $tblStock1 = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
            <tr>
                <td width=\"4%\" ><div style=\"text-align: center;\">No</div></td>
                <td width=\"9%\" ><div style=\"text-align: center;\">Tanggal</div></td>
                <td width=\"15%\" ><div style=\"text-align: center;\">Uraian</div></td>
                <td width=\"18%\" ><div style=\"text-align: center;\">Deskripsi </div></td>
                <td width=\"9%\" ><div style=\"text-align: center;\">No. Perkiraan </div></td>
                <td width=\"18%\" ><div style=\"text-align: center;\">Perkiraan </div></td>
                <td width=\"14%\" ><div style=\"text-align: center;\">Debet </div></td>
                <td width=\"14%\" ><div style=\"text-align: center;\">Kredit </div></td>
            </tr>
        
             ";

        $totaldebet     = 0;
        $totalkredit    = 0;
        $no = 1;
        $tblStock2 =" ";
        foreach ($acctjournalvoucher as $key => $val) {
            $id = $this->getMinID($val['journal_voucher_id']);
            $project_name = $this->getProjectName($val['project_id']);

            if($val['journal_voucher_debit_amount'] <> 0 ){
                $debet = number_format($val['journal_voucher_debit_amount'], 2,',','.');
                $kredit = " ";
            } else if($val['journal_voucher_credit_amount'] <> 0){
                $kredit = number_format($val['journal_voucher_credit_amount'], 2,',','.');
                $debet = " ";
            } else {
                $kredit = " ";
                $debet = " ";
            }

            if($val['journal_voucher_item_id'] == $id){
                $tblStock2 .="
                    <tr>			
                        <td style=\"text-align:center\">$no.</td>
                        <td style=\"text-align:center\">".$val['journal_voucher_date']."</td>
                        <td>(".$project_name.") ".$val['journal_voucher_title']."</td>
                        <td> ".$val['journal_voucher_description_item']."</td>
                        <td><div style=\"text-align: left;\"> ".$val['account_code']."</div></td>
                        <td><div style=\"text-align: left;\"> ".$val['account_name']."</div></td>
                        <td><div style=\"text-align: right;\">".$debet."</div></td>
                        <td><div style=\"text-align: right;\">".$kredit."</div></td>
                    </tr>
                    
                ";
                $no++;
            } else {
                $tblStock2 .="
                    <tr>			
                        <td style=\"text-align:center\"></td>
                        <td style=\"text-align:center\"></td>
                        <td></td>
                        <td>".$val['journal_voucher_description_item']."</td>
                        <td><div style=\"text-align: left;\">&nbsp;&nbsp;&nbsp;&nbsp;".$val['account_code']."</div></td>
                        <td><div style=\"text-align: left;\">&nbsp;&nbsp;&nbsp;&nbsp;".$val['account_name']."</div></td>
                        <td><div style=\"text-align: right;\">".$debet."</div></td>
                        <td><div style=\"text-align: right;\">".$kredit."</div></td>
                    </tr>
                    
                ";
            }

            $totaldebet 	+= $val['journal_voucher_debit_amount'];
            $totalkredit 	+= $val['journal_voucher_credit_amount'];
        }
        $tblStock4 = " 
            <tr>	
                <td colspan=\"6\"><div style=\"text-align: left;\"></div></td>
                <td><div style=\"text-align: right;font-weight:bold\">".number_format($totaldebet, 2,',','.')."</div></td>
                <td><div style=\"text-align: right;font-weight:bold\">".number_format($totalkredit, 2,',','.')."</div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tblStock1.$tblStock2.$tblStock4, true, false, false, false, '');

        // ob_clean();

        $filename = 'Jurnal_KasDanBank_'.$start_date.'s.d.'.$end_date.'.pdf';
        $pdf::Output($filename, 'I');
    }

    public function processExportAcctJournalVoucher()
    {
        $branch         = CoreBranch::where('data_state','=',0)->get();

        if(!Session::get('start_date')){
            $start_date     = date('Y-m-d');
        }else{
            $start_date     = Session::get('start_date');
        }

        if(!Session::get('end_date')){
            $end_date       = date('Y-m-d');
        }else{
            $end_date       = Session::get('end_date');
        }

        if(!Session::get('branch_id')){
            $branch_id      = $branch[0]['branch_id'];
        }else{
            $branch_id      = Session::get('branch_id');
        }
        
        $corebranch         = CoreBranch::where('core_branch.data_state','=','0')->get()->pluck('branch_name','branch_id');

        $acctjournalvoucher = AcctJournalVoucherItem::select(DB::raw("acct_journal_voucher_item.journal_voucher_item_id, acct_journal_voucher.journal_voucher_description, acct_journal_voucher.journal_voucher_title, acct_journal_voucher.project_type_id, acct_journal_voucher.project_id, acct_journal_voucher_item.journal_voucher_debit_amount, acct_journal_voucher_item.journal_voucher_credit_amount, acct_journal_voucher_item.account_id, acct_account.account_code, acct_account.account_name, acct_journal_voucher_item.account_id_status, acct_journal_voucher.transaction_module_code, acct_journal_voucher.journal_voucher_date, acct_journal_voucher.journal_voucher_id, acct_journal_voucher_item.journal_voucher_description AS journal_voucher_description_item"))
        ->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id','=','acct_journal_voucher_item.journal_voucher_id')
        ->join('acct_account','acct_account.account_id','=','acct_journal_voucher_item.account_id')
        ->where('acct_journal_voucher.journal_voucher_date','>=',$start_date)
        ->where('acct_journal_voucher.journal_voucher_date','<=',$end_date)
        // ->where('acct_journal_voucher.branch_id','=',$branch_id)
        // ->where('acct_journal_voucher.journal_voucher_type_id','=',4)
        ->where('acct_journal_voucher.transaction_module_code','=','PP')
        ->where('acct_journal_voucher.data_state','=',0)
        ->where('acct_journal_voucher_item.journal_voucher_amount','<>',0)
        ->orderBy('acct_journal_voucher_item.journal_voucher_item_id', 'ASC')
        ->get();
        
        $spreadsheet = new Spreadsheet();

        if(count($acctjournalvoucher)>=0){
            $spreadsheet->getProperties()->setCreator("KAROTA KING")
                                         ->setLastModifiedBy("KAROTA KING")
                                         ->setTitle("Jurnal KasDanBank")
                                         ->setSubject("")
                                         ->setDescription("Jurnal KasDanBank")
                                         ->setKeywords("Jurnal KasDanBank")
                                         ->setCategory("Jurnal KasDanBank");
                                 
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Jurnal KasDanBank");
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
    
            $spreadsheet->getActiveSheet()->mergeCells("B1:I1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);

            $spreadsheet->getActiveSheet()->getStyle('B3:I3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:I3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);




            
            $sheet->setCellValue('B1',"Jurnal KasDanBank Dari Periode ".date('d M Y', strtotime($start_date))." s.d. ".date('d M Y', strtotime($end_date)));	
            $sheet->setCellValue('B3',"No");
            $sheet->setCellValue('C3',"Tanggal");
            $sheet->setCellValue('D3',"Uraian");
            $sheet->setCellValue('E3',"Deskripsi");
            $sheet->setCellValue('F3',"No. Perkiraan");
            $sheet->setCellValue('G3',"Perkiraan");
            $sheet->setCellValue('H3',"Debet");
            $sheet->setCellValue('I3',"Kredit");

        
            
            
            $j=4;
            $no=0;
            
            foreach($acctjournalvoucher as $key=>$val){

                if(is_numeric($key)){
                    
                    $sheet = $spreadsheet->getActiveSheet(0);
                    $spreadsheet->getActiveSheet()->setTitle("Jurnal KasDanBank");
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j.':I'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    $spreadsheet->getActiveSheet()->getStyle('H'.$j.':I'.$j)->getNumberFormat()->setFormatCode('0.00');
            
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                    $id = $this->getMinID($val['journal_voucher_id']);
                    $project_name = $this->getProjectName($val['project_id']);

                    if($val['journal_voucher_debit_amount'] <> 0 ){
                        $debet = number_format($val['journal_voucher_debit_amount'], 2,',','.');
                        $kredit = " ";
                    } else if($val['journal_voucher_credit_amount'] <> 0){
                        $kredit = number_format($val['journal_voucher_credit_amount'], 2,',','.');
                        $debet = " ";
                    } else {
                        $kredit = " ";
                        $debet = " ";
                    }

                    if($val['journal_voucher_item_id'] == $id){

                        $no++;
                        $sheet->setCellValue('B'.$j, $no);
                        $sheet->setCellValue('C'.$j, $val['journal_voucher_date']);
                        $sheet->setCellValue('D'.$j, "(".$project_name.") ".$val['journal_voucher_title']);
                        $sheet->setCellValue('E'.$j, $val['journal_voucher_description_item']);
                        $sheet->setCellValue('F'.$j, $val['account_code']);
                        $sheet->setCellValue('G'.$j, $val['account_name']);
                        $sheet->setCellValue('H'.$j, $debet);
                        $sheet->setCellValue('I'.$j, $kredit);
                    } else {
                        $sheet->setCellValue('B'.$j, "");
                        $sheet->setCellValue('C'.$j, "");
                        $sheet->setCellValue('D'.$j, "");
                        $sheet->setCellValue('E'.$j, $val['journal_voucher_description_item']);
                        $sheet->setCellValue('F'.$j, $val['account_code']);
                        $sheet->setCellValue('G'.$j, $val['account_name']);
                        $sheet->setCellValue('H'.$j, $debet);
                        $sheet->setCellValue('I'.$j, $kredit);
                    }
                        
                        
                    
                }else{
                    continue;
                }
                $j++;
        
            }
            
            // ob_clean();
            $filename='Jurnal_KasDanBank_'.$start_date.'_s.d._'.$end_date.'.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }else{
            echo "Maaf data yang di eksport tidak ada !";
        }

    }
    
    public function getDefaultStatus($account_default_status)
    {
        $default_status = array (
            '1'	=> 'Debit',
            '0'	=> 'Kredit',
        );
        return $default_status[$account_default_status];
    }
}
