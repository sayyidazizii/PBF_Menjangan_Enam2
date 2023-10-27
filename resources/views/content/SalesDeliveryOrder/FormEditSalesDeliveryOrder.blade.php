@inject('SalesDeliveryOrder', 'App\Http\Controllers\SalesDeliveryOrderController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
	$(document).ready(function(){
	});

</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-delivery-order') }}">Daftar Sales Delivery Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Sales Delivery Order</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Edit Sales Delivery Order</b>
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
            Form Edit
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('sales-delivery-order') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-edit-sales-delivery-order')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Gudang :</a>
                        <br/>
                        {!! Form::select('warehouse_id',  $warehouse, $salesdeliveryorder['warehouse_id'], ['class' => 'selection-search-clear select-form', 'id' => 'warehouse_id']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Delivery Order :</a>
                        <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="sales_delivery_order_date" id="sales_delivery_order_date" onChange="function_elements_add(this.name, this.value);" value="{{$salesdeliveryorder['sales_delivery_order_date']}}"/>
                        <input type ="hidden" class="" name="sales_delivery_order_id" id="sales_delivery_order_id" value="{{$sales_delivery_order_id}}"/>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Deskripsi :</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="sales_delivery_order_remark" id="sales_delivery_order_remark" onChange="function_elements_add(this.name, this.value);" >{{$salesdeliveryorder['sales_delivery_order_remark']}}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br/>
    <div class="card border border-dark">
        <div class="card-header border-dark bg-dark">
            <h5 class="mb-0 float-left">
                Daftar
            </h5>
        </div>
    
        <div class="card-body">
            <div class="form-body form">
                <div class="table-responsive">
                    <table class="table table-bordered table-advance table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style='text-align:center'>No.</th>
                                <th style='text-align:center'>Pelanggan</th>
                                <th style='text-align:center'>No. Sales Order</th>
                                <th style='text-align:center'>Tanggal SO</th>
                                <th style='text-align:center'>Barang</th>
                                <th style='text-align:center'>Qty</th>
                                <th style='text-align:center'>Qty Proses</th>
                                <th style='text-align:center'>Qty Kirim</th>
                                <th style='text-align:center'>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                           
                                @if(count($salesdeliveryorderitem)==0)
                                    <tr><th colspan='9' style='text-align  : center !important;'>Data Kosong</th></tr>
                                @else 
                                    @php
                                        $no =1;
                                    @endphp
                                    @foreach ($salesdeliveryorderitem AS $key => $val)
                                        @php
                                            $item = $SalesDeliveryOrder->getSalesOrderItem($val['sales_order_item_id']);
                                        @endphp
                                            <tr>
                                                <td style='text-align  : center'>{{$no}}</td>
                                                <td style='text-align  : left !important;'>{{$SalesDeliveryOrder->getCustomerName($item['customer_id'])}}</td>
                                                <td style='text-align  : left !important;'>{{$item['sales_order_no']}}</td>
                                                <td style='text-align  : left !important;'>{{$item['sales_order_date']}}</td>
                                                <td style='text-align  : left !important;'>{{$item['item_name']}}</td>
                                                <td style='text-align  : right !important;'>{{$item['quantity']}}</td>
                                                <td style='text-align  : right !important;'>{{$item['quantity_resulted']}}</td>
                                                <td style='text-align  : right !important;'>
                                                    <input class='form-control' style='text-align:right;'type='text' name='quantity_delivered_{{$no}}' id='quantity_delivered_{{$no}}' value='{{$val['quantity']}}'/>  

                                                    <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_id_{{$no}}' id='sales_order_id_{{$no}}' value='{{$val['sales_order_id']}}'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_item_id_{{$no}}' id='sales_order_item_id_{{$no}}' value='{{$val['sales_order_item_id']}}'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='customer_id_{{$no}}' id='customer_id_{{$no}}' value='{{$val['customer_id']}}'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='item_id_{{$no}}' id='item_id_{{$no}}' value='{{$val['item_id']}}'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='item_unit_id_{{$no}}' id='item_unit_id_{{$no}}' value='{{$val['item_unit_id']}}'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='item_unit_price_{{$no}}' id='item_unit_price_{{$no}}' value='{{$val['item_unit_price']}}'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='subtotal_price_{{$no}}' id='subtotal_price_{{$no}}' value='{{$val['subtotal_price']}}'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='quantity_{{$no}}' id='quantity_{{$no}}' value='{{$val['quantity']}}'/>
                                                </td>
                                                <td style='text-align:center;'>
                                                    <a href='{{url('sales-delivery-order/edit/detail-item-stock/'.$val['sales_delivery_order_id'].'/'.$val['sales_delivery_order_item_id'])}}' class="btn btn-outline-info btn-sm" title="Detail Stock">Detail Stock</a>
                                                </td>
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
        
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <a name='Reset' class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'><i class="fa fa-times"></i> Reset</a>
                <button type="submit" name="Save" class="btn btn-primary btn-sm" title="Save"><i class="fa fa-check"></i> Simpan</button>
            </div>
        </div>
    </div>
</form>
<br/>
<br/>
<br/>

@include('footer')

@stop


@section('css')
    
@stop

@section('js')
    
@stop