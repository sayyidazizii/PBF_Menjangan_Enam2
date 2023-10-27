@inject('PurchaseInvoice', 'App\Http\Controllers\PurchaseInvoiceController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
      <li class="breadcrumb-item"><a href="{{ url('purchase-invoice') }}">Daftar Invoice Pembelian</a></li>
      <li class="breadcrumb-item active" aria-current="page">Daftar Purchase Order</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Purchase Order</b>
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
    <div class="float-right">
        <button onclick="location.href='{{ url('purchase-invoice') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
    </div>
  </div>

  <form method="post" action="/sales-delivery-order/add" enctype="multipart/form-data">
    @csrf
    <div class="card-body">
        <div class="table-responsive">
            <table style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="13%" style='text-align:center'>Pemasok</th>
                        <th width="15%" style='text-align:center'>No. PO</th>
                        <th width="10%" style='text-align:center'>Tanggal PO</th>
                        <th width="12%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($purchaseorder as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{$PurchaseInvoice->getSupplierName($item['supplier_id'])}}</td>
                        <td>{{$item['purchase_order_no']}}</td>
                        <td>{{$item['purchase_order_date']}}</td>
                        <td style='text-align:center'>
                            <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('/purchase-invoice/add/'.$item['purchase_order_id']) }}"><i class="fa fa-plus"></i></a>
                        </td>
                    </tr>
                    <?php $no++; ?>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
        
    {{-- <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <button type="submit" name="Save" class="btn btn-primary" title="Save">Tambah</button>
        </div>
    </div> --}}
  </div>
</div>
</form>

@stop

@section('footer')
    
@stop

@section('css')
    
@stop