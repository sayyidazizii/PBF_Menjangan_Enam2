@inject('AcctBankDisbursementReport', 'App\Http\Controllers\AcctBankDisbursementReportController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Laporan Pengeluaran Bank</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Laporan Pengeluaran Bank</b> <small>Daftar Pengeluaran Bank </small>
</h3>
<br/>
<div id="accordion">
    <form  method="post" action="{{route('report-filter-bank-disbursement')}}" enctype="multipart/form-data">
    @csrf
        <div class="card border border-dark">
        <div class="card-header bg-dark" id="headingOne" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            <h5 class="mb-0">
                Filter
            </h5>
        </div>
    
        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
            <div class="card-body">
                <div class = "row">
                    <div class = "col-md-4">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Mulai
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="date" onChange="function_elements_add(this.name, this.value);" value="{{$start_date}}" style="width: 15rem;"/>
                        </div>
                    </div>

                    <div class = "col-md-4">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Akhir
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="date" onChange="function_elements_add(this.name, this.value);" value="{{$end_date}}" style="width: 15rem;"/>
                        </div>
                    </div>
                    {{-- <div class="col-md-4">
                        <a class="text-dark">Cabang</a>
                        <select class="selection-search-clear" name="branch_id" style="width: 100% !important">
                            @foreach($branch as $item)
                            @if($item['branch_id']==$branch_id){
                                <option value="{{$item['branch_id']}}" selected="selected">{{$item['branch_name']}}</option>
                            }
                            @else
                                <option value="{{$item['branch_id']}}">{{$item['branch_name']}}</option>
                            @endif
                            @endforeach
                        </select>
                    </div> --}}
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <button type="reset" name="Reset" class="btn btn-danger" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                    <button type="submit" name="Find" class="btn btn-primary" title="Search Data"><i class="fa fa-search"></i> Cari</button>
                </div>
            </div>
        </div>
        </div>
    </form>
</div>
<br/>
<div class="card border border-dark">
    <div class="card-header bg-dark clearfix">
        <h5 class="mb-0 float-left">
            Daftar
        </h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="15%" style='text-align:center'>Tanggal</th>
                        <th width="20%" style='text-align:center'>No. Pengeluaran</th>
                        <th width="23%" style='text-align:center'>Judul</th>
                        <th width="20%" style='text-align:center'>Total Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no =1; ?>
                    @foreach($acctdisbursement as $disbursement)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{$disbursement['bank_disbursement_date']}}</td>
                        <td>{{$disbursement['bank_disbursement_no']}}</td>
                        <td>{{$disbursement['bank_disbursement_title']}}</td>
                        <td style='text-align:right'>{{number_format($disbursement['bank_disbursement_amount_total'], 0, '', '.')}}</td>
                    </tr>
                    <?php $no++?>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<br>
<br>

@include('footer')

@stop

@section('css')
    
@stop

@section('js')
    
@stop