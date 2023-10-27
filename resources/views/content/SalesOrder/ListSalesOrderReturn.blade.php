@inject('SalesOrderReturn', 'App\Http\Controllers\SalesOrderReturnController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Return Penjualan</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Return Penjualan</b> <small>Mengelola Return Penjualan</small>
</h3>
<br/>
<div id="accordion">
    <form  method="post" action="{{route('filter-sales-order-return')}}" enctype="multipart/form-data">
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
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <a type="button" href="{{ route('filter-reset-sales-order-return')}}" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</a>
                    <button type="submit" name="Find" class="btn btn-primary btn-sm" title="Search Data"><i class="fa fa-search"></i> Cari</button>
                    <a href="{{ url('sales-order-return/export') }}"name="Find" class="btn btn-sm btn-info" title="Export Excel"><i class="fa fa-print"></i>Export</a>

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
            <button onclick="location.href='{{ route('search-sales-invoice') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Return Penjualan</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        <th width="20%" style='text-align:center'>Nama Pelanggan</th>
                        <th width="15%" style='text-align:center'>No Invoice</th>
                        <th width="15%" style='text-align:center'>No Retur Barang</th>
                        <th width="15%" style='text-align:center'>Nota Retur Pajak</th>
                        <th width="15%" style='text-align:center'>Status Barang</th>
                        <th width="10%" style='text-align:center'>Tanggal Invoice</th>
                        <th width="10%" style='text-align:center'>Tanggal Return Penjualan</th>
                        <th width="5%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($salesorderreturn as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        <td>{{$SalesOrderReturn->getCustomerNameSalesOrderId($item['sales_order_id'])}}</td>
                        <td>{{$SalesOrderReturn->getSalesInvoiceNo($item['sales_invoice_id'])}}</td>
                        <td>{{$item['no_retur_barang']}}</td>
                        <td>{{$item['nota_retur_pajak']}}</td>
                        @php
                            if($item['barang_kembali'] == 0 ){
                                echo "<td>Barang Belum Kembali</td>";
                            }
                            if($item['barang_kembali'] == 1 ){
                                echo "<td>Barang Sudah Kembali</td>";
                            }
                        @endphp
                        <td>{{$SalesOrderReturn->getSalesInvoiceDate($item['sales_invoice_id'])}}</td>
                        <td>{{date('d/m/Y', strtotime($item['sales_order_return_date']))}}</td>
                        <td class="" style='text-align:center'>
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/sales-order-return/edit/'.$item['sales_order_return_id']) }}">Edit</a>
                            <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('/sales-order-return/detail/'.$item['sales_order_return_id']) }}">Detail</a>
                        </td>
                    </tr>
                    <?php $no++; ?>
                    @endforeach
                </tbody>
            </table>
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