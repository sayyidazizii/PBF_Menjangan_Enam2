@inject('SalesDeliveryOrder', 'App\Http\Controllers\SalesDeliveryOrderController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
    $(document).ready(function(){
        $("#sales_order_item_id").select2("val", "0");
        $("#item_stock_id").select2("val", "0");

        $("#sales_order_item_id").change(function(){
			var sales_order_item_id 	= $("#sales_order_item_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('select-data-stock')}}",
                    dataType: "html",
                    data: {
                        'sales_order_item_id'	: sales_order_item_id,
                        '_token'        : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
					$('#item_stock_id').html(return_data);
                        console.log(return_data);
                    },
                    error: function(data)
                    {
                        console.log(data);

                    }
                });
		});
    });
</script>
@stop

@section('content_header')
@php
    $sdo_item_id = Request::segment(5);
@endphp
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-delivery-order') }}">Daftar Sales Delivery Order</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-delivery-order/void/'.$sales_delivery_order_id) }}">Hapus Sales Delivery Order</a></li>
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
                
                <button onclick="location.href='{{ url('sales-delivery-order/void/'.$sales_delivery_order_id) }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
            </div>
        </div>
    
        <div class="card-body">
            <div class="form-body form">
                <div class="table-responsive">
                    {{-- <div class="float-left">
                        <a data-toggle='modal' data-target="#editstock" name="Find" class="btn btn-success btn-sm mb-3" title="Add Data">Pilih Stock</a>
                    </div> --}}
                    <table class="table table-bordered table-advance table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style='text-align:center'>No.</th>
                                <th style='text-align:center'>Nama Barang</th>
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
                                                    <input type="hidden" name="sales_order_item_id_{{$no}}" id="sales_order_item_id_{{$no}}" value="{{$val['sales_order_item_id']}}">
                                                    <td style='text-align  : left !important;'>{{$SalesDeliveryOrder->getSelectInvItemStock2($val['item_stock_id'])}}</td>
                                                    <td style='text-align  : right !important;'>{{$val['item_total_stock']}}</td>
                                                    {{-- <td style='text-align:center;'>
                                                        <a href='' class="btn btn-outline-warning btn-sm" title="Edit Data">Edit</a> 
                                                        <a href='{{url ('/sales-delivery-order/edit/detail-item-stock/delete-item-stock/'.$val->sales_delivery_order_id.'/'.$val->sales_delivery_order_item_id.'/'.$val->sales_delivery_order_item_stock_id)}}' class="btn btn-outline-danger btn-sm" title="delete Data">Hapus</a>
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

{{-- {{Request::segment(4)}}
{{Request::segment(5)}} --}}
<br/>
<br/>
<br/>

@include('footer')
@php
    $sales_delivery_order = Request::segment(4);
    $sales_delivery_order_item = Request::segment(5);
@endphp
<form action="{{url('/sales-delivery-order/edit/detail-item-stock/change-item-stock/'.$sales_delivery_order.'/'.$sales_delivery_order_item)}}" method="POST">
    @csrf
    {{-- <input type="hidden" name="sales_delivery_order_item_stock_id" id="sales_delivery_order_item_stock_id" value="{{$val->sales_delivery_order_item_stock_id}}"> --}}

    <div class="modal fade bs-modal-md" id="editstock" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header"  style='text-align:left !important'>
                    <h4>Form Edit Item Stock</h4>
                    
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <a class="text-dark">Nama Barang<a class='red'> </a></a>
                            {!! Form::select('sales_order_item_id',  $type, '', ['class' => 'selection-search-clear select-form', 'id' => 'sales_order_item_id', 'name' => 'sales_order_item_id']) !!}
                            <input type="hidden" name="sales_delivery_order_id" id="sales_delivery_order_id" value="{{$sales_delivery_order}}">
                            <input type="hidden" name="sales_delivery_order_item_id" id="sales_delivery_order_item_id" value="{{$sales_delivery_order_item}}">
                            <input type="hidden" name="sales_order_id" id="sales_order_id" value="{{$sales_order_item_id['sales_order_id']}}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <a class="text-dark">Stock Barang<a class='red'> </a></a>
                            {{-- {!! Form::select('item_stock_id',  [], null, ['class' => 'selection-search-clear select-form', 'id' => 'item_stock_id']) !!} --}}
                            <select class="selection-search-clear" name="item_stock_id" id="item_stock_id" style="width: 100% !important">
                            </select>

                        </div>
                        {{-- <div class="col-md-12 mb-3">
                            <a class="text-dark">Satuan<a class='red'> </a></a>
                            {!! Form::select('item_unit_id',  $add_unit_purchaseorderitem, '', ['class' => 'selection-search-clear select-form', 'id' => 'item_unit_id']) !!}
                        </div> --}}
                        <div class="col-md-12">
                            <a class="text-dark">Quantity Stock<a class='red'> </a></a>
                            <input type="number" class="form-control input-bb" name="item_stock_quantity" value="" autocomplete="off">
                        </div>
                    </div>
                    <br>
                    <div class="modal-footer">
                        {{-- <button type="button" class="btn btn-danger btn-sm" >Batal</button> --}}
                        <button type="submit" class="btn btn-primary btn-sm" style="margin-right: -3%">Tambah</button>
                    </div>
                </div>
            </div>
        </div>
</form>
<br>
@stop

@section('css')
    
@stop

@section('js')
    
@stop