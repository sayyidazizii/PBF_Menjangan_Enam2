@inject('AcctCashReceipt', 'App\Http\Controllers\AcctCashReceiptController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Penerimaan Kas</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Penerimaan Kas</b> <small>Mengelola Penerimaan Kas </small>
</h3>
<br/>
<div id="accordion">
    <form  method="post" action="{{route('filter-cash-receipt')}}" enctype="multipart/form-data">
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
                    <div class = "col-md-6">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Mulai
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="start_date" onChange="function_elements_add(this.name, this.value);" value="{{$start_date}}" style="width: 15rem;"/>
                        </div>
                    </div>

                    <div class = "col-md-6">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Akhir
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="end_date" onChange="function_elements_add(this.name, this.value);" value="{{$end_date}}" style="width: 15rem;"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <button type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                    <button type="submit" name="Find" class="btn btn-primary btn-sm" title="Search Data"><i class="fa fa-search"></i> Cari</button>
                </div>
            </div>
        </div>
        </div>
    </form>
</div>
<br/>
@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif
<div class="card border border-dark">
    <div class="card-header bg-dark clearfix">
        <h5 class="mb-0 float-left">
            Daftar
        </h5>
        <div class="form-actions float-right">
            <a   href="{{route('add-cash-receipt')}}" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Penerimaan Kas Baru</a>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="15%" style='text-align:center'>Nama Pelanggan</th>
                        <th width="10%" style='text-align:center'>Tanggal</th>
                        <th width="10%" style='text-align:center'>No. Penerimaan</th>
                        <th width="15%" style='text-align:center'>Judul</th>
                        <th width="10%" style='text-align:center'>Total Jumlah</th>
                        <th width="10%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no =1; ?>
                    @foreach($acctreceipt as $val)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{$AcctCashReceipt->getCustomerName($val['customer_id'])}}</td>
                        <td>{{date('d/m/Y', strtotime($val['cash_receipt_date']))}}</td>
                        <td>{{$val['cash_receipt_no']}}</td>
                        <td>{{$val['cash_receipt_title']}}</td>
                        <td style='text-align:right'>{{number_format($val['cash_receipt_amount_total'], 0, '', '.')}}</td>
                        <td class="" style='text-align:center'>
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{route('detail-cash-receipt', ['cash_receipt_id' => $val['cash_receipt_id']])}}" >Detail</a>
                            <a type="button" class="btn btn-outline-danger btn-sm"  href="{{route('void-cash-receipt', ['cash_receipt_id' => $val['cash_receipt_id']])}}">Batal</a>
                        </td>
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