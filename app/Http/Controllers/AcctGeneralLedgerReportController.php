<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\AcctAccount;
use App\Models\AcctAccountBalanceDetail;
use App\Models\AcctAccountOpeningBalance;
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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LedgerExport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AcctGeneralLedgerReportController extends Controller
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
        $branch         = CoreBranch::where('data_state','=',0)->get();

        if(!Session::get('month_period')){
            $month_period   = date('m');
        }else{
            $month_period   = Session::get('month_period');
        }

        if(!Session::get('year_period')){
            $year_period    = date('Y');
        }else{
            $year_period    = Session::get('year_period');
        }

        if(!Session::get('branch_id')){
            $branch_id      = $branch[0]['branch_id'];
        }else{
            $branch_id      = Session::get('branch_id');
        }

        if(!Session::get('account_id')){
            $account_id     = '';
        }else{
            $account_id     = Session::get('account_id');
        }

        $accountname        = AcctAccount::where('acct_account.data_state','=','0')
        ->where('account_id','=',$account_id)
        ->select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))
        ->first();

        $opening_balance 	= AcctAccountOpeningBalance::select('opening_balance')
        ->whereMonth('month_period','=',$month_period)
        ->whereYear('year_period','=',$year_period)
        ->where('account_id','=',$account_id)
        ->where('branch_id','=',$branch_id)
        ->orderBy('acct_account_opening_balance.account_opening_balance_id', 'DESC')
        ->first();

        if(empty($opening_balance)){
            $opening_balance 	= AcctAccountOpeningBalance::select('opening_balance')
            ->where('account_id','=',$account_id)
            ->where('branch_id','=',$branch_id)
            ->orderBy('acct_account_opening_balance.account_opening_balance_id', 'DESC')
            ->first();
        }
        
        $account_id_status 		= AcctAccount::select('account_default_status')
        ->where('account_id','=',$account_id)
        ->where('data_state','=',0)
        ->first();

        if(!empty($month_period)){
            $accountbalancedetail	= AcctAccountBalanceDetail::select('acct_account_balance_detail.account_balance_detail_id', 
            'acct_account_balance_detail.transaction_type', 'acct_account_balance_detail.transaction_code', 
            'acct_account_balance_detail.transaction_date', 'acct_account_balance_detail.transaction_id', 
            'acct_account_balance_detail.account_id', 'acct_account.account_code', 'acct_account.account_name',
            'acct_account_balance_detail.opening_balance', 'acct_account_balance_detail.account_in', 
            'acct_account_balance_detail.account_out', 'acct_account_balance_detail.last_balance',
            'acct_journal_voucher.journal_voucher_no','acct_journal_voucher_item.journal_voucher_description',
            'acct_journal_voucher.journal_voucher_id','acct_journal_voucher.data_state')

            ->join('acct_account','acct_account.account_id','=','acct_account_balance_detail.account_id')
            ->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->join('acct_journal_voucher_item','acct_journal_voucher_item.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->where('acct_account_balance_detail.account_id','=',$account_id)
            ->where('acct_account_balance_detail.branch_id','=',$branch_id)
            ->whereMonth('acct_account_balance_detail.transaction_date','=',$month_period)
            ->whereYear('acct_account_balance_detail.transaction_date','=',$year_period)
            ->orderBy('acct_account_balance_detail.transaction_date', 'ASC')
            ->orderBy('acct_account_balance_detail.account_balance_detail_id', 'ASC')
            ->distinct()
            ->get();
        }else{
            $accountbalancedetail	= AcctAccountBalanceDetail::select('acct_account_balance_detail.account_balance_detail_id', 
            'acct_account_balance_detail.transaction_type', 'acct_account_balance_detail.transaction_code', 
            'acct_account_balance_detail.transaction_date', 'acct_account_balance_detail.transaction_id', 
            'acct_account_balance_detail.account_id', 'acct_account.account_code', 'acct_account.account_name',
            'acct_account_balance_detail.opening_balance', 'acct_account_balance_detail.account_in', 
            'acct_account_balance_detail.account_out', 'acct_account_balance_detail.last_balance',
            'acct_journal_voucher.journal_voucher_no','acct_journal_voucher_item.journal_voucher_description',
            'acct_journal_voucher.journal_voucher_id','acct_journal_voucher.data_state')

            ->join('acct_account','acct_account.account_id','=','acct_account_balance_detail.account_id')
            ->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->join('acct_journal_voucher_item','acct_journal_voucher_item.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->where('acct_account_balance_detail.account_id','=',$account_id)
            ->where('acct_account_balance_detail.branch_id','=',$branch_id)
            ->whereMonth('acct_account_balance_detail.transaction_date','=',$month_period)
            ->whereYear('acct_account_balance_detail.transaction_date','=',$year_period)
            ->orderBy('acct_account_balance_detail.transaction_date', 'ASC')
            ->orderBy('acct_account_balance_detail.account_balance_detail_id', 'ASC')
            ->distinct()
            ->get();
        }


        if(!empty($accountbalancedetail)){
            $acctgeneralledgerreport = array ();
            if($opening_balance){
                $last_balance 		= $opening_balance['opening_balance'];
            }else{
                $last_balance 		= 0;
            }
            foreach ($accountbalancedetail as $key => $val) {
                // $description 	= AcctJournalVoucherItem::select('journal_voucher_description')
                // ->where('journal_voucher_id','=',$val['transaction_id'])
                // ->where('account_id','=',$val['account_id'])
                // ->first();      

                // $journal_no 	= AcctJournalVoucher::select('journal_voucher_no')
                // ->where('journal_voucher_id','=',$val['transaction_id'])
                // ->first();

                // $data_state 	= AcctJournalVoucher::select('data_state')
                // ->where('journal_voucher_id','=',$val['transaction_id'])
                // ->first();

                $last_balance = ($last_balance + $val['account_in']) - $val['account_out'];
                // $last_balance = 0;

                if($account_id_status['account_default_status'] == 1 ){
                    $debet 	= $val['account_in'];
                    $kredit = $val['account_out'];

                    if($last_balance >= 0){
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    } else {
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    }
                } else {
                    $debet 	= $val['account_out'];
                    $kredit = $val['account_in'];

                    if($last_balance >= 0){
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    } else {
                        
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    }
                }

                

                $data_acctgeneralledgerreport = array (
                    'transaction_date'			=> $val['transaction_date'],
                    // 'transaction_no'			=> $journal_no['journal_voucher_no'],
                    // 'transaction_no'			=> $journal_no['journal_voucher_no'],
                    // 'transaction_description'	=> $description['journal_voucher_description'],
                     'transaction_no'			=> $val['journal_voucher_no'],
                     'transaction_description'	=> $val['journal_voucher_description'],
                    'account_name'				=> $accountname['full_name'],
                    'account_in'				=> $debet,
                    'account_out'				=> $kredit,
                    'last_balance_debet'		=> $last_balance_debet,
                    'last_balance_credit'		=> $last_balance_kredit,
                    // 'data_state'				=> $data_state['data_state'],
                     'data_state'				=> $val['data_state'],
                );
                array_push($acctgeneralledgerreport, $data_acctgeneralledgerreport);
            }
        } else {
            $acctgeneralledgerreport = array ();
        }
        
        // $opening_balance            = $opening_balance['opening_balance'];
        if($opening_balance){
            $last_balance 		= $opening_balance['opening_balance'];
        }else{
            $last_balance 		= 0;
        }

        if($account_id_status){
            $account_id_status	        = $account_id_status['account_default_status'];
        }else{
            $account_id_status	        = 1;
        }
    
        $monthlist 		            = array (
                                        '01'		=> "January",
                                        '02'		=> "February",
                                        '03'		=> "March",
                                        '04'		=> "April",
                                        '05'		=> "May",
                                        '06'		=> "June",
                                        '07'		=> "July",
                                        '08'		=> "August",
                                        '09'		=> "September",
                                        '10'		=> "October",
                                        '11'		=> "November",
                                        '12'		=> "December",
                                    );
        
        $acctaccount		        = AcctAccount::where('data_state','=',0)
        ->where('parent_account_status','=',0)
        ->get()
        ->pluck('account_name','account_id');
        

        $year_now 	=	date('Y');
        for($i=($year_now-2); $i<($year_now+2); $i++){
            $year[$i] = $i;
        } 

        $corebranch         = CoreBranch::where('data_state','=',0)
        ->get()
        ->pluck('branch_name','branch_id');

        return view('content/AcctGeneralLedgerReport/ListAcctGeneralLedgerReport', compact('acctgeneralledgerreport','opening_balance','account_id_status','monthlist','acctaccount','corebranch', 'year', 'month_period', 'year_period', 'branch_id', 'account_id'));

    }

    public function filterAcctGeneralLedgerReport(Request $request){
        $month_id       = $request->month_id;
        $year_id        = $request->year_id;
        $account_id     = $request->account_id;
        $branch_id      = $request->branch_id;

        Session::put('month_period', $month_id);
        Session::put('year_period', $year_id);
        Session::put('account_id', $account_id);
        Session::put('branch_id', $branch_id);

        return redirect('/ledger');
    }

    public function processPrintingAcctGeneralLedgerReport(){
        $branch         = CoreBranch::where('data_state','=',0)->get();

        if(!Session::get('month_period')){
            $month_period   = date('m');
        }else{
            $month_period   = Session::get('month_period');
        }

        if(!Session::get('year_period')){
            $year_period    = date('Y');
        }else{
            $year_period    = Session::get('year_period');
        }

        if(!Session::get('branch_id')){
            $branch_id      = $branch[0]['branch_id'];
        }else{
            $branch_id      = Session::get('branch_id');
        }

        if(!Session::get('account_id')){
            $account_id     = '';
        }else{
            $account_id     = Session::get('account_id');
        }

        $accountname        = AcctAccount::where('acct_account.data_state','=','0')
        ->where('account_id','=',$account_id)
        ->select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))
        ->first();

        $opening_balance 	= AcctAccountOpeningBalance::select('opening_balance')
        ->whereMonth('month_period','=',$month_period)
        ->whereYear('year_period','=',$year_period)
        ->where('account_id','=',$account_id)
        ->where('branch_id','=',$branch_id)
        ->orderBy('acct_account_opening_balance.account_opening_balance_id', 'DESC')
        ->first();

        if(empty($opening_balance)){
            $opening_balance 	= AcctAccountOpeningBalance::select('opening_balance')
            ->where('account_id','=',$account_id)
            ->where('branch_id','=',$branch_id)
            ->orderBy('acct_account_opening_balance.account_opening_balance_id', 'DESC')
            ->first();
        }
        
        $account_id_status 		= AcctAccount::select('account_default_status')
        ->where('account_id','=',$account_id)
        ->where('data_state','=',0)
        ->first();

        if(!empty($month_period)){
            $accountbalancedetail	= AcctAccountBalanceDetail::select('acct_account_balance_detail.account_balance_detail_id', 
            'acct_account_balance_detail.transaction_type', 'acct_account_balance_detail.transaction_code', 
            'acct_account_balance_detail.transaction_date', 'acct_account_balance_detail.transaction_id', 
            'acct_account_balance_detail.account_id', 'acct_account.account_code', 'acct_account.account_name',
            'acct_account_balance_detail.opening_balance', 'acct_account_balance_detail.account_in', 
            'acct_account_balance_detail.account_out', 'acct_account_balance_detail.last_balance',
            'acct_journal_voucher.journal_voucher_no','acct_journal_voucher_item.journal_voucher_description',
            'acct_journal_voucher.journal_voucher_id','acct_journal_voucher.data_state')

            ->join('acct_account','acct_account.account_id','=','acct_account_balance_detail.account_id')
            ->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->join('acct_journal_voucher_item','acct_journal_voucher_item.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->where('acct_account_balance_detail.account_id','=',$account_id)
            ->where('acct_account_balance_detail.branch_id','=',$branch_id)
            ->whereMonth('acct_account_balance_detail.transaction_date','=',$month_period)
            ->whereYear('acct_account_balance_detail.transaction_date','=',$year_period)
            ->orderBy('acct_account_balance_detail.transaction_date', 'ASC')
            ->orderBy('acct_account_balance_detail.account_balance_detail_id', 'ASC')
            ->distinct()
            ->get();
        }else{
            $accountbalancedetail	= AcctAccountBalanceDetail::select('acct_account_balance_detail.account_balance_detail_id', 
            'acct_account_balance_detail.transaction_type', 'acct_account_balance_detail.transaction_code', 
            'acct_account_balance_detail.transaction_date', 'acct_account_balance_detail.transaction_id', 
            'acct_account_balance_detail.account_id', 'acct_account.account_code', 'acct_account.account_name',
            'acct_account_balance_detail.opening_balance', 'acct_account_balance_detail.account_in', 
            'acct_account_balance_detail.account_out', 'acct_account_balance_detail.last_balance',
            'acct_journal_voucher.journal_voucher_no','acct_journal_voucher_item.journal_voucher_description',
            'acct_journal_voucher.journal_voucher_id','acct_journal_voucher.data_state')

            ->join('acct_account','acct_account.account_id','=','acct_account_balance_detail.account_id')
            ->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->join('acct_journal_voucher_item','acct_journal_voucher_item.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->where('acct_account_balance_detail.account_id','=',$account_id)
            ->where('acct_account_balance_detail.branch_id','=',$branch_id)
            ->whereMonth('acct_account_balance_detail.transaction_date','=',$month_period)
            ->whereYear('acct_account_balance_detail.transaction_date','=',$year_period)
            ->orderBy('acct_account_balance_detail.transaction_date', 'ASC')
            ->orderBy('acct_account_balance_detail.account_balance_detail_id', 'ASC')
            ->distinct()
            ->get();
        }


        if(!empty($accountbalancedetail)){
            $acctgeneralledgerreport = array ();
            if($opening_balance){
                $last_balance 		= $opening_balance['opening_balance'];
            }else{
                $last_balance 		= 0;
            }
            foreach ($accountbalancedetail as $key => $val) {
                // $description 	= AcctJournalVoucherItem::select('journal_voucher_description')
                // ->where('journal_voucher_id','=',$val['transaction_id'])
                // ->where('account_id','=',$val['account_id'])
                // ->first();      

                // $journal_no 	= AcctJournalVoucher::select('journal_voucher_no')
                // ->where('journal_voucher_id','=',$val['transaction_id'])
                // ->first();

                // $data_state 	= AcctJournalVoucher::select('data_state')
                // ->where('journal_voucher_id','=',$val['transaction_id'])
                // ->first();

                $last_balance = ($last_balance + $val['account_in']) - $val['account_out'];
                // $last_balance = 0;

                if($account_id_status['account_default_status'] == 1 ){
                    $debet 	= $val['account_in'];
                    $kredit = $val['account_out'];

                    if($last_balance >= 0){
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    } else {
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    }
                } else {
                    $debet 	= $val['account_out'];
                    $kredit = $val['account_in'];

                    if($last_balance >= 0){
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    } else {
                        
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    }
                }

                

                $data_acctgeneralledgerreport = array (
                    'transaction_date'			=> $val['transaction_date'],
                    // 'transaction_no'			=> $journal_no['journal_voucher_no'],
                    // 'transaction_no'			=> $journal_no['journal_voucher_no'],
                    // 'transaction_description'	=> $description['journal_voucher_description'],
                     'transaction_no'			=> $val['journal_voucher_no'],
                     'transaction_description'	=> $val['journal_voucher_description'],
                    'account_name'				=> $accountname['full_name'],
                    'account_in'				=> $debet,
                    'account_out'				=> $kredit,
                    'last_balance_debet'		=> $last_balance_debet,
                    'last_balance_credit'		=> $last_balance_kredit,
                    // 'data_state'				=> $data_state['data_state'],
                     'data_state'				=> $val['data_state'],
                );
                array_push($acctgeneralledgerreport, $data_acctgeneralledgerreport);
            }
        } else {
            $acctgeneralledgerreport = array ();
        }
        
        // $opening_balance            = $opening_balance['opening_balance'];
        if($opening_balance){
            $last_balance 		= $opening_balance['opening_balance'];
        }else{
            $last_balance 		= 0;
        }

        if($account_id_status){
            $account_id_status	        = $account_id_status['account_default_status'];
        }else{
            $account_id_status	        = 1;
        }

        $monthname 		            = array (
            '01'		=> "January",
            '02'		=> "February",
            '03'		=> "March",
            '04'		=> "April",
            '05'		=> "May",
            '06'		=> "June",
            '07'		=> "July",
            '08'		=> "August",
            '09'		=> "September",
            '10'		=> "October",
            '11'		=> "November",
            '12'		=> "December",
        );

        //$account_id_status = $account_id_status['account_default_status'];

        $accounstatus   = array (
            array(  'account_default_status'	=> '1',
                    'account_default_name'	    => 'Debit',
            ),
            array(  'account_default_status'	=> '0',
                    'account_default_name'	    => 'Kredit',
            ),
        );


        

        // require_once('tcpdf/config/tcpdf_config.php');
        // require_once('tcpdf/tcpdf.php');
        
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
                <td><div style=\"text-align: center; font-size:14px; font-weight: bold\">BUKU BESAR</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center; font-size:12px\">PERIODE : ".$monthname[$month_period]." ".$year_period."</div></td>
            </tr>
        </table>
        ";
        $pdf::writeHTML($tbl, true, false, false, false, '');

        $tbl = "
        <br>
        <br>
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td width=\"20%\"><div style=\"text-align: lef=ft; font-size:12px;font-weight: bold\">Nama. Perkiraan</div></td>
                <td width=\"5%\"><div style=\"text-align: center; font-size:12px; font-weight: bold\">:</div></td>
                <td width=\"65%\"><div style=\"text-align: left; font-size:12px; font-weight: bold\">".$accountname['full_name']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: lef=ft; font-size:12px;font-weight: bold\">Posisi Saldo</div></td>
                <td width=\"5%\"><div style=\"text-align: center; font-size:12px; font-weight: bold\">:</div></td>
                <td width=\"65%\"><div style=\"text-align: left; font-size:12px; font-weight: bold\">".$accounstatus[$account_id_status]['account_default_name']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: lef=ft; font-size:12px;font-weight: bold\">Saldo Awal</div></td>
                <td width=\"5%\"><div style=\"text-align: center; font-size:12px; font-weight: bold\">:</div></td>
                <td width=\"65%\"><div style=\"text-align: left; font-size:12px; font-weight: bold\">".number_format($opening_balance, 2)."</div></td>
            </tr>
        </table>";
        $pdf::writeHTML($tbl, true, false, false, false, '');
        

        $no = 1;
        $tblStock1 = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
            <tr>
                <td width=\"5%\" rowspan=\"2\"><div style=\"text-align: center;\">No</div></td>
                <td width=\"12%\" rowspan=\"2\"><div style=\"text-align: center;\">Tanggal</div></td>
                <td width=\"25%\" rowspan=\"2\"><div style=\"text-align: center;\">Uraian</div></td>
                <td width=\"15%\" rowspan=\"2\"><div style=\"text-align: center;\">Debet </div></td>
                <td width=\"15%\" rowspan=\"2\"><div style=\"text-align: center;\">Kredit </div></td>
                <td width=\"30%\" colspan=\"2\"><div style=\"text-align: center;\">Saldo </div></td>
            </tr>
            
            <tr>
                <td width=\"15%\"><div style=\"text-align: center;\">Debet </div></td>
                <td width=\"15%\"><div style=\"text-align: center;\">Kredit </div></td>
            </tr>
        
             ";

        $tblStock2 = " ";
        $no = 1;
        foreach ($acctgeneralledgerreport as $key => $val) {
            $tblStock2 .="
                    <tr>			
                        <td style=\"text-align:center\">$no.</td>
                        <td style=\"text-align:center\">".$val['transaction_date']."</td>
                        <td>".$val['transaction_description']."</td>
                        <td><div style=\"text-align: right;\">".number_format($val['account_in'], 2)."</div></td>
                        <td><div style=\"text-align: right;\">".number_format($val['account_out'], 2)."</div></td>
                        <td><div style=\"text-align: right;\">".number_format($val['last_balance_debet'], 2)."</div></td>
                        <td><div style=\"text-align: right;\">".number_format($val['last_balance_credit'], 2)."</div></td>
                    </tr>
                    
                ";
            $no++;
        }
        $tblStock4 = " </table>";

        $pdf::writeHTML($tblStock1.$tblStock2.$tblStock4, true, false, false, false, '');
        
        // ob_clean();

        $filename = 'Buku_Besar_'.$accountname.'.pdf';
        $pdf::Output($filename, 'I');

        return redirect('/ledger');
    }
    
    public function processExportAcctGeneralLedgerReport ()
    {
        $branch         = CoreBranch::where('data_state','=',0)->get();

        if(!Session::get('month_period')){
            $month_period   = date('m');
        }else{
            $month_period   = Session::get('month_period');
        }

        if(!Session::get('year_period')){
            $year_period    = date('Y');
        }else{
            $year_period    = Session::get('year_period');
        }

        if(!Session::get('branch_id')){
            $branch_id      = $branch[0]['branch_id'];
        }else{
            $branch_id      = Session::get('branch_id');
        }

        if(!Session::get('account_id')){
            $account_id     = '';
        }else{
            $account_id     = Session::get('account_id');
        }

        $accountname        = AcctAccount::where('acct_account.data_state','=','0')
        ->where('account_id','=',$account_id)
        ->select('account_id', DB::raw('CONCAT(account_code, " - ", account_name) AS full_name'))
        ->first();

        $opening_balance 	= AcctAccountOpeningBalance::select('opening_balance')
        ->whereMonth('month_period','=',$month_period)
        ->whereYear('year_period','=',$year_period)
        ->where('account_id','=',$account_id)
        ->where('branch_id','=',$branch_id)
        ->orderBy('acct_account_opening_balance.account_opening_balance_id', 'DESC')
        ->first();

        if(empty($opening_balance)){
            $opening_balance 	= AcctAccountOpeningBalance::select('opening_balance')
            ->where('account_id','=',$account_id)
            ->where('branch_id','=',$branch_id)
            ->orderBy('acct_account_opening_balance.account_opening_balance_id', 'DESC')
            ->first();
        }
        
        $account_id_status 		= AcctAccount::select('account_default_status')
        ->where('account_id','=',$account_id)
        ->where('data_state','=',0)
        ->first();

        if(!empty($month_period)){
            $accountbalancedetail	= AcctAccountBalanceDetail::select('acct_account_balance_detail.account_balance_detail_id', 
            'acct_account_balance_detail.transaction_type', 'acct_account_balance_detail.transaction_code', 
            'acct_account_balance_detail.transaction_date', 'acct_account_balance_detail.transaction_id', 
            'acct_account_balance_detail.account_id', 'acct_account.account_code', 'acct_account.account_name',
            'acct_account_balance_detail.opening_balance', 'acct_account_balance_detail.account_in', 
            'acct_account_balance_detail.account_out', 'acct_account_balance_detail.last_balance',
            'acct_journal_voucher.journal_voucher_no','acct_journal_voucher_item.journal_voucher_description',
            'acct_journal_voucher.journal_voucher_id','acct_journal_voucher.data_state')

            ->join('acct_account','acct_account.account_id','=','acct_account_balance_detail.account_id')
            ->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->join('acct_journal_voucher_item','acct_journal_voucher_item.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->where('acct_account_balance_detail.account_id','=',$account_id)
            ->where('acct_account_balance_detail.branch_id','=',$branch_id)
            ->whereMonth('acct_account_balance_detail.transaction_date','=',$month_period)
            ->whereYear('acct_account_balance_detail.transaction_date','=',$year_period)
            ->orderBy('acct_account_balance_detail.transaction_date', 'ASC')
            ->orderBy('acct_account_balance_detail.account_balance_detail_id', 'ASC')
            ->distinct()
            ->get();
        }else{
            $accountbalancedetail	= AcctAccountBalanceDetail::select('acct_account_balance_detail.account_balance_detail_id', 
            'acct_account_balance_detail.transaction_type', 'acct_account_balance_detail.transaction_code', 
            'acct_account_balance_detail.transaction_date', 'acct_account_balance_detail.transaction_id', 
            'acct_account_balance_detail.account_id', 'acct_account.account_code', 'acct_account.account_name',
            'acct_account_balance_detail.opening_balance', 'acct_account_balance_detail.account_in', 
            'acct_account_balance_detail.account_out', 'acct_account_balance_detail.last_balance',
            'acct_journal_voucher.journal_voucher_no','acct_journal_voucher_item.journal_voucher_description',
            'acct_journal_voucher.journal_voucher_id','acct_journal_voucher.data_state')

            ->join('acct_account','acct_account.account_id','=','acct_account_balance_detail.account_id')
            ->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->join('acct_journal_voucher_item','acct_journal_voucher_item.journal_voucher_id','=','acct_account_balance_detail.transaction_id')
            ->where('acct_account_balance_detail.account_id','=',$account_id)
            ->where('acct_account_balance_detail.branch_id','=',$branch_id)
            ->whereMonth('acct_account_balance_detail.transaction_date','=',$month_period)
            ->whereYear('acct_account_balance_detail.transaction_date','=',$year_period)
            ->orderBy('acct_account_balance_detail.transaction_date', 'ASC')
            ->orderBy('acct_account_balance_detail.account_balance_detail_id', 'ASC')
            ->distinct()
            ->get();
        }


        if(!empty($accountbalancedetail)){
            $acctgeneralledgerreport = array ();
            if($opening_balance){
                $last_balance 		= $opening_balance['opening_balance'];
            }else{
                $last_balance 		= 0;
            }
            foreach ($accountbalancedetail as $key => $val) {
                // $description 	= AcctJournalVoucherItem::select('journal_voucher_description')
                // ->where('journal_voucher_id','=',$val['transaction_id'])
                // ->where('account_id','=',$val['account_id'])
                // ->first();      

                // $journal_no 	= AcctJournalVoucher::select('journal_voucher_no')
                // ->where('journal_voucher_id','=',$val['transaction_id'])
                // ->first();

                // $data_state 	= AcctJournalVoucher::select('data_state')
                // ->where('journal_voucher_id','=',$val['transaction_id'])
                // ->first();

                $last_balance = ($last_balance + $val['account_in']) - $val['account_out'];
                // $last_balance = 0;

                if($account_id_status['account_default_status'] == 1 ){
                    $debet 	= $val['account_in'];
                    $kredit = $val['account_out'];

                    if($last_balance >= 0){
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    } else {
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    }
                } else {
                    $debet 	= $val['account_out'];
                    $kredit = $val['account_in'];

                    if($last_balance >= 0){
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    } else {
                        
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    }
                }

                

                $data_acctgeneralledgerreport = array (
                    'transaction_date'			=> $val['transaction_date'],
                    // 'transaction_no'			=> $journal_no['journal_voucher_no'],
                    // 'transaction_no'			=> $journal_no['journal_voucher_no'],
                    // 'transaction_description'	=> $description['journal_voucher_description'],
                     'transaction_no'			=> $val['journal_voucher_no'],
                     'transaction_description'	=> $val['journal_voucher_description'],
                    'account_name'				=> $accountname['full_name'],
                    'account_in'				=> $debet,
                    'account_out'				=> $kredit,
                    'last_balance_debet'		=> $last_balance_debet,
                    'last_balance_credit'		=> $last_balance_kredit,
                    // 'data_state'				=> $data_state['data_state'],
                     'data_state'				=> $val['data_state'],
                );
                array_push($acctgeneralledgerreport, $data_acctgeneralledgerreport);
            }
        } else {
            $acctgeneralledgerreport = array ();
        }
        
        // $opening_balance            = $opening_balance['opening_balance'];
        if($opening_balance){
            $last_balance 		= $opening_balance['opening_balance'];
        }else{
            $last_balance 		= 0;
        }

        if($account_id_status){
            $account_id_status	        = $account_id_status['account_default_status'];
        }else{
            $account_id_status	        = 1;
        }
        $opening_balance            = $opening_balance;
    
        $account_id_status	        = $account_id_status;

        $monthlist 		            = array (
                                        '01'		=> "January",
                                        '02'		=> "February",
                                        '03'		=> "March",
                                        '04'		=> "April",
                                        '05'		=> "May",
                                        '06'		=> "June",
                                        '07'		=> "July",
                                        '08'		=> "August",
                                        '09'		=> "September",
                                        '10'		=> "October",
                                        '11'		=> "November",
                                        '12'		=> "December",
                                    );
        
        $acctaccount		        = AcctAccount::where('data_state','=',0)
        ->where('parent_account_status','=',0)
        ->get()
        ->pluck('account_name','account_id');

        $accountstatus = array (
            '1'	=> 'Debit',
            '0'	=> 'Kredit',
        );

        $year_now 	=	date('Y');
        for($i=($year_now-2); $i<($year_now+2); $i++){
            $year[$i] = $i;
        } 

        $corebranch         = CoreBranch::where('data_state','=',0)
        ->get()
        ->pluck('branch_name','branch_id');

        $spreadsheet = new Spreadsheet();
        
        if(count($acctgeneralledgerreport)>=0){
            
            $spreadsheet->getProperties()->setCreator("KAROTA KING")
                                 ->setLastModifiedBy("KAROTA KING")
                                 ->setTitle("Buku Besar")
                                 ->setSubject("")
                                 ->setDescription("Buku Besar")
                                 ->setKeywords("Buku Besar")
                                 ->setCategory("Buku Besar");
                                 
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Buku Besar");
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    
            $spreadsheet->getActiveSheet()->mergeCells("B1:G1");

            $spreadsheet->getActiveSheet()->mergeCells("B8:B9");
            $spreadsheet->getActiveSheet()->mergeCells("C8:C9");
            $spreadsheet->getActiveSheet()->mergeCells("D8:D9");
            $spreadsheet->getActiveSheet()->mergeCells("E8:E9");
            $spreadsheet->getActiveSheet()->mergeCells("F8:F9");

            
            $spreadsheet->getActiveSheet()->mergeCells("G8:H8");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);

            $spreadsheet->getActiveSheet()->getStyle('B8:H8')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B9:H9')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $spreadsheet->getActiveSheet()->getStyle('B8:H8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B9:H9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $spreadsheet->getActiveSheet()->mergeCells("B5:C5");
            $spreadsheet->getActiveSheet()->getStyle('B5:D5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $spreadsheet->getActiveSheet()->getStyle('B5:D5')->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->mergeCells("B6:C6");
            $spreadsheet->getActiveSheet()->getStyle('B6:D6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $spreadsheet->getActiveSheet()->getStyle('B6:D6')->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->mergeCells("B7:C7");
            $spreadsheet->getActiveSheet()->getStyle('B7:D7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $spreadsheet->getActiveSheet()->getStyle('B7:D7')->getFont()->setBold(true);
            
            $spreadsheet->getActiveSheet()->getStyle('D7')->getNumberFormat()->setFormatCode('0.00');



            
            $sheet->setCellValue('B1',"Buku Besar Dari Periode ".$monthlist[$month_period]." ".$year_period);	
            $sheet->setCellValue('B5',"Nama Perkiraan");
            $sheet->setCellValue('D5', $accountname['full_name']);
            $sheet->setCellValue('B6',"Posisi Saldo");
            $sheet->setCellValue('D6',$accountstatus[$account_id_status]);
            $sheet->setCellValue('B7',"Saldo Awal");
            $sheet->setCellValue('D7',number_format($opening_balance, 2));
            $sheet->setCellValue('B8',"No");
            $sheet->setCellValue('C8',"Tanggal");
            $sheet->setCellValue('D8',"Uraian");
            $sheet->setCellValue('E8',"Debet");
            $sheet->setCellValue('F8',"Kredit");
            $sheet->setCellValue('G8',"Saldo");

            
            $sheet->setCellValue('G9',"Debet");
            $sheet->setCellValue('H9',"Kredit");
            
            
            $j=10;
            $no=0;
            
            foreach($acctgeneralledgerreport as $key=>$val){

                if(is_numeric($key)){
                    
                    $spreadsheet->setActiveSheetIndex(0);
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j.':H'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    $spreadsheet->getActiveSheet()->getStyle('E'.$j.':H'.$j)->getNumberFormat()->setFormatCode('0.00');
            
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                        $no++;
                        $sheet->setCellValue('B'.$j, $no);
                        $sheet->setCellValue('C'.$j, $val['transaction_date']);
                        $sheet->setCellValue('D'.$j, $val['transaction_description']);
                        $sheet->setCellValue('E'.$j, $val['account_in']);
                        $sheet->setCellValue('F'.$j, $val['account_out']);
                        $sheet->setCellValue('G'.$j, $val['last_balance_debet']);
                        $sheet->setCellValue('H'.$j, $val['last_balance_credit']);
                        
                        
                    
                }else{
                    continue;
                }
                $j++;
        
            }
            
            // ob_clean();
            $filename='Buku_Besar_'.$accountname['full_name'].'.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }else{
            echo "Maaf data yang di eksport tidak ada !";
        }

    }

    public function getMinID($journal_voucher_id){
        $id = AcctJournalVoucherItem::select('journal_voucher_item_id')->where('journal_voucher_id','=',$journal_voucher_id)->orderBy('journal_voucher_item_id', 'asc')->first();
        return $id['journal_voucher_item_id'];
    }

    public function getProjectName($project_id){
        $project_name = CoreProject::select('project_name')->where('project_id','=',$project_id)->first();
        return $project_name['project_name'];
    }

    public function getAccountCode($account_id){
        $account_code = AcctAccount::select('account_code')->where('account_id','=',$account_id)->first();
        return $account_code['account_code'];
    }

    public function getAccountName($account_id){
        $account_name = AcctAccount::select('account_name')->where('account_id','=',$account_id)->first();
        return $account_name['account_name'];
    }

    public function selectProjectAcctJournalVoucher(Request $request)
    {
        Session::put('journalvoucherprojecttype', $request->project_type_id);

        return redirect('/journal/add');
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
