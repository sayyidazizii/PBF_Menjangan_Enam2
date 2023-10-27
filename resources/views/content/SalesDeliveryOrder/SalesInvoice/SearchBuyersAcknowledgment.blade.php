@inject('BuyersAcknowledgment', 'App\Http\Controllers\BuyersAcknowledgmentController')

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
        <li class="breadcrumb-item"><a href="{{ url('sales-invoice') }}">Daftar Sales invoice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Buyers Acknowledgment</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Buyers Acknowledgment</b>
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
            <button onclick="location.href='{{ url('sales-invoice') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="/sales-delivery-order/add" enctype="multipart/form-data">
    @csrf
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="20%" style='text-align:center'>Nama Pelanggan</th>
                        <th width="15%" style='text-align:center'>No Penerimaan</th>
                        <th width="15%" style='text-align:center'>No SO</th>
                        <th width="10%" style='text-align:center'>Tanggal SO</th>
                        <th width="10%" style='text-align:center'>Tanggal Delivery SO</th>
                        <th width="10%" style='text-align:center'>Nomor PO Customer</th>
                        <th width="10%" style='text-align:center'>Tanggal Penerimaan Pihak Pembeli</th>
                        <th width="5%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($buyersAcknowledgment as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{$BuyersAcknowledgment->getCustomerNameSalesOrderId($item['sales_order_id'])}}</td>
                        <td>{{$item['buyers_acknowledgment_no']}}</td>
                        <td>{{$BuyersAcknowledgment->getSalesOrderNo($item['sales_order_id'])}}</td>
                        <td>{{date('d/m/Y', strtotime($BuyersAcknowledgment->getSalesOrderDate($item['sales_order_id'])))}}</td>
                        <td>{{date('d/m/Y', strtotime($BuyersAcknowledgment->getSalesDeliveryOrderDate($item['sales_delivery_order_id'])))}}</td>
                        <td>{{$BuyersAcknowledgment->getPoNo($item['sales_order_id'])}}</td>
                        <td>{{date('d/m/Y', strtotime($item['buyers_acknowledgment_date']))}}</td>
                        <td style='text-align:center'>
                            <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('/sales-invoice/add/'.$item['buyers_acknowledgment_id']) }}"><i class="fa fa-plus"></i></a>
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
<br>
<br>
<br>

@include('footer')

@stop

@section('css')
    
@stop