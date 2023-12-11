@inject('PurchaseOrderReturn', 'App\Http\Controllers\PurchaseOrderReturnController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Purchase Invoice</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Purchase Invoice</b> <small>Mengelola Purchase Invoice</small>
</h3>
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
            <button onclick="location.href='{{ url('purchase-order-return') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        <th width="10%" style='text-align:center'>No Invoice</th>
                        <th width="10%" style='text-align:center'>Tanggal Invoice</th>
                        <th width="18%" style='text-align:center'>Nama Supplier</th>
                        <th width="10%" style='text-align:center'>Subtotal Amount</th>
                        <th width="8%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; 
                    $purchase_invoice_id = -1;
                    ?>
                    @foreach($purchaseinvoice as $item)
                        <tr>
                            <td style='text-align:center'>{{$no}}.</td>
                            <td>{{$item['purchase_invoice_no']}}</td>
                            <td>{{$item['purchase_invoice_date']}}</td>
                            <td>{{$PurchaseOrderReturn->getCoreSupplierName($item['supplier_id'])}}</td>
                            <td>{{$item['subtotal_amount']}}</td>
                            <td class="" style='text-align:center'>
                                <a type="button" class="btn btn-outline-success btn-sm" href="{{ url('/purchase-order-return/add/'.$item['purchase_invoice_id']) }}"><i class="fa fa-plus"></i> Tambah</a>
                            </td>
                        </tr>
                        <?php 
                        $no++; 
                        ?>
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