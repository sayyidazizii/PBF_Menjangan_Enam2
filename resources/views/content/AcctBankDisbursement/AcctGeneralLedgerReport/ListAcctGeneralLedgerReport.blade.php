@inject('AcctGeneralLedgerReport', 'App\Http\Controllers\AcctGeneralLedgerReportController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Buku Besar</li>
    </ol>
</nav>

@stop

@section('content')


@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif 
<div class="card border border-dark">
    <div class="card-header bg-dark clearfix">
        <h5 class="mb-0 float-left">
            Daftar Buku Besar
        </h5>
    </div>

<form  method="post" action="{{route('filter-ledger')}}" enctype="multipart/form-data">
@csrf
    <div class="card-body">
        <div class='row'>
            <div style='width:47% !important; margin-right:3%; margin-left:0.5%' >
                <div class="row form-group">
                    <a class="text-dark">Periode (Bulan)</a>
                    <br/>
                    {!! Form::select('month_id',  $monthlist, $month_period, ['class' => 'selection-search-clear select-form', 'style'=> 'width: 100% !important']) !!}
                </div>
            </div>	
            <div style='width:47% !important; margin-right:2%' >
                <div class="row form-group">
                    <a class="text-dark">Periode (Tahun)</a>
                    <br/>
                    {!! Form::select('year_id',  $year, $year_period, ['class' => 'selection-search-clear select-form', 'style'=> 'width: 100% !important']) !!}
                </div>
            </div>	
        </div>
        <div class="row">
            <div style='width:47% !important; margin-right:3%; margin-left:0.5%' >
                <div class="row form-group">
                    <a class="text-dark">Nama Account</a>
                    <br/>
                    {!! Form::select('account_id',  $acctaccount, $account_id, ['class' => 'selection-search-clear select-form', 'style'=> 'width: 100% !important']) !!}
                </div>
            </div>	
            {{-- <div style='width:47% !important; margin-right:2%' >
                <div class="row form-group">
                    <a class="text-dark">Cabang</a>
                    <br/>
                    {!! Form::select('branch_id',  $corebranch, $branch_id, ['class' => 'selection-search-clear select-form', 'style'=> 'width: 100% !important']) !!}
                </div>
            </div>	 --}}
        </div>
    </div>
    <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <button type="reset" name="Reset" class="btn btn-danger" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
            <button type="submit" name="Find" class="btn btn-primary" title="Search Data"><i class="fa fa-search"></i> Cari</button>
        </div>
    </div>
</form>
    
    <div class="card-body">
        <table class="table table-striped table-bordered table-hover table-full-width">
        <thead>
            <tr>
                <th width="5%" rowspan="2"  style='text-align:center; vertical-align: middle;'>No</th>
                <th width="10%" rowspan="2"  style='text-align:center; vertical-align: middle;'>Tanggal</th>
                <th width="10%" rowspan="2"  style='text-align:center; vertical-align: middle;'>No. Jurnal</th>
                <th width="25%" rowspan="2"  style='text-align:center; vertical-align: middle;'>Deskripsi</th>
                <th width="20%" rowspan="2"  style='text-align:center; vertical-align: middle;'>Nama Perkiraan</th>
                <th width="15%" rowspan="2"  style='text-align:center; vertical-align: middle;'>Debet</th>
                <th width="15%" rowspan="2"  style='text-align:center; vertical-align: middle;'>Kredit</th>
                <th width="15%" colspan="2"  style='text-align:center; vertical-align: middle;'>Saldo</th>
            </tr>
            <tr>
                <th width="15%">Debet</th>
                <th width="15%">Kredit</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" align="center"><b> Saldo Awal</b></td>
                <td></td>
                <td></td>
                <?php 
                    if($account_id_status == 1){
                        if($opening_balance >= 0){
                            echo "
                                <td style='text-align: right'>".number_format($opening_balance, 2)."</td>
                                <td style='text-align: right'>0.00</td>
                            ";
                        } else {
                            echo "
                                <td style='text-align: right'>0.00</td>
                                <td style='text-align: right'>".number_format($opening_balance, 2)."</td>
                            ";
                        }
                    
                    } else {
                        if($opening_balance >= 0){
                            echo "
                                <td style='text-align: right'>0.00</td>
                                <td style='text-align: right'>".number_format($opening_balance, 2)."</td>
                                
                            ";
                        } else {
                            echo "
                                <td style='text-align: right'>".number_format($opening_balance, 2)."</td>
                                <td style='text-align: right'>0.00</td>
                            ";
                        }
                    }
                ?>
                
                
            </tr>
            <?php
                $no = 1;
                $last_balance_debet 	= 0;
                $last_balance_credit	= 0;

                $total_debit 	= 0;
                $total_kredit 	= 0;
                if(!empty( $acctgeneralledgerreport)){	
                    foreach ( $acctgeneralledgerreport as $key=>$val){	
                        if($val['data_state']==2){
                            echo"
                                <tr class='red'>			
                                    <td style='text-align:center'>$no.</td>
                                    <td style='text-align:center'>".$val['transaction_date']."</td>
                                    <td>".$val['transaction_no']."</td>
                                    <td>".$val['transaction_description']."</td>
                                    <td>".$val['account_name']."</td>
                                    <td style='text-align:right'>".number_format($val['account_in'], 2)."</td>
                                    <td style='text-align:right'>".number_format($val['account_out'], 2)."</td>
                                    <td style='text-align:right'>".number_format($val['last_balance_debet'], 2)."</td>
                                    <td style='text-align:right'>".number_format($val['last_balance_credit'], 2)."</td>
                                </tr>
                            ";
                        }else{
                            echo"
                                <tr>			
                                    <td style='text-align:center'>$no.</td>
                                    <td style='text-align:center'>".$val['transaction_date']."</td>
                                    <td>".$val['transaction_no']."</td>
                                    <td>".$val['transaction_description']."</td>
                                    <td>".$val['account_name']."</td>
                                    <td style='text-align:right'>".number_format($val['account_in'], 2)."</td>
                                    <td style='text-align:right'>".number_format($val['account_out'], 2)."</td>
                                    <td style='text-align:right'>".number_format($val['last_balance_debet'], 2)."</td>
                                    <td style='text-align:right'>".number_format($val['last_balance_credit'], 2)."</td>
                                </tr>
                            ";
                        }
                        $no++;

                        $last_balance_debet 	= $val['last_balance_debet'];
                        $last_balance_credit 	= $val['last_balance_credit'];

                        $total_debit += $val['account_in'];
                        $total_kredit+= $val['account_out'];
                    } 
                } else {
                    if($account_id_status == 1){
                        if($opening_balance >= 0){
                            $last_balance_debet 	= $opening_balance;
                            $last_balance_credit 	= 0;
                        } else {
                            $last_balance_debet 	= 0;
                            $last_balance_credit 	= $opening_balance;
                        }
                    
                    } else {
                        if($opening_balance >= 0){
                            $last_balance_debet 	= 0;
                            $last_balance_credit 	= $opening_balance;
                        } else {
                            $last_balance_debet 	= $opening_balance;
                            $last_balance_credit 	= 0;
                            
                        }
                    }
                }
                
            ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right"><?php echo number_format($total_debit, 2); ?></td>
                    <td style="text-align: right"><?php echo number_format($total_kredit, 2); ?></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="5" align="center"><b> Saldo Akhir</b></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right"><?php echo number_format($last_balance_debet, 2); ?></td>
                    <td style="text-align: right"><?php echo number_format($last_balance_credit, 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <a type="reset" name="Reset" class="btn btn-info" href="/ledger/printing"><i class="fa fa-eye"></i> Preview</a>
            <a type="submit" name="Find" class="btn btn-primary" href="/ledger/export"><i class="fa fa-download"></i> Export Data</a>
        </div>
    </div>
  </div>
</div>

<form method="post" action="{{route('select-project-journal')}}" enctype="multipart/form-data">
    @csrf
    <div class="modal fade bs-modal-lg" id="chooseprojecttype" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"  style='text-align:left !important'>
                    <h4>Pilih Tipe Proyek</h4>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">		
                            <center>
                                    
                                <input type="radio" name="project_type_id" id="project_type_id" value="0" checked> Proyek WBM

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input type="radio" name="project_type_id" id="project_type_id" value="1"> Proyek Non WBM
                            </center>
                        </div>
                        <div class="col-md-3"></div>	
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('footer')
    
@stop

@section('css')
    
@stop

@section('js')
    
@stop