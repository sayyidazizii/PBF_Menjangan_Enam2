@inject('AcctJournalVoucher', 'App\Http\Controllers\AcctJournalVoucherPurchaseController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Jurnal Penjualan</li>
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
            Daftar Jurnal Penjualan
        </h5>
        {{-- <div class="form-actions float-right">
            <a  href="{{route('add-journal')}}" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Jurnal Umum Baru</a>
        </div> --}}
    </div>

<form  method="post" action="{{route('filter-journal-sales')}}" enctype="multipart/form-data">
@csrf
    <div class="card-body">
        <div class='row'>
            <div class = "col-md-4">
                <div class="form-group form-md-line-input">
                    <section class="control-label">Tanggal Mulai
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="start_date" onChange="function_elements_add(this.name, this.value);" value="{{$start_date}}" style="width: 15rem;"/>
                </div>
            </div>
            <div class = "col-md-4">
                <div class="form-group form-md-line-input">
                    <section class="control-label">Tanggal Akhir
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="end_date" onChange="function_elements_add(this.name, this.value);" value="{{$end_date}}" style="width: 15rem;"/>
                </div>
            </div>
            {{-- <div class="col-md-4">
                <div class="row form-group">
                    <a class="text-dark">Cabang</a>
                    <br/>
                    {!! Form::select('branch_id',  $corebranch, null, ['class' => 'selection-search-clear select-form']) !!}
                </div>
            </div>	 --}}
        </div>
    </div>
    <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <button type="reset" name="Reset" class="btn btn-sm btn-danger" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
            <button type="submit" name="Find" class="btn btn-sm btn-primary" title="Search Data"><i class="fa fa-search"></i> Cari</button>
        </div>
    </div>
</form>
    
    <div class="card-body">
        <table class="table table-striped table-bordered table-hover table-full-width">
            <thead>
                <tr style='text-align:center'>
                    <th width="5%">No</th>
                    <th width="8%">Tanggal</th>
                    <th width="18%">Uraian</th>
                    <th width="20%">Deskripsi</th>
                    <th width="8%">No. Perkiraan</th>
                    <th width="20%">Nama Perkiraan</th>
                    <th width="11%">Debit</th>
                    <th width="11%">Kredit</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                if(empty($acctjournalvoucher)){
                    echo "
                        <tr>
                            <td colspan='6' align='center'>Data Kosong</td>
                        </tr>
                    ";
                } else {
                    $totaldebet		= 0;
                    $totalkredit	= 0;
                    foreach ($acctjournalvoucher as $key=>$val){	
                        $id = $AcctJournalVoucher->getMinID($val['journal_voucher_id']);

                        $project_name = $AcctJournalVoucher->getProjectName($val['project_id']);

                        // if($val['journal_voucher_amount'] <> 0){
                            if($val['journal_voucher_debit_amount'] <> 0 ){
                                $debet = number_format($val['journal_voucher_debit_amount'], 2);
                                $kredit = " ";
                            } else if($val['journal_voucher_credit_amount'] <> 0){
                                $kredit = number_format($val['journal_voucher_credit_amount'], 2);
                                $debet = " ";
                            } else {
                                $kredit = " ";
                                $debet = " ";
                            }
                        // }
                        


                        if($val['journal_voucher_item_id'] === $id){
                            echo"
                                <tr>			
                                    <td style='text-align:center'>".$no."</td>
                                    <td>".$val['journal_voucher_date']."</td>
                                    <td>".$val['journal_voucher_title']."</td>
                                    <td>".$val['journal_voucher_description_item']."</td>
                                    <td>".$val['account_code']."</td>
                                    <td>".$val['account_name']."</td>
                                    <td align='right'>".$debet."</td>
                                    <td align='right'>".$kredit."</td>
                                </tr>
                            ";
                            $no++;
                        } else {
                            echo"
                                <tr>			
                                    <td style='text-align:center'></td>
                                    <td></td>
                                    <td></td>
                                    <td>".$val['journal_voucher_description_item']."</td>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;".$val['account_code']."</td>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;".$val['account_name']."</td>
                                    <td align='right'>".$debet."</td>
                                    <td align='right'>".$kredit."</td>
                                </tr>
                            ";
                        }

                        $totaldebet 	+= $val['journal_voucher_debit_amount'];
                        $totalkredit 	+= $val['journal_voucher_credit_amount'];
                    }
                    
                    echo"
                        <tr>
                            <td colspan='8' style='text-align:right'></td>
                        </tr>
                        <tr>
                            <td colspan='6' style='text-align:right'><b> TOTAL </b></td>
                            <td style='text-align:right'><b>".number_format($totaldebet, 2)."</b></td>
                            <td style='text-align:right'><b>".number_format($totalkredit, 2)."</b></td>
                        </tr>
                    ";
                }
                
            ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <a type="reset" name="Reset" class="btn btn-sm btn-info" target="_blank" href="{{route('printing-journal-sales')}}"><i class="fa fa-eye"></i> Preview</a>
            <a type="submit" name="Find" class="btn btn-sm btn-primary" href="{{route('export-journal-sales')}}"><i class="fa fa-download"></i> Export Data</a>
        </div>
    </div>
  </div>
</div>
<br>
<br>
<br>

@include('footer')

@stop

@section('css')
    
@stop

@section('js')
    
@stop