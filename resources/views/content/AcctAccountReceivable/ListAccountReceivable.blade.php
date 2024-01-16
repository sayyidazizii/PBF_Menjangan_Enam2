@inject('AcctAgingArReportController', 'App\Http\Controllers\AcctAgingArReportController')

@extends('adminlte::page')

@section('js')
<script>
	$(document).ready(function(){
        var customer_id    = {!! json_encode($customer) !!};
        
        if(customer_id == null){
            $("#customer_id").select2("val", "0");
        }
    });
</script>
@stop

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Aging Piutang Customer</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Aging Piutang Customer</b> <small>Mengelola Aging Piutang Customer</small>
</h3>
<br/>
<div id="accordion">
    <form  method="post" action="{{route('filter-aging-account-receivable')}}" enctype="multipart/form-data">
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
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="date" onChange="function_elements_add(this.name, this.value);" value="{{$start_date}}"/>
                        </div>
                    </div>

                    <div class = "col-md-4">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Akhir
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="date" onChange="function_elements_add(this.name, this.value);" value="{{$end_date}}"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a class="text-dark">Pelanggan</a>
                        <br/>
                        {!! Form::select('customer_id',  $customer, $customer_id, ['class' => 'selection-search-clear select-form', 'id' => 'customer_id']) !!}
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <a href="{{route('filter-reset-aging-account-receivable')}}" type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</a>
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
            {{-- <button onclick="location.href='{{ url('purchase-invoice/search-goods-received-note') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Invoice Pembelian Baru</button> --}}
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        <th width="15%" style='text-align:center'>Nama Pemasok</th>
                        <th width="10%" style='text-align:center'>No Invoice Penjualan</th>
                        <th width="10%" style='text-align:center'>Tanggal Invoice</th>
                        <th width="10%" style='text-align:center'>Jatuh Tempo</th>
                        <th width="10%" style='text-align:center'>No Faktur Pajak</th>
                        <th width="10%" style='text-align:center'>Umur</th>
                        <th width="20%" style='text-align:center'>Piutang</th>
                        <th width="20%" style='text-align:center'>Saldo Akhir</th>
                        {{-- <th width="15%" style='text-align:center'>Aksi</th> --}}
                    </tr>
                </thead>
                <tbody>
                    <?php
                     $no = 1;
                     $aging = 0;
                     ?>
                    @foreach($salesinvoice as $item)
                    <?php
                     $aging = $AcctAgingArReportController->calculateDateDifference($item['sales_invoice_date'],date('d-m-Y'))
                     ?>
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        <td>{{$AcctAgingArReportController->getCustomerName($item['customer_id'])}}</td>
                        <td>{{$item['sales_invoice_no']}}</td>
                        <td>{{date('d/m/Y', strtotime($item['sales_invoice_date']))}}</td>
                        <td>{{date('d/m/Y', strtotime($item['sales_invoice_due_date']))}}</td>
                        <td>{{$item['faktur_tax_no']}}</td>
                        <td>{{ $aging['days'] }} Hari</td>
                        <td style='text-align:right'>{{$item['total_amount']}}</td>
                        <td style='text-align:right'>{{$item['owing_amount']}}</td>
                        
                        {{-- <td class="" style='text-align:center'>
                            <a type="button" class="btn btn-outline-secondary btn-sm" href="{{ url('/purchase-invoice/edit/'.$item['purchase_invoice_id']) }}">Print</a>
                            <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('/purchase-invoice/detail/'.$item['purchase_invoice_id']) }}">Detail</a>
                        </td> --}}
                   </tr> 
                    <?php 
                    $no++;
                     ?>
                    @endforeach
                </tbody>
            </table>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    {{-- <a href="{{ url('aging-account-payable/print') }}"name="Find" class="btn btn-sm btn-secondary" title="print Pdf"><i class="fa fa-print"></i>Cetak</a> --}}
                </div>
            </div>
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