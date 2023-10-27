@inject('SalesDeliveryOrder', 'App\Http\Controllers\SalesDeliveryOrderController')
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
        <li class="breadcrumb-item"><a href="{{ url('sales-delivery-order') }}">Daftar Sales Delivery Order</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-delivery-order/detail/'.$sales_delivery_order_id) }}">Detail Sales Delivery Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Item Stock Sales Delivery Order</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Detail Item Stock Sales Delivery Order</b>
</h3>
<br/>
@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif
@if(count($errors) > 0)
<div class="alert alert-danger" role="alert">
    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
</div>
@endif
    <div class="card border border-dark">
        <div class="card-header border-dark bg-dark">
            <h5 class="mb-0 float-left">
                Daftar
            </h5>
            <div class="float-right">
                <button onclick="location.href='{{ url('sales-delivery-order/detail/'.$sales_delivery_order_id) }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
            </div>
        </div>
    
        <div class="card-body">
            <div class="form-body form">
                <div class="table-responsive">
                    <table class="table table-bordered table-advance table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style='text-align:center'>No.</th>
                                <th style='text-align:center'>Nama- Barang</th>
                                <th style='text-align:center'>Stock Barang</th>
                                <th style='text-align:center'>Qty Stock</th>
                                {{-- <th style='text-align:center'>Aksi</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                                @if($detail_stock_sdo->count() == 0)
                                    <tr>
                                        <th colspan='9' style='text-align  : center !important;'>Data Kosong</th>
                                    </tr>
                                @else
                                    @php
                                        $no = 1;
                                    @endphp
                                        @foreach ($detail_stock_sdo AS $key => $val)
                                                <tr>
                                                    <td style='text-align  : center'>{{$no}}.</td>
                                                    <td style='text-align  : left !important;'>{{$SalesDeliveryOrder->getSalesOrderItemTypeName($val['sales_order_item_id'])}}</td>
                                                    <input type="hidden" name="sales_order_item_id_{{$no}}" id="sales_order_item_id_{{$no}}" value="$val['sales_order_item_id']">
                                                    <td style='text-align  : left !important;'>{{$SalesDeliveryOrder->getSelectInvItemStock2($val['item_stock_id'])}}</td>
                                                    <td style='text-align  : right !important;'>{{$val['item_total_stock']}}</td>
                                                    {{-- <td style='text-align:center;'>
                                                        <a href='3' class="btn btn-outline-danger btn-sm" title="Add Data">Hapus</a>
                                                    </td> --}}
                                            </tr>
                                            @php
                                                $no++; 
                                            @endphp 
                                        @endforeach
                                @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<br/>
<br/>
<br/>

@include('footer')

@stop


@section('css')
    
@stop

@section('js')
    
@stop