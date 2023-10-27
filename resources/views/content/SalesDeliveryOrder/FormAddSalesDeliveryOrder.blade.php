@inject('SalesDeliveryOrder', 'App\Http\Controllers\SalesDeliveryOrderController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
        function elements_add(name, value){
                $.ajax({
                    type: "POST",
                    url : "{{route('elements-add-sales-delivery-order')}}",
                    dataType: "html",
                    data: {
                        'name'      : name,
                        'value'	    : value,
                        '_token'    : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
                        console.log(return_data);
                    },
                    error: function(data)
                    {
                        console.log(data);

                    }
                });
            }

	$(document).ready(function(){

        var elements = {!! json_encode($salesdeliveryorderelements) !!};

        if(!elements || elements==''){
            elements = [];
        }

        if(!elements['warehouse_id']){
            $("#warehouse_id").select2("val", "0");
        }
        
        if(!elements['sales_delivery_order_remark']){
            $("#sales_delivery_order_remark").value("val", "0");
        }

        // if(!elements['sales_delivery_order_date']){
        //     $("#sales_delivery_order_date").select2("val", "0");
        // }



        // $("#warehouse_id").select2("val", "0");
        $("#sales_order_item_id").select2("val", "0");
        // $("#item_stock_id").select2("val", "0");
        // $("#item_stock_id").select2("val", "0");

             

        // // $("#item_type_id").change(function(){
        



        $("#sales_order_item_id").change(function(){
			var sales_order_item_id 	= $("#sales_order_item_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('select-data-unit2')}}",
                    dataType: "html",
                    data: {
                        'sales_order_item_id'	: sales_order_item_id,
                        '_token'        : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
					$('#item_unit_id').html(return_data);
                        console.log(return_data);
                    $("#item_unit_id").val(return_data);
                        console.log(return_data);        
                    },
                    error: function(data)
                    {
                        console.log(data);

                    }
                });
		});
        
	});


    function datastock_add($no){
        var sales_order_item_id = $("#sales_order_item_id_"+$no).val();
        var sales_order_id = $("#sales_order_id_"+$no).val();
        var item_type_id = $("#item_type_id_"+$no).val();
        var item_stock_id = $("#item_stock_id_"+$no).val();
        var item_stock_quantity = $("#quantity_item_"+$no).val();
        var item_unit_id = $("#item_unit_id_"+$no).val();



        console.log(sales_order_item_id,sales_order_id,item_type_id,item_stock_id,item_stock_quantity,item_unit_id);
        $.ajax({
            type: "POST",
            url : "{{route('add-item-stock-to-sdo')}}",
            dataType: "html",
            data: {
                'sales_order_item_id'      : sales_order_item_id,
                'sales_order_id'           : sales_order_id,
                'item_type_id'             : item_type_id,
                'item_stock_id'            : item_stock_id,
                'item_stock_quantity'      : item_stock_quantity,
                'item_unit_id'             : item_unit_id,
                '_token'                   : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                window.location.reload();
                console.log(data);
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }


    function datastock_delete($no){
        var sdo_item_stock_temporary_id = $("#sales_delivery_order_item_stock_temporary_id_"+$no).val();


        console.log(sdo_item_stock_temporary_id);
        $.ajax({
            type: "post",
            url : "{{route('delete-item-stock-sdo-temp')}}",
            dataType: "html",
            data: {
                'sales_delivery_order_item_stock_temporary_id'      : sdo_item_stock_temporary_id,
                '_token'                   : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                window.location.reload();
                console.log(data);
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }



    

    
</script>
@stop

@php
    // dd($inv_item_stock_temporary);
@endphp

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-delivery-order') }}">Daftar Sales Delivery Order</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-delivery-order/search-sales-order') }}">Daftar Sales Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Sales Delivery Order</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Tambah Sales Delivery Order</b>
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
            Form Tambah
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('sales-delivery-order/search-sales-order') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

<form method="post" action="{{route('process-add-sales-delivery-order')}}" enctype="multipart/form-data">
        @csrf
        
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Gudang<a class='red'> *</a></a>
                        <br/>
                        {!! Form::select('warehouse_id',  $warehouse ,$salesdeliveryorderelements == null ? '' : $salesdeliveryorderelements['warehouse_id'], ['class' => 'selection-search-clear select-form', 'id' => 'warehouse_id' , 'onchange' => 'elements_add(this.name , this.value);'] ) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Delivery Order<a class='red'> *</a></a>
                        <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="sales_delivery_order_date" id="sales_delivery_order_date" onChange="elements_add(this.name, this.value);" value="{{$salesdeliveryorderelements == null ? '' : $salesdeliveryorderelements['sales_delivery_order_date']}}"/>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Deskripsi</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="sales_delivery_order_remark" id="sales_delivery_order_remark"  onChange="elements_add(this.name, this.value);" >{{$salesdeliveryorderelements == null ? '' : $salesdeliveryorderelements['sales_delivery_order_remark']}}</textarea>
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
            <div class="float-right">
                {{-- <a href='#addtstock' data-toggle='modal' name="Find" class="btn btn-success btn-sm" title="Add Data">Pilih Stock</a> --}}
            </div>
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
                                <th style='text-align:center'>Satuan</th>
                                <th style='text-align:center'>Qty</th>
                                <th style='text-align:center'>Qty Proses</th>
                                <th style='text-align:center'>Qty Kirim</th>
                                <th style='text-align:center'>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <label style='text-align  : left !important;'>Ppn Out<input class='form-control' style='text-align:right;'type='text' name='ppn_out_amount' id='ppn_out_amount' value='{{ $SalesDeliveryOrder->getPpnOut($salesorder['sales_order_id']) }}' readonly/>
                            </label>
                                @if(count($salesorderitem)==0)
                                    <tr>
                                        <th colspan='9' style='text-align  : center !important;'>Data Kosong</th>
                                    </tr>
                                @else
                                    @php
                                        $no =1;
                                        $sales_order_id = -1;
                                        $sales_order_item_id = -1;
                                    @endphp
                                        @foreach ($salesorderitem AS $key => $val)
                                        
                                            <tr>
                                                <td style='text-align  : center'>{{$no}}.</td>
                                                <td style='text-align  : left !important;'>{{$SalesDeliveryOrder->getCustomerName($salesorder['customer_id'])}}</td>
                                                <td style='text-align  : left !important;'>{{$salesorder['sales_order_no']}}</td>
                                                <td style='text-align  : left !important;'>{{date('d/m/Y', strtotime($salesorder['sales_order_date']))}}</td>
                                                <td style='text-align  : left !important;'>{{$SalesDeliveryOrder->getInvItemTypeName($val['item_type_id'])}}</td>
                                                <td style='text-align  : left !important;'>{{$SalesDeliveryOrder->getInvItemUnitName($val['item_unit_id'])}}</td>
                                                <td style='text-align  : right !important;'>{{$val['quantity']}}</td>
                                                <td style='text-align  : right !important;'>{{$val['quantity_resulted']}}</td>
                                                <td style='text-align  : right !important;'>
                                                    <input class='form-control' style='text-align:right;'type='text' name='quantity_delivered_{{$no}}' id='quantity_delivered_{{$no}}' value='{{$val['quantity_resulted']}}'/>  

                                                    <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_id_{{$no}}' id='sales_order_id_{{$no}}' value='{{$val['sales_order_id']}}'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_item_id_{{$no}}' id='sales_order_item_id_{{$no}}' value='{{$val['sales_order_item_id']}}'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='customer_id_{{$no}}' id='customer_id_{{$no}}' value='{{$salesorder['customer_id']}}'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='item_type_id_{{$no}}' id='item_type_id_{{$no}}' value='{{$val['item_type_id']}}'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='item_unit_id_{{$no}}' id='item_unit_id_{{$no}}' value='{{$val['item_unit_id']}}'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='item_unit_price_{{$no}}' id='item_unit_price_{{$no}}' value='{{$val['item_unit_price']}}'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='subtotal_after_discount_item_{{$no}}' id='subtotal_after_discount_item_{{$no}}' value='{{$val['subtotal_after_discount_item_b']}}'/>
                                                    <input class='form-control' style='text-align:right;' type='hidden' name='quantity_{{$no}}' id='quantity_{{$no}}' value='{{$val['quantity']}}'/>
                                                </td>
                                                <td style='text-align:center;'>
                                                    <a href='{{url('sales-delivery-order/detail-item-stock/'.$val['sales_order_id'].'/'.$val['sales_order_item_id'])}}' class="btn btn-outline-info btn-sm" title="Detail Stock">Detail</a>
                                                </td>
                                            </tr>
                                            <tr>
                                              {{-- <form id="form_stock" action="{{url('/sales-delivery-order/add-item-stock')}}" method="POST"> --}}
                                                {{-- {{ Form::open(['route' => '/sales-delivery-order/add-item-stock']) }} --}}
                                                <div id="form_baru">

                                                </div>
                                                    <td style='text-align  : center'>
                                                        <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_item_id' id='sales_order_item_id_{{$no}}' value='{{$val['sales_order_item_id']}}'/>  
                                                    </td>
                                                    <td colspan="2" style='text-align  : left !important;'>
                                                        <div class="row form-group">
                                                        <div class="col-md-12">
                                                            <input type="hidden" name="sales_order_id" id="sales_order_id_{{$no}}" value="{{Request::segment(3)}}">
                                                            <input type="hidden" name="item_type_id" id="item_type_id_{{$no}}" value="{{$val['item_type_id']}}">
                                                            {{-- <select class="selection-search-clear" name="item_type_id" id="item_type_id" style="width: 100% !important" > --}}
                                                            <div class="form-group">
                                                                <a class="text-dark">Pilih Batch Barang<a class='red'>*</a></a>
                            
                                                                <select class="selection-search-clear" name="item_stock_id" id="item_stock_id_{{$no}}" style="width: 100% !important" >
                                                                    <option value=''>--Choose One--</option>
                                                                    @foreach($item_stock_id[$no]['datastock'] as $Key => $item)
                                                                    <option value="{{$item['item_stock_id']}}">{{$item['item_name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        {{-- <input type="" name="item_batch_number" id="item_batch_number_{{$no}}" > --}}
                                                        <a class="text-dark">Qty Barang<a class='red'>*</a></a>
                                                        <input class='form-control' style='text-align:right;' type='' name='item_stock_quantity' id='quantity_item_{{$no}}' />
                                                        <input class='form-control' style='text-align:right;'type='hidden' name='item_unit_id' id='item_unit_id_{{$no}}' value='{{$val['item_unit_id']}}'/>
                                                    </td>
                                                    <td style='text-align  : left !important;'>
                                                        <br>
                                                        <a id="add-stock-button_{{$no}}"  type="button" onclick="datastock_add({{ $no }});" class="btn btn-primary btn-sm" ><i class="fa fa-plus"> </i> Pilih Stock</a>
                                                    </td>
                                                {{-- </form> --}}
                                                <td colspan="5" style='text-align  : left !important;'>
                                                    <table>
                                                        <tr>
                                                            <td>No</td>
                                                            <td>Batch</td>
                                                            <td>Satuan</td>
                                                            <td>qty</td>
                                                            <td>aksi</td>
                                                        </tr>
                                                  
                                                        @php
                                                             $row =1;
                                                             $baris =count($salesorderitemstocktemp);
                                                             $total = 0;

                                                             $datatemp = $SalesDeliveryOrder->getSOid2($val['sales_order_item_id']);
                                                        @endphp

                                                        @foreach ($datatemp as $Key => $val) 
                                                            {{-- <td>{{ $row++ }}</td> --}}
                                                        <tr>
                                                            {{-- <td>{{ $val['sales_delivery_order_item_stock_temporary_id'] }}</td> --}}
                                                             @php
                                                            $total += $val['item_stock_quantity'] ;
                                                            @endphp
                                                      
                                                            <td>{{ $row++ }}</td>
                                                            <td>{{ $SalesDeliveryOrder->getSelectInvItemStock($val['item_stock_id']) }}</td>
                                                            <input  type="hidden" name="sales_delivery_order_item_stock_temporary_id" id="sales_delivery_order_item_stock_temporary_id_{{$val['sales_delivery_order_item_stock_temporary_id']}}" value="{{$val['sales_delivery_order_item_stock_temporary_id']}}">    
                                                            <td>{{ $SalesDeliveryOrder->getInvItemUnitName($val['item_unit_id'])}}</td>                                                     
                                                            <td hidden>{{ $val['sales_order_id'] }}</td>
                                                            <td hidden>{{ $val['sales_order_item_id'] }}</td>
                                                            <td>{{ $val['item_stock_quantity'] }}</td>
                                                            <td><button id="delete-stock-button_{{$val['sales_delivery_order_item_stock_temporary_id']}}" onclick="datastock_delete({{ $val['sales_delivery_order_item_stock_temporary_id'] }});" type="button" class="btn btn-danger"><i class="fa fa-trash"></i></button></td>
                                                        </tr>
                                                        @endforeach 
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td>Total</td>
                                                            <td>{{ $total }}</td>
                                                            <td></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            @php
                                                $no++; 
                                            @endphp 
                                        @endforeach
                                        
                                        @php
                                            $no =1;
                                            $nos = 1;
                                        @endphp
                                        @foreach($stock AS $key => $item)
                                                <input type="text" name="item_stock_id_{{$item['sales_order_item_id']}}{{$no}}" id="item_stock_id{{$item['sales_order_item_id']}}_{{$no}}" value="{{$item['item_stock_id']}}">
                                                <input type="text" name="sales_order_id_{{$item['sales_order_item_id']}}{{$no}}" id="sales_order_id{{$item['sales_order_item_id']}}_{{$no}}" value="{{$item['sales_order_id']}}">
                                                <input type="text" name="sales_order_item_id_{{$item['sales_order_item_id']}}{{$no}}" id="sales_order_item_id{{$item['sales_order_item_id']}}_{{$no}}" value="{{$item['sales_order_item_id']}}">
                                                <input type="text" name="item_stock_quantity_{{$item['sales_order_item_id']}}{{$no}}" id="item_stock_quantity{{$item['sales_order_item_id']}}_{{$no}}" value="{{$item['item_stock_quantity']}}">
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
</div>
<br/>
<br/>
<br/>

@include('footer')

{{-- <form action="{{url('/sales-delivery-order/add-item-stock')}}" method="POST"> --}}
    @csrf
    
    <div class="modal fade bs-modal-md" id="addtstock" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header"  style='text-align:left !important'>
                    <h4>Form Pilih Stock Barang</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- <div class="col-md-12 mb-3">
                            <a class="text-dark">Tipe Barang<a class='red'> </a></a>
                            {!! Form::select('sales_order_item_id',  $type, '', ['class' => 'selection-search-clear select-form', 'id' => 'sales_order_item_id', 'name' => 'sales_order_item_id']) !!}
                            <input type="hidden" name="sales_order_id" id="sales_order_id" value="{{Request::segment(3)}}">
                            <input type="hidden" name="item_type_id" id="item_type_id" value="">
                        </div> --}}
                        <div class="col-md-12 mb-3">
                            <a class="text-dark">Stock Barang<a class='red'> </a></a>
                            {{-- {!! Form::select('item_stock_id',  [], null, ['class' => 'selection-search-clear select-form', 'id' => 'item_stock_id']) !!} --}}
                            {{-- <select class="selection-search-clear" name="item_stock_id" id="item_stock_id" style="width: 100% !important">
                            </select> --}}

                        </div>
                        <div class="col-md-12 mb-3">
                            <a class="text-dark">Satuan<a class='red'> </a></a>
                            {{-- <select class="selection-search-clear" name="item_unit_id" id="item_unit_id" style="width: 100% !important"> --}}
                            </select>                        
                        </div>
                        <div class="col-md-12">
                            <a class="text-dark">Quantity Stock<a class='red'> </a></a>
                            {{-- <input type="number" class="form-control input-bb" name="item_stock_quantity" id="item_stock_quantity" value="" autocomplete="off"> --}}
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
{{-- </form> --}}
<br>
<br>

@stop


@section('css')
    
@stop

@section('js')
    
@stop